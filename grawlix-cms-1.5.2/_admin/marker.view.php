<?php

// ! ------ Setup

require_once('panl.init.php');

$view = new GrlxView;
//$modal = new GrlxForm_Modal;
$message = new GrlxAlert;
$link = new GrlxLinkStyle;
$list = new GrlxList;
$form = new GrlxForm;
//$fileops = new GrlxFileOps;
//$fileops->db = $db;
//$comic_image = new GrlxComicImage;
$sl = new GrlxSelectList;

$form->send_to($_SERVER['SCRIPT_NAME']);


$var_list = array(
	'marker_id','book_id','new_order','new_image','original_image_ref_id','new_title','edit_marker_type'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

if ( !$marker_id ) {
	header('location:book.view.php');
	die();
}

$marker = new GrlxMarker($marker_id);

$new_order ? $new_order : $new_order = 1;




// ! ------ Updates


// ! Images
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

// ! Update the marker’s title
if ( $marker_id && $new_title ) {
	$success = $marker-> saveMarker ( $marker_id, $new_title, $edit_marker_type );
	$marker = new GrlxMarker($marker_id); // reset
	if ( $success == 1)
	{
		$alert_output .= $message->success_dialog('Changes saved.');
	}
	else
	{
		$alert_output .= $message->alert_dialog('Changes failed to save.');
	}
}


/*
if ( $_POST['new_sort_order'] && $book_id ) {
	foreach ( $_POST['new_sort_order'] as $key => $val ) {
		if ( $_POST['orig_sort_order'][$key] > $_POST['new_sort_order'][$key] ) {
			$val -= 0.0001;
			$marker-> movePage($key,$val);
		}
		elseif ( $_POST['orig_sort_order'][$key] < $_POST['new_sort_order'][$key] ) {
			$val += 0.0001;
			$marker-> movePage($key,$val);
		}
	}
	reset_page_order($book_id,$db);
}
*/


/*
if ( $_FILES && $book_id ){

	$which = 'file';
	$fileops-> up_set_destination_folder('../'.DIR_COMICS_IMG);
	$files_uploaded = $fileops-> up_process($which);

	if ( $files_uploaded ) {

		// Count which page sort_order to add each new page.
		$i = 0;

		foreach ( $files_uploaded as $key => $val ) {

			// Create the image DB record.
			$new_image_id = $comic_image-> createImageRecord ( DIR_COMICS_IMG.'/'.$val );

			// Create the page DB record.
			$title = explode('.', $val);
			$title = $title[0];
			if ( strpos($title, '/')) {
				$title = explode('/', $title);
				$title = $title[1];
			}
			$new_page_id = $comic_image-> createPageRecord($title,$new_order - 1 + $i,$book_id);
//			$first_page_id ? null : $first_page_id = $new_page_id;

			// Assign the image to the page.
			if ( $new_image_id && $new_page_id ) {
				$new_assignment_id = $comic_image-> assignImageToPage($new_image_id,$new_page_id);
			}

			$i+=0.001;
		}
		reset_page_order($book_id,$db);

		if ( count($files_uploaded) == 1 ) {
			$alert_output .= $message->success_dialog('One image added. Make changes below or <a href="book.view.php">check out all the pages</a>.');
		}
		if ( count($files_uploaded) > 1 ) {
			$alert_output .= $message->success_dialog(count($files_uploaded).' images added. Make changes below or <a href="book.view.php">check out all the pages</a>.');
		}
	}
	if ( !$files_uploaded ) {
//		$alert_output .= $message->alert_dialog('No images added.');
	}
}
*/





// ! ------ Display logic

/*
if ( !is_writable('../'.DIR_COMICS_IMG) ) {
	$alert_output .= $message->alert_dialog('The comics images folder is not writable.');
}
*/


// Reset the marker info after making updates.
if ( $_POST ) {
	$marker = new GrlxMarker($marker_id);
}




$marker_type_list = $db-> get ('marker_type',null,'id,title');
$marker_type_list = rekey_array($marker_type_list,'id');


/*
if ( $marker-> pageList ) {

	$edit_link = new GrlxLinkStyle;
	$edit_link->url('book.page-edit.php');
	$edit_link->title('Edit page meta.');
	$edit_link->reveal(false);
	$edit_link->action('edit');

	$delete_link = new GrlxLinkStyle;
	$delete_link->i_only(true);
	
	$list-> draggable(false);
	$list->row_class('chapter');

	$heading_list[] = array(
		'value' => ' ',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Title',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Page number',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Date',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Actions',
		'class' => null
	);
	
	$list->headings($heading_list);


	$pages_displayed = 0;
	foreach ( $marker-> pageList as $key => $val ) {

		$page = new GrlxComicPage($val['id']);

		$delete_link->id("id-$key");
		$edit_link->query("page_id=$key");
		$actions = $delete_link->icon_link('delete').$edit_link->icon_link();

		$link_output = $link->icon_link('edit');
		$val['title'] ? $title = $val['title'] : $title = '<span class="error">Untitled</span>';
		$select = '<input type="checkbox" name="sel['.$key.']" value="'.$key.'"/>'."\n";

		$list_items[$key] = array(
//			'select'=> $select,
			'select' => '&nbsp;',
			'title'=> $title,
			'sort_order'=> '<input type="number" name="new_sort_order['.$key.']" value="'.intval($val['sort_order']).'" style="width:3rem"/>',
			'date'=> format_date($val['date_publish']),
			'action'=> $actions
		);
		$orig_output .= '<input type="hidden" name="orig_sort_order['.$key.']" value="'.$val['sort_order'].'"/>'."\n";
		$pages_displayed++;
	}

	$list->content($list_items);
	$content_output  = $list->format_headings();
	$content_output .= $list->format_content();
}
*/
//Just show a list to the marker page list instead:
$content_output = '<p><a href="book.view.php?view_marker='.$marker_id.'" class="btn primary">View pages</a></p>';

if ( $marker_type_list ) {
	$sl-> setName('edit_marker_type');
	$sl-> setList($marker_type_list);
	$sl-> setValueID('id');
	$sl-> setCurrent($marker->markerInfo['marker_type_id']); // selected="selected"
	$sl-> setValueTitle('title');
	$sl-> setStyle('width:12rem');
	$select_options = $sl-> buildSelect();

}

/////// Add these later

$metadata_output .= '	<label for="new_title">Title</label>'."\n";
$metadata_output .= '	<input type="text" id="new_title" name="new_title" size="12" style="width:12rem" value="'.$marker-> markerInfo['title'].'"/></p>'."\n";

$metadata_output .= '	<label for="add-marker-type">Type</label>'."\n";
$metadata_output .= $select_options;
$metadata_output .= '	<br/><button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button>'."\n";

$link->url('book.pages-create.php');
$link->tap('bulk importer');

$new_upload_field  = '<input name="file[]" id="file" type="file" multiple /><br/>';
$new_upload_field .= '<button class="btn primary new" name="new-pages" type="submit" value="new-pages"><i></i>Add pages</button>';
/*
$new_upload_field .= '<br/>&nbsp;<p>'.number_format($fileops-> up_get_max_size()).' bytes max file size. (Recommended max: 100,000 bytes per image.) The server can accept up to '.$fileops-> up_get_max_file_uploads().' images at a time.</p>'."\n";
$new_upload_field .= '<p>Uploading more than '.$fileops-> up_get_max_file_uploads().' images? Try the '.$link-> paint().'.</p>';
*/




$custom_image_output .= '<input type="hidden" name="original_image_ref_id" value="'.$marker->thumbInfo['id'].'"/>'."\n";
if ( $marker->thumbInfo && is_array($marker->thumbInfo) )
{
	$thumbnail_image = '<img src="'.$milieu_list['directory']['value'].$marker->thumbInfo['url'].'" alt="'.$marker->thumbInfo['description'].'">';
}
else
{
	$thumbnail_image = '<p>This marker has no archive image.</p>';
}

$custom_image_output .= '	<p><label for="new_title">Image</label>'."\n";
$custom_image_output .= $thumbnail_image."\n";
$custom_image_output .= '	<input type="file" id="new_image" name="new_image"/></p>'."\n";
$custom_image_output .= '	<button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button>'."\n";



$type = $marker_type_list [ $marker-> markerInfo['marker_type_id'] ]['title'];
$type ? $type : $type = 'Marker';
$view->page_title($type.': '.$marker-> markerInfo['title']);
$view->tooltype('page');
$view->headline($type.' <span>'.$marker-> markerInfo['title'].'</span>');

/*
$link->url('marker.edit.php?marker_id='.$marker-> markerID);
$link->tap('Edit '.$marker_type_list [ $marker-> markerInfo['marker_type_id'] ]['title'].' info');
$link->reveal(true);
$action_output = $link->text_link('editmeta');
*/



//$view->action($action_output);




$view->group_h2('Archive image');
$view->group_instruction('Add or change the graphic that represents this marker in the archives.');
$view->group_contents($custom_image_output);
$image_output .= $view->format_group();


$view->group_h2('Add pages');
$view->group_instruction('Upload images to create new pages here (NOT IMPLEMENTED).');
$view->group_contents($new_upload_field);
$upload_output .= $view->format_group();



$view->group_h2('Pages');
$view->group_instruction('Comic pages that belong to this marker.');
$view->group_contents($content_output);
$page_output .= $view->format_group();



$view->group_h2('Metadata');
$view->group_instruction('General information about this marker.');
$view->group_contents($metadata_output);
$metadata_output = $view->format_group();






/*****
 * Display
 */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
//$output .= $modal->modal_container();


$output .= '<form accept-charset="UTF-8" method="post" action="marker.view.php" enctype="multipart/form-data">'."\n";
$output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";


$output .= $metadata_output.'<hr/>';
$output .= $image_output."<hr/>\n";


$output .= $page_output.'<hr/>';
$output .= $upload_output;



$output .= '	<input name="new_order" id="new_order" type="hidden" value="'.($marker-> endPage+1).'" />';
$output .= '	<input name="marker_id" id="marker_id" type="hidden" value="'.$marker_id.'" />';
$output .= '	<input name="book_id" id="book_id" type="hidden" value="'.$marker-> markerInfo['book_id'].'" />';
$output .= $orig_output;
$output .= '</form>'."\n";

print($output);


/*
$js_call = <<<EOL
	$( "i.sort" ).hover( // highlight a draggable row
		function() {
			$( this ).parent().parent().addClass("dragging");
		}, function() {
			$( this ).parent().parent().removeClass("dragging");
		}
	);
	$( "a.edit" ).hover( // highlight the editable item
		function() {
			$( this ).parent().parent().addClass("editme");
		}, function() {
			$( this ).parent().parent().removeClass("editme");
		}
	);
	$( "i.delete" ).hover( // highlight a row to be deleted
		function() {
			$( this ).parent().parent().addClass("red-alert");
		}, function() {
			$( this ).parent().parent().removeClass("red-alert");
		}
	);
	$( '[id^="id-"]' ).click( // delete item
		function() { // update the db
			var item = $(this).attr('id'); // id of the item to delete
			var container = $('#'+item).parent().parent();
			$.ajax({
				url: "ajax.book-delete.php",
				data: "delete-chapter=" + item,
				dataType: "html",
				success: function(data){
					$(container).remove();
					renumberOrder( '[id^="sort-"]', 1 );
				}
			});
		}
	);
	$( "#sortable" ).sortable({ // sort items
		activate: function(event, ui) { // highlight the dragged item
			$( ui.item ).children().addClass("dragging");
		},
		deactivate: function(event, ui) { // turn off the highlight
			$( ui.item ).children().removeClass("dragging");
			renumberOrder( '[id^="sort-"]', 1 );
		},
		update: function() {
			serial = $('#sortable').sortable('serialize');
			$.ajax({
				url: "ajax.sort.php",
				type: "post",
				data: serial,
				success: function(data){
					var obj = jQuery.parseJSON(data);
				},
				error: function(){
					alert("AJAX error");
				}
			});
		}
	});
	$( "#sortable" ).disableSelection();
EOL;
*/


//$view->add_jquery_ui();
//$view->add_inline_script($js_call);
print( $view->close_view() );
