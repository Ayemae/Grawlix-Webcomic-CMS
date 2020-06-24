<?php

/*****
 * Setup
 */

include('panl.init.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;
$form = new GrlxForm;
$fileops = new GrlxFileOps;
$fileops->db = $db;
$book = new GrlxComicBook;
$marker = new GrlxMarker;
$comic_image = new GrlxComicImage;
$message = new GrlxAlert;
$sl = new GrlxSelectList;

$view-> yah = 2;

$book_id = $book-> bookID;



/*****
 * Updates
 */


if ( $_FILES && $book_id ){

	if ( is_writable('../'.DIR_COMICS_IMG) ) {

		$which = 'file';
		$fileops-> up_set_destination_folder('../'.DIR_COMICS_IMG);
		$files_uploaded = $fileops-> up_process($which);

		$new_order = $_POST['new_order'];
		$new_order ? $new_order : $new_order = 1;

		if ( $files_uploaded ) {

			$i = -1;

			$qty = count($_FILES['file']['name']);

			foreach ( $files_uploaded as $key => $val ) {

				$title = explode('.', $val);
				$title = $title[0];
				if ( strpos($title, '/')) {
					$title = explode('/', $title);
					$title = $title[1];
				}

				// Create the image DB record.
				$new_image_id = $comic_image-> createImageRecord ( DIR_COMICS_IMG.'/'.$val );

				// Create the page DB record.
				$new_page_id = $comic_image-> createPageRecord($title,$new_order + $i,$book_id);
				$first_page_id ? null : $first_page_id = $new_page_id;

				// Assign the image to the page.
				if ( $new_image_id && $new_page_id ) {
					$new_assignment_id = $comic_image-> assignImageToPage($new_image_id,$new_page_id);
				}

				$i+=0.0001;
			}

			reset_page_order($book_id,$db);
			header('location:book.view.php');
		}
	}
	else {
		$alert_output .= $message->alert_dialog('Yeah, look, I can’t put files in the '.DIR_COMICS_IMG.' folder on your web host.');
	}
}

if ( $_POST && $_FILES['file']['name']['0'] == '' ) {
	$alert_output .= $message->alert_dialog('Nothing uploaded. Did you select some images from your computer?');
}
elseif ( $fileops-> error_list ) {
	$alert_fileops .= '<ul>'."\n";
	foreach ( $fileops-> error_list as $key => $val ) {
		$alert_fileops .= '<li>'.$val.'</li>'."\n";
	}
	$alert_fileops .= '</ul>'."\n";
	$alert_output .= $message->alert_dialog($alert_fileops);
}


if ( $_POST['add-marker-type'] && $_POST['new_order'] && $first_page_id ) {

	$title = $_POST['new_name'];
	$title ? $title : $title = 'Untitled';
	$new_marker_id = $marker-> createMarker($title,$_POST['add-marker-type'],$first_page_id);
}

if ( $first_page_id && $new_marker_id ) {

	$data = array (
		'marker_id' => $new_marker_id
	);
	$db-> where('id',$first_page_id);
	$success = $db->update('book_page', $data);

}



if ( $new_marker_id && $_POST['return-or-not'] == '1' ) {
	header('location:marker.view.php?marker_id='.$new_marker_id);
	die();
}



/*****
 * Display
 */

if ( !is_writable('../'.DIR_COMICS_IMG) ) {
	$alert_output .= $message-> alert_dialog('I can’t write to the '.DIR_COMICS_IMG.' directory. Looks like a permissions problem.');
}

// Reset
$marker = new GrlxMarker;


$form->multipart();

$marker_type_list = $db-> get ('marker_type',null,'id,title');
$marker_type_list = rekey_array($marker_type_list,'id');

if ( $marker_type_list ) {
	$sl-> setName('add-marker-type');
	$sl-> setList($marker_type_list);
//	$sl-> setCurrent();
	$sl-> setValueID('id');
	$sl-> setValueTitle('title');
	$sl-> setStyle('width:12rem');
	$select_options = $sl-> buildSelect();

/*
	$select_options .= '<select name="add-marker-type" style="width:12rem">';
	foreach ( $marker_type_list as $key => $val ) {
		$select_options .= '<option value="'.$key.'">'.$val['title'].'</option>';
	}
	$select_options .= '</select>';
*/
}




$new_title_field .= '<label for="new_name">New marker title</label>'."\n";

$new_title_field .= '<input autofocus style="width:20rem" size="20" type="text" id="new_name" name="new_name" maxlength="64" placeholder="Title" value="">'."\n";

$new_upload_field  = '<input name="file[]" id="file" type="file" multiple /><br/>';
$new_upload_field .= 'Max size per image: '.number_format($fileops-> up_get_max_size()).' bytes. But recommend nothing larger than 100,000.<br/>';
//$new_upload_field .= 'Uploading more than '.$fileops-> up_get_max_file_uploads().' images? Try the <a href="book.import.php">bulk importer</a>.';

$result = $db-> get ('book_page',1,'MAX(sort_order) AS endpage');
if ( $result ) {
	$end_page = number_format($result[0]['endpage']) + 1;
}
else {
	$end_page = 1;
}

$new_order_field = '<input name="new_order" id="new_order" size="3" style="width:3rem" type="text" value="'.$end_page.'" />';



$view->page_title('New pages');
$view->tooltype('chap');
$view->headline('New pages');
$view->action($action_output);

if ( count ( $this_type ) > 1 ) {
	$view->group_h2('Type');
	$view->group_instruction('What kind of marker is this?');
	$view->group_contents($select_options);
	$content_output .= $view->format_group().'<hr />';
}
else {
	$id = reset($marker_type_list);
	$id = $id['id'];
	$content_output .= '<input type="hidden" name="add-marker-type" value="'.$id.'"/>';
}

$quick_upload_field = <<<EOL
<form action="uploadtome.php" class="dropzone" method="post" enctype="multipart/form-data">
	<input type="hidden" name="grlx_xss_token" value="{$_SESSION[admin]}"/>
	<div class="fallback">
  	<input name="file[]" type="file" multiple />
	</div>
</form>

EOL;

$view->group_h2('Quick add');
$view->group_instruction('Just add pages to the end of your book with no frills.');
$view->group_contents($quick_upload_field);
$content_output .= $view->format_group().'<hr />';

$content_output .= '<form accept-charset="UTF-8" action="marker.create.php" method="post" enctype="multipart/form-data" data-abide>'."\n";
$content_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";

$view->group_h2('Add images');
$view->group_instruction('Upload images from your computer to make comic pages.');
$view->group_contents($new_upload_field);
$content_output .= $view->format_group();

$view->group_h3('Starting at');
$view->group_instruction('Where in your story should the new pages go?');
$view->group_contents($new_order_field);
$content_output .= $view->format_group();

$link-> title = 'Learn more about markers';
$link-> url = 'http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/marker';
$link-> tap = 'mark these pages';

$view->group_h3('Marker');
$view->group_instruction('Optionally, you can '.$link-> external_link().' with a new '.$this_type.'.');
$view->group_contents($new_title_field);
$content_output .= $view->format_group();

$content_output .= '</form>'."\n";



print ( $view->open_view() );
print ( $view->view_header() );
print ( $alert_output );



?>

				<div class="row collapse">
<? if ( $book_list_output ): ?>
<?=$book_list_output ?>
<? endif; ?>
<?=$content_output ?>
								<button class="btn primary save" name="submit" type="submit" value="create-and-return"><i></i>Create</button> &nbsp;&nbsp;
								<!--input type="checkbox" name="return-or-not" value="1" id="return-or-not"/> <label for="return-or-not">and view the new chapter</label-->

				</div>
			</form>

<?php
$view->add_jquery_ui();
$view->add_script('dropzone.js');
$view->add_inline_script($js_call);
print($view->close_view());
?>