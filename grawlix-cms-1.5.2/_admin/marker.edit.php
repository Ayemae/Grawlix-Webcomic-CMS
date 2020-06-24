<?php

// ! Setup


require_once('panl.init.php');

$view = new GrlxView;
$modal = new GrlxForm_Modal;
$message = new GrlxAlert;
$link1 = new GrlxLinkStyle;
$link2 = new GrlxLinkStyle;
$list = new GrlxList;
$sl = new GrlxSelectList;

$max_file_size = ini_get( 'upload_max_filesize' ).'B maximum';

$var_list = array(
	'marker_id','new_title','edit_marker_type','new_image','original_image_ref_id'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}


if ( $marker_id ) {
	$marker = new GrlxMarker($marker_id,true);
}
else {
	header('location:book.view.php');
	die();
}




// ! Updates
if ( $_FILES )
{
	// Every new image gets its own folder.
	$serial = date('YmdHis').substr(microtime(),2,6);
	$path = '/'.DIR_COMICS_IMG.$serial;
	$new_directory_made = mkdir('..'.$path);

	// Move the file to its new home.
	if ( $new_directory_made )
	{
		$success1 = move_uploaded_file($_FILES['new_image']['tmp_name'], '..'.$path.'/'.$_FILES['new_image']['name']);
	}

	// Got a problem? Report what went wrong.
	if ( !$success1 ) {

		// Can you write to the new folder?
		if ( !is_writable('..'.$path)) {
			$alert_output .= $message->alert_dialog('Unable to upload image. Looks like a folder permissions problem.');
		}
		else {
			// See http://php.net/manual/en/features.file-upload.errors.php
			switch ( $_FILES[$which]['error'][$key] ) {
				case 1:
					$alert_output .= $message->alert_dialog('I couldn’t upload the image. It exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit.');
					break;
				case 2:
					$alert_output .= $message->alert_dialog('I couldn’t upload the image. It exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit.');
					break;
				case 3:
					$alert_output .= $message->alert_dialog('I couldn’t receive the image. There was nothing to receive.');
					break;
				case 6:
					$alert_output .= $message->alert_dialog('I couldn’t receive the image. There was no “temp” folder on the server — contact your host.');
					break;
				case 8:
					$alert_output .= $message->alert_dialog('I couldn’t upload the image. It doesn’t look like a PNG, GIF, JPG, JPEG or SVG.');
					break;
			}
		}
	}

	// If the image uploaded successfully, then …
	if ( $success1 )
	{
		// Update existing image records.
		if ( $original_image_ref_id && $original_image_ref_id > 0 )
		{
			$data = array(
				'url' => $path.'/'.$_FILES['new_image']['name'],
				'date_modified' => $db->now()
			);
			$db->where('id',$original_image_ref_id);
			$success2 = $db->update('image_reference', $data);
		}

		// Create a new image record.
		else
		{
		$data = array(
			'url' => $path.'/'.$_FILES['new_image']['name'],
			'date_created' => $db->now()
		);
		$success3 = $db->insert('image_reference', $data); // I used success3 to differentiate between DB events.

		$data = array(
			'rel_id' => $marker_id,
			'rel_type' => 'marker',
			'image_reference_id' => $success3,
			'date_created' => $db->now()
		);

		$success3 = $db->insert('image_match', $data);
		}
	}
}


// Update the marker’s title
if ( $marker_id && $new_title ) {
	$success = $marker-> saveMarker ( $marker_id, $new_title, $edit_marker_type );
	$marker = new GrlxMarker($marker_id); // reset
}

if ( $success ) {
	$link1-> url('marker.view.php?marker_id='.$marker_id);
	$link1-> tap('for this marker');

	$link2-> url('book.view.php');
	$link2-> tap('the whole book');

	$alert_output = $message-> success_dialog('Marker saved. Return to the page list '.$link1-> paint().' or '.$link2-> paint().'.');

}
// What are the odds that we’ll need this? Seriously, I’m asking.
elseif ( $_POST ) {

	$link1-> url('marker.view.php?marker_id='.$marker_id);
	$link1-> tap('Return to the page list');

	$alert_output = $message-> alert_dialog('Something went wrong, sorry. '.$link1-> paint().'.');
}





// ! Get the marker types
// so we can let artists change this marker to a different kind.

$db-> orderBy('rank', 'ASC');
$marker_type_list = $db-> get ('marker_type',null,'id,title,rank');
$marker_type_list = rekey_array($marker_type_list,'id');

// Add rank numbers to the titles (I want to emphasize
// that there’s a definite pecking order at work).
if ( $marker_type_list ) {
	foreach ( $marker_type_list as $key => $val ) {
		$marker_type_list[$key]['title'] = $val['rank'].'. '.$val['title'];
	}
}

// Build the list from which artists choose
if ( $marker_type_list ) {

	$sl-> setName('edit_marker_type'); // <select name="spike">
	$sl-> setList($marker_type_list); // List for <option>s
	$sl-> setCurrent($marker->markerInfo['marker_type_id']); // selected="selected"
	$sl-> setValueID('id'); // <select id="jet">
	$sl-> setValueTitle('title'); // <label for="jet">Faye</label>
	$sl-> setStyle('width:12rem'); // <select style="Ed">
	$select_options = $sl-> buildSelect().'<br/>'."\n"; // Assemble the select element.
}


// ! Build the edit form
$edit_form_output .= '	<input type="hidden" name="marker_id" value="'.$marker_id.'"/>'."\n";
$edit_form_output .= '	<input type="hidden" name="original_image_ref_id" value="'.$marker->thumbInfo['id'].'"/>'."\n";
$edit_form_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
$edit_form_output .= '	<p><label for="new_title">Title</label>'."\n";
$edit_form_output .= '	<input type="text" id="new_title" name="new_title" size="12" style="width:12rem" value="'.$marker-> markerInfo['title'].'"/></p>'."\n";
$edit_form_output .= '	<label for="edit_marker_type">Type</label>'."\n";
$edit_form_output .= $select_options."\n";
//$edit_form_output .= '	<button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button>'."\n";


if ( $marker->thumbInfo && is_array($marker->thumbInfo) )
{
	$thumbnail_image = '<img src="'.$milieu_list['directory']['value'].$marker->thumbInfo['url'].'" alt="'.$marker->thumbInfo['description'].'">';
}
else
{
	$thumbnail_image = '<p>This marker has no archive image.</p>';
}

$image_form_output .= '	<p><label for="new_image">Image</label>'."\n";
$image_form_output .= $thumbnail_image."\n";
$image_form_output .= '	<input type="file" id="new_image" name="new_image"/></p>'."\n";
$image_form_output .= '	<button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button>'."\n";


// ! Build the overall view

$view->page_title('Marker: '.$marker-> markerInfo['title']);
$view->tooltype('chap');
$view->headline('Marker <span>'.$marker-> markerInfo['title'].'</span>');

$content_output .= '<form accept-charset="UTF-8" action="marker.edit.php" method="post" enctype="multipart/form-data">'."\n";

$view->group_h2('General info');
$view->group_instruction('Change this marker’s name and type.');
$view->group_contents($edit_form_output);
$content_output .= $view->format_group();

$content_output .= '<hr/>'."\n";

$view->group_h2('Archive image');
$view->group_instruction('Add or change the graphic that represents this marker in the archives.');
$view->group_contents($image_form_output);
$content_output .= $view->format_group();

$content_output .= '</form>'."\n";

// Let artists jump to the marker type manager
// from here because it’s contextual.

$link1->url('marker-type.list.php');
$link1->tap('Edit marker types');
$link1->id('edit-types');
$action_output = $link1->text_link('editmeta');
$view->action($action_output);



// ! Display
$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $content_output;
$output .= $view->close_view();

print($output);

print( $view->close_view() );
