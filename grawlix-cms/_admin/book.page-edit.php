<?php

// ! ------ Setup

require_once('panl.init.php');
require_once('lib/htmLawed.php');

$link1 = new GrlxLinkStyle;

$view = new GrlxView;
$message = new GrlxAlert;
$marker = new GrlxMarker;
$fileops = new GrlxFileOps;
$sl = new GrlxSelectList;

$max_file_size = ini_get( 'upload_max_filesize' ).'B maximum';

$view-> yah = 3;

$var_list = array('action','page_id','new_page_name','remove_id','blog_headline','sort_order','custom_url','created');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

if ( empty($page_id) )
{
	header('location:book.view.php');
	die();
}
if ( $page_id && !is_numeric($page_id))
{
	header('location:book.view.php');
	die();
}


for ( $i=1; $i<32; $i++ ) {
	$i < 10 ? $i = '0'.$i : null;
	$day_list[$i] = array(
		'title' => $i,
		'id' => $i
	);
}

for ( $i=1; $i<13; $i++ ) {
	$i < 10 ? $i = '0'.$i : null;
	$month_list[$i] = array(
		'title' => date("F", mktime(0, 0, 0, $i, 1, 2015)),
		'id' => $i
	);
}

for ( $i=date('Y')-20; $i<date('Y')+2; $i++ ) {
	$year_list[$i] = array(
		'title' => $i,
		'id' => $i
	);
}

$meridian = 'am';
for ( $i=0; $i<24; $i++ ) {
	$ii = $i; // For converting to 12-hour time as we count 24 hours with $i
	if ( $i > 11 ) {
		$meridian = 'pm';
		$ii = $i - 12;
	}
	if ( $ii == 0 ) {
		$ii = '12';
	}
	if ( $i < 10 ) {
		$i = '0'.$i;
	}
	for ($j=0; $j<60; $j+=15 ) {
		if ( $j < 15 ) {
			$j = '00';
		}
		$time_title = $ii.':'.$j.$meridian;
		$hour_list[$i.':'.$j.':00'] = $time_title;
	}
}


// register_variable strips needed whitespace from text blocks
$transcript = $_POST['transcript'] ?? null;
$transcript ? $transcript : $transcript = $_GET['transcript'] ?? null;
$transcript ? $transcript : $transcript = $_SESSION['transcript'] ?? null;
$blog_post = $_POST['blog_post'] ?? null;
$blog_post ? $blog_post : $blog_post = $_GET['blog_post'] ?? null;
$blog_post ? $blog_post : $blog_post = $_SESSION['blog_post'] ?? null;

$pub_year = $_POST['pub_year'] ?? null;
$pub_month = $_POST['pub_month'] ?? null;
$pub_day = $_POST['pub_day'] ?? null;
$pub_time = $_POST['pub_time'] ?? null;

if ( !$page_id ) {
	header('location:book.view.php');
	die();
}


$alert_output = '';

// ! ------ Updates

// ! Create new images

$which = 'new_file';
if ( isset($_FILES[$which]) && $_FILES[$which]['name'][0] != '' ) {
	foreach ( $_FILES[$which]['name'] as $key => $val ) {
		$serial = date('YmdHis').substr(microtime(),2,6);
		$path = '/'.DIR_COMICS_IMG.$serial;
		mkdir('..'.$path);

		// Move the file to its new home.
		$success1 = move_uploaded_file($_FILES[$which]['tmp_name'][$key], '..'.$path.'/'.$val);
		if ( !$success1 ) {
			$result = report_image_error($path, $_FILES[$which]['error']);
			$alert_output .= $message->alert_dialog($result);
		}
		else {
			$data = array(
				'url' => $path.'/'.$val,
				'date_created' => $db->now()
			);
			$success3 = $db->insert('image_reference', $data); // I used success3 to differentiate between DB events.

			$data = array(
				'rel_id' => $page_id,
				'rel_type' => 'page',
				'image_reference_id' => $success3,
				'date_modified' => $db->now()
			);

			$success3 = $db->insert('image_match', $data);
		}
	}
}

// ! Replace existing images

$which = 'file_change';
if ( isset($_FILES[$which]) ) {
	foreach ( $_FILES[$which]['name'] as $key => $val ) {
		// If there’s a folder to hold the previous image, use it for this new image.
		if (
			isset($_POST['original_path'])
			&& isset($_POST['original_path'][$key])
			&& strlen($_POST['original_path'][$key]) > 0
			&& is_dir('../'.$_POST['original_path'][$key])
		) {
			$path = $_POST['original_path'][$key];
		}
		// If not, make one.
		else {
			$serial = date('YmdHis').substr(microtime(),2,6);
			$path = '/'.DIR_COMICS_IMG.$serial;

			// Error handling
			$path = str_replace('//', '/', $path);
			mkdir('..'.$path);
		}

		// Move the file to its new home.
		$success1 = move_uploaded_file($_FILES[$which]['tmp_name'][$key], '../'.$path.'/'.$val);
		if ( !$success1 ) {
			if ( !is_writable('../'.$path)) {
				$alert_output .= $message->alert_dialog('Unable to upload image. Looks like a folder permissions problem.');
			}
			else {
				// See http://php.net/manual/en/features.file-upload.errors.php
				switch ( $_FILES[$which]['error'] ) {
					case UPLOAD_ERR_INI_SIZE:
						$alert_output .= $message->alert_dialog('I couldn’t upload the image. It exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit.');
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$alert_output .= $message->alert_dialog('I couldn’t upload the image. It exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit.');
						break;
					case UPLOAD_ERR_PARTIAL:
						$alert_output .= $message->alert_dialog('I couldn’t receive the image. There was nothing to receive.');
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$alert_output .= $message->alert_dialog('I couldn’t receive the image. There was no “temp” folder on the server — contact your host.');
						break;
					case UPLOAD_ERR_EXTENSION: //This error is actually about PHP extensions, not file extensions...
						$alert_output .= $message->alert_dialog('I couldn’t upload the image. It doesn’t look like a PNG, GIF, JPG, JPEG or SVG.');
						break;
				}
			}
		}
		// Or add an image reference to the database.
		else {
			// Update the DB image reference to use the new file.
			if ( $success1 ) {
				if ($key && $key > 0 ) {
					$data = array(
						'url' => $path.'/'.$val,
						'date_modified' => $db->now()
					);

					$db->where('id',$key);
					$success2 = $db->update('image_reference', $data);
				}
				// Or add an image reference to the database.
				else {
					$data = array(
						'url' => $path.'/'.$val,
						'date_modified' => $db->now()
					);
					$success3 = $db->insert('image_reference', $data); // I used success3 to differentiate between DB events.

					$data = array(
						'rel_id' => $page_id,
						'rel_type' => 'page',
						'image_reference_id' => $success3,
						'date_modified' => $db->now()
					);

					$success3 = $db->insert('image_match', $data);
				}
			}
		}
	}
}

// ! Metadata

if ( isset($page_id) && !empty($_POST) ) {
	if(!isset($custom_url))
		$custom_url = '';
	$custom_url = str_replace(' ', '-', $custom_url);
	$custom_url = str_replace('/', '-', $custom_url);
	$custom_url = str_replace('?', '', $custom_url);
	$custom_url = str_replace('%', '', $custom_url);
	$custom_url = str_replace('&', '', $custom_url);
	$custom_url = str_replace('#', '', $custom_url);
	$custom_url = str_replace(';', '', $custom_url);
	$custom_url = str_replace('\\', '', $custom_url);
	$custom_url = str_replace('(', '', $custom_url);
	$custom_url = str_replace(')', '', $custom_url);
	$custom_url = str_replace('=', '', $custom_url);
	$custom_url = str_replace('<script', '', $custom_url);
	$custom_url = trim($custom_url);
	$custom_url = strtolower($custom_url);

	$data = array(
		'title' => $new_page_name,
		'options' => $custom_url,
		'date_modified' => $db -> NOW()
	);
	$db -> where('id', $page_id);
	$success = $db -> update('book_page', $data);

	if ( isset($_POST['image_description']) ) {
		foreach ( $_POST['image_description'] as $key => $val ) {
			$data = array(
				'description' => $val,
				'date_modified' => $db -> NOW()
			);
			$db -> where('id', $key);
			$success = $db -> update('image_reference', $data);
		}
	}

	$db -> where('id', $page_id);
	$success = $db -> update('book_page', $data);

	$link1-> url('book.view.php');
	$link1-> tap('Return to the page list');
	$go_return_link = $link1-> paint();

	$db-> where('id', $page_id);
	$current_order = $db-> getOne('book_page', 'sort_order');

	$db-> where('sort_order', $current_order['sort_order'],'>');
	$db-> orderBy('sort_order','ASC');
	$next_id = $db-> getOne('book_page', 'id');

	if($next_id) {
		$link1-> url('book.page-edit.php?page_id='.$next_id['id']);
		$link1-> tap('Go to the next page');
		$go_next_link = $link1-> paint();
	} else {
		$go_next_link = null;
	}

	if ( $success == 1 ) {
		$success_message = <<<EOL
Changes to <b>$new_page_name</b> were saved.
<ul>
	<li>Make more changes below</li>
	<li>$go_return_link</li>
EOL;
	if($go_next_link) {
		$success_message .= "\t<li>".$go_next_link.'</li>';
	}
	$success_message .= "</ul>\n";

		if ( $success || $success1 ) {
			$alert_output .= $message->success_dialog($success_message);
		}
	}
	else {
		$alert_output .= $message->alert_dialog('Unable to save <b>'.$new_page_name.'</b> change.');
	}
}

// ! Blog post and transcript

if ( isset($page_id) && !empty($_POST) ) {

	$blog_headline = $blog_headline? htmLawed($blog_headline) : null;
	$blog_post = $blog_post? htmLawed($blog_post) : null;
	$transcript = $transcript? htmLawed($transcript) : null;

	$data = array(
		'blog_title' => $blog_headline,
		'blog_post' => $blog_post,
		'transcript' => $transcript,
		'date_publish' => $pub_year.'-'.$pub_month.'-'.$pub_day.' '.$pub_time,
		'date_modified' => $db -> NOW()
	);
	$db -> where('id', $page_id);
	$db -> update('book_page', $data);
}

if ( isset($page_id) && isset($remove_id) ) {
	$db -> where('id', $remove_id);
	$db -> delete('image_match');
}

if ( isset($page_id) && isset($sort_order) && (is_array($sort_order) || is_object($sort_order)) && !empty($_POST) ) {
	foreach ( $sort_order as $key => $val ) 	{
		$data = array(
			'sort_order' => $val
		);
		$db -> where('id', $key);
		$db -> update('image_match', $data);
	}
}



// ! ------ Display logic

if ( isset($page_id) ) {
	$page = new GrlxComicPage($page_id);
}

if ( isset($page-> pageInfo['book_id']) ) {
	$book = new GrlxComicBook($page-> pageInfo['book_id']);
	$book-> getPages();
}

$sl-> setName('pub_year');
$sl-> setCurrent(substr($page-> pageInfo['date_publish'],0,4));
$sl-> setList($year_list);
$sl-> setValueID('id');
$sl-> setValueTitle('title');
$sl-> setStyle('width:6rem');
$year_select_output = $sl-> buildSelect();

$sl-> setName('pub_month');
$sl-> setCurrent(substr($page-> pageInfo['date_publish'],5,2));
$sl-> setList($month_list);
$sl-> setValueID('id');
$sl-> setValueTitle('title');
$sl-> setStyle('width:9rem');
$month_select_output = $sl-> buildSelect();

$sl-> setName('pub_day');
$sl-> setCurrent(substr($page-> pageInfo['date_publish'],8,2));
$sl-> setList($day_list);
$sl-> setValueID('id');
$sl-> setValueTitle('title');
$sl-> setStyle('width:5rem');
$day_select_output = $sl-> buildSelect();

$hour_select_output = '';
if ( isset($hour_list) ) {
	$hour_select_output = '<select name="pub_time" style="width:8rem">'."\n";
	$prevTime = '0'; //this is less than '00:00'.
	foreach ( $hour_list as $key => $val ) {
		if ( $key >= substr($page-> pageInfo['date_publish'],-8,8) && $prevTime < substr($page-> pageInfo['date_publish'],-8,8)) {
			$selected = ' selected="selected"';
		}
		else {
			$selected = '';
		}
		$hour_select_output .= '<option value="'.$key.'"'.$selected.'>'.$val.'</option>'."\n";
		$prevTime = $key;
	}
	$hour_select_output .= '</select>'."\n";
}

$meta_output  = '		<label for="new_page_name">Page title</label>'."\n";
$meta_output .= '		<input type="text" name="new_page_name" id="new_page_name" value="'.$page-> pageInfo['title'].'" style="max-width:40rem"/>';

$meta_output .= '		<label for="custom_url">Custom URL</label>'."\n";
$meta_output .= '		<input type="text" name="custom_url" id="custom_url" value="'.$page-> pageInfo['options'].'" style="max-width:40rem"/>';

$meta_output .= '		<label>Publication date</label>'."\n";
$meta_output .= $day_select_output;
$meta_output .= $month_select_output;
$meta_output .= $year_select_output;


$meta_output .= $hour_select_output."\n";





// ! Back/next

if ( isset($book) && !empty($book-> pageList) ) {

	$last = end($book-> pageList);
	$last_id = $last['id'] ?? null;

	$first = reset($book-> pageList);
	$first_id = $first['id'] ?? null;

	$next = $page-> pageInfo['sort_order'] + 1;
	$next = $book-> pageList[$next.'.0000'] ?? null;
	$next_id = $next['id'] ?? null;

	$back = $page-> pageInfo['sort_order'] - 1;
	$back = $book-> pageList[$back.'.0000'] ?? null;
	$back_id = $back['id'] ?? null;

	if ( $first_id == $page_id ) {
		unset($first_page_id);
	}

	if ( $last_id == $page_id ) {
		unset($last_page_id);
	}
}


if ( isset($next_id) ) {
	$link1-> url('book.page-edit.php?page_id='.$next_id);
	$link1-> tap('next &rsaquo;');
	$next_link = $link1-> paint();
}
else {
	$next_link = 'next &rsaquo;';
}
if ( isset($back_id) ) {
	$link1-> url('book.page-edit.php?page_id='.$back_id);
	$link1-> tap('&lsaquo; back');
	$back_link = $link1-> paint();
}
else {
	$back_link = '&lsaquo; back';
}

if ( isset($first_id) ) {
	$link1-> url('book.page-edit.php?page_id='.$first_id);
	$link1-> tap('&laquo; first');
	$first_link = $link1-> paint();
}
else {
	$first_link = '&laquo; first';
}
if ( isset($last_id) ) {
	$link1-> url('book.page-edit.php?page_id='.$last_id);
	$link1-> tap('last &raquo;');
	$last_link = $link1-> paint();
}
else {
	$last_link = 'last &raquo;';
}



// ! Create thumbnails, if they don’t exist.
// This needs to happen here so we have imageList available.

$gd_enabled = FALSE; // Until proven otherwise
$gd_info = gd_info();
if ($gd_info && $gd_info['GD Version'] != '') {
	$gd_enabled = TRUE;

	// ! How big should thumbnails be?
	$db->where('label','thumb_max');
	$result = $db->getOne('milieu','value');

	if ($result) {
		$thumb_max = $result['value'];
	}
	else {
		$thumb_max = 200;
	}
}

// Actually make the new files.
$created_thumbs = FALSE;
if ( $page->imageList && $action == 'gen-thumb') {
	$success = FALSE;
	foreach ( $page->imageList as $key => $val ) {
		if (substr($val['url'],0,4) != 'http') {
			// Get the directory.
			$dir = explode('/',$val['url']);

			// Get the extension.
			$filename = array_pop($dir);
			$filename = explode('.',$filename);
			$extension = array_pop($filename);

			// Put it back together.
			$dir = implode('/',$dir);

			// Figure out the filenames with paths.
			$thumb_filename = '..'.$dir.'/thumb.'.$extension;
			$source_file = '..'.$val['url'];
			$success = create_thumbnail( $source_file, $thumb_filename, $thumb_max);
			if ($success === TRUE) {
				$created_thumbs = TRUE;
			}
		}
	}
}
if ($created_thumbs === TRUE ) {
	$alert_output .= $message->success_dialog('Thumbnail created.');
}
if ($action == 'gen-thumb' && $created_thumbs === FALSE ) {
	$alert_output .= $message->warning_dialog('Thumbnail not created.');
}





// Display each image. Let the user trash ’em as needed.
$path_output = '';
$row_divider = '';
$delete_me = '';
$main_image_output = '';
if ( $page-> imageList ) {
	if ( count($page-> imageList) > 1 ) {
		$row_divider .= '<hr/>';
	}
	$sort_order=1;
	foreach ( $page-> imageList as $key => $val ) {
		$ref_id = $val['image_reference_id'];
		if ( count ( $page-> imageList ) > 1 ) {
			$link1-> url('book.page-edit.php?page_id='.$page_id.'&amp;remove_id='.$key);
			$link1-> tap(' Delete this image');
			$link1-> title('Permanently delete the graphic from this comic page. There is no undo. Do not pass go. Do not collect 200 cubits.');
			$link1-> anchor_class('warning');
			$link1-> icon('delete');
			$delete_me = $link1-> paint();
		}

		if ( substr($val['url'],0,4) != 'http' && is_file('../'.$val['url'])) {
			$image_dimensions = getimagesize('../'.$val['url']);
			$image_bytes = filesize('../'.$val['url']);
			$weight = figure_pixel_weight($image_dimensions[0],$image_dimensions[1],$image_bytes);
		}
		else {
			$weight = 0;
		}
		$weight = round($weight,3);

		if (substr($val['url'],0,4) == 'http')
		{
			$this_image = '<img src="'.$val['url'].'" alt="'.$val['description'].'"/>'."\n";
		}
		else
		{
			!$milieu_list['directory']['value'] || $milieu_list['directory']['value'] == '' ? $url_prefix = '../' : $url_prefix = '../';
			$this_image = '<img src="'.$url_prefix.$val['url'].'" alt="'.$val['description'].'"/>'."\n";
		}

    $link1-> title = 'Learn more about pixel weight';
    $link1-> url = 'http://getgrawlix.com/docs/'.DOCS_VERSION.'/image-optimization';
    $link1-> tap = 'bytes/pixel';
    $link1-> icon = '';

		$this_description = '<p><label for="image_description['.$val['image_reference_id'].']">Description (alt text)</label>'."\n";

		$this_description .= '<input type="text" name="image_description['.$val['image_reference_id'].']" id="image_description['.$val['image_reference_id'].']" value="'.$val['description'].'" style="max-width:20rem"/>'."\n";

		$this_weight = 'Weight: '.$weight.' '.$link1-> paint()."\n";

		$this_sort_order = '<label for="this_sort_order['.$key.']">Order</label>'."\n";
		$this_sort_order .= '<input type="text" size="3" style="width:3rem" name="sort_order['.$key.']" value="'.$sort_order.'"/>'."\n";


		$temp_path = explode('/',$val['url']);
		array_pop($temp_path);
		$temp_path = implode('/',$temp_path);

		$path_output .= '<input type="hidden" name="original_path['.$val['image_reference_id'].']" value="'.$temp_path.'" />'."\n";

		if ( count ( $page-> imageList ) > 1 ) {
			$main_image_output .= <<<EOL
$row_divider
<div class="row">
	<div class="medium-4 columns">
		<p>Upload a new file ($max_file_size)</p>
		<input type="file" name="file_change[$ref_id]" value=""/><br/>
		<button class="btn secondary upload" id="submit" name="edit-submit" type="submit" value="save"/><i></i>Replace</button><br/>
		<br/><p>$this_weight</p>
		<p>$delete_me</p>
	</div>
	<div class="medium-4 columns">
		<p>$this_description</p>
		<p>$this_sort_order</p>
	</div>
	<div class="medium-4 columns">
		$this_image
	</div>
</div>

EOL;

		}
		else
		{
			$main_image_output .= <<<EOL
$row_divider
<div class="row">
	<div class="medium-6 columns">
		<p>Upload a new file ($max_file_size)</p>
		<input type="file" name="file_change[$ref_id]" value=""/><br/>
		<button class="btn secondary upload" id="submit" name="edit-submit" type="submit" value="save"/><i></i>Replace</button><br/>
		<p>$this_description
		$this_weight</p>
		<p>$delete_me</p>
	</div>
	<div class="medium-6 columns">
		$this_image
	</div>
</div>

EOL;
		}




		$sort_order++;
	}
}
$main_image_output .= <<<EOL
<hr/>
<div class="row">
	<div class="medium-12 columns">
		<p>New image for this page ($max_file_size)</p>
		<input type="file" name="new_file[]" value=""/><br/>
		<button class="btn secondary upload" id="submit" name="add-submit" type="submit" value="save"/><i></i>Upload</button><br/>
	</div>
</div>
<hr/>
EOL;

$head_output = <<<EOL


		<p style="text-align:right">
			$first_link $back_link &nbsp;
			$next_link $last_link
		</p>
$main_image_output

EOL;



// This exposition says “here are the images in this page.”
if( isset($page-> pageInfo['images']) ) {
	if ( count($page-> pageInfo['images']) == 1 ) {
		$image_count_output = '1 image in this page';
	}
	else {
		$image_count_output = count($page-> pageInfo['images']).' images in this page';
	}
} else {
	$image_count_output = '0 images in this page';
}


if ( isset($page-> pageInfo) && isset($page-> pageInfo['title']) ) {
	$heading_output = $page-> pageInfo['title'];
}
else {
	$heading_output = 'Unknown page';
}

$headline_output = '';
if ( isset($page-> pageInfo['blog_title']) ) {
	$headline_output = $page-> pageInfo['blog_title'];
}

$blog_output = <<<EOL
<label for="blog_headline">Headline</label>
<input type="text" name="blog_headline" id="blog_headline" value="$headline_output"/>
<label for="blog_post">Post</label>
<textarea name="blog_post" id="blog_post" rows="7">{$page-> pageInfo['blog_post']}</textarea>

EOL;

$transcript_output = <<<EOL
<label for="transcript">Transcript</label>
<textarea name="transcript" id="transcript" rows="7">{$page-> pageInfo['transcript']}</textarea>

EOL;



if ($page->imageList) {
	if ($gd_enabled === TRUE) {
		$thumb_output = '<a class="btn secondary upload" href="?page_id='.$page_id.'&action=gen-thumb">Generate thumbnail</a>'."\n";
	}
	else {
		$thumb_output = 'Your web host does not allow thumbnail generation.';
	}
}



$content_output = '';
if ( isset($thumb_output) ) {
	$view->group_css('page');
	$view->group_h2('Thumbnail');
	$view->group_contents($thumb_output);
	$view->group_instruction('Create an image to represent this page.');
	$content_output .= $view->format_group().'<hr />';
}

$link1-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/seo');
$link1-> tap('Metadata');

$view->group_css('page');
$view->group_h2('Metadata');
$view->group_contents($meta_output);
$view->group_instruction($link1->external_link().' is information that describes this comic page.');
$content_output .= $view->format_group().'<hr />';

//$link1-> url('http://daringfireball.net/projects/markdown');
//$link1-> tap('Markdown');

$view->group_css('page');
$view->group_h2('Blog');
$view->group_instruction('Write your thoughts for the day on this comic page. The field accepts most HTML elements.');
$view->group_contents($blog_output);
$content_output .= $view->format_group().'<hr />';

$link1-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/seo');
$link1-> tap('SEO');

$view->group_css('page');
$view->group_h2('Transcript');
$view->group_instruction('Transcript is a record of the the dialog, events and scenes in a comic page. In short, a script. Use this to improve accessibility and '.$link1-> external_link());
$view->group_contents($transcript_output);
$content_output .= $view->format_group();

if ($created == 1)
{
	$alert_output .= $message->success_dialog('Page created. <a href="book.page-create.php">Make another</a>?');
}



// ! ------ Display

$view->page_title("Comic page: $heading_output");
$view->tooltype('chap');
$view->headline("Comic page <span>$heading_output</span>");
$view->action($action_output ?? null);

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
print($output);

?>


<form accept-charset="UTF-8" action="book.page-edit.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="grlx_xss_token" value="<?=$_SESSION['admin']?>"/>
	<input type="hidden" name="page_id" value="<?=$page_id?>"/>
	<input type="hidden" name="sort_top" value="1"/>

<?=$head_output ?>

<?=$content_output ?>
<?=$path_output ?>

<hr/><button class="btn primary save right" name="submit" type="submit" value="save"/><i></i>Save</button>

</form>

<?php

$view->add_jquery_ui();
$view->add_inline_script($js_call ?? null);
print($view->close_view());
?>
