<?php

// ! Setup


require_once('panl.init.php');
require_once('lib/htmLawed.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;
$fileops = new GrlxFileOps;
$comic_image = new GrlxComicImage;
$sl = new GrlxSelectList;

$max_file_size = ini_get( 'upload_max_filesize' ).' maximum';

$view-> yah = 1;
$alert_output = '';

$var_list = array(
	array('page_id','int'),
	array('new_page_name','string'),
	array('custom_url','string'),
	array('blog_headline','string'),
	array('blog_post','html'),
	array('book_id','int'),
	array('beginning_end','string'),
	array('into_marker_id','int'),
	array('pub_day','int'),
	array('pub_month','int'),
	array('pub_year','int'),
	array('transcript','html'),
	array('pub_time','string'),
	array('created','int')
);

if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		${$val[0]} = register_variable($val[0],$val[1]);
	}
}

if ( empty($book_id) ) {
	$book = new GrlxComicBook;
	$book_id = $book-> bookID;
}
else {
	$book = new GrlxComicBook($book_id);
}

$book-> getMarkers();


// register_variable strips needed whitespace from certain text blocks that we need.

$transcript = $_POST['transcript'] ?? NULL;
$transcript ? $transcript : $transcript = $_GET['transcript'] ?? NULL;
$transcript ? $transcript : $transcript = $_SESSION['transcript'] ?? NULL;

$blog_post = $_POST['blog_post'] ?? NULL;
$blog_post ? $blog_post : $blog_post = $_GET['blog_post'] ?? NULL;
$blog_post ? $blog_post : $blog_post = $_SESSION['blog_post'] ?? NULL;


// ! Updates


if ( !empty($_POST) && isset($_FILES['file_change']) && $_FILES['file_change']['name']['0'] != '' ) {

	// ! Add to a marker, if necessary.

	if ( $into_marker_id && is_numeric($into_marker_id) ) {
		$marker = new GrlxMarker($into_marker_id);
	}
	if ( isset($marker) ) {
		if ( $marker-> pageList ) {
			$start_page = reset($marker-> pageList);
			$start_tone_id = $start_page['tone_id'];
		}
		$start_tone_id ? $start_tone_id : $start_tone_id = 1; // Better check to make sure that’s valid.
		if ( $beginning_end == 'beginning' ) {
			$sort_order = $marker-> startPage + 0.001;
		}
		else {
			$sort_order = $marker-> endPage + 0.001;
		}
	}
	else {
		if ( $beginning_end == 'end' ) {
			$result = $db-> get ('book_page',1,'MAX(sort_order) AS endpage');
			if ( $result ) {
				$sort_order = $result[0]['endpage'];
			}
			else {
				$sort_order = 1.001;
			}
		}
	}

	if ( $into_marker_id && !is_numeric($into_marker_id) ) {
		if ( $beginning_end == 'beginning' ) {
			$sort_order = 0.001;
		}
		else {
			$result = $db-> get ('book_page',1,'MAX(sort_order) AS endpage');
			if ( $result ) {
				$sort_order = $result[0]['endpage'];
			}
			else {
				$sort_order = 1.001;
			}
		}
	}

	// ! Add the page to MySQL.
	$new_page_name ? $new_page_name : $new_page_name = 'Untitled';
	$blog_headline = $blog_headline? htmLawed($blog_headline) : null;
	$blog_post = $blog_post? htmLawed($blog_post) : null;
	$transcript = $transcript? htmLawed($transcript) : null;

	$custom_url = str_replace(' ', '-', $custom_url);
	$custom_url = str_replace('/', '', $custom_url);
	$custom_url = str_replace('?', '', $custom_url);
	$custom_url = str_replace('%', '', $custom_url);
	$custom_url = str_replace('&', '', $custom_url);
	$custom_url = trim($custom_url);
	$custom_url = strtolower($custom_url);

	$data = array(
		'sort_order' => $sort_order,
		'title' => $new_page_name,
		'book_id' => $book_id,
		'tone_id' => $start_tone_id,
		'options' => $custom_url,
		'blog_title' => $blog_headline,
		'blog_post' => $blog_post,
		'transcript' => $transcript,
		'date_modified' => $db -> NOW()
	);
	if ( $pub_year && $pub_month && $pub_day ) {
		$data['date_publish'] = $pub_year.'-'.$pub_month.'-'.$pub_day.' '.$pub_time;
	}
	else {
		$data['date_publish'] = $db -> NOW();
	}
	$new_page_id = $db -> insert('book_page', $data);
	if ( $new_page_id ) {
		reset_page_order($book_id,$db);
	}
}
elseif ( !empty($_POST) ) {
	$alert_output .= $message->alert_dialog('Huh, I didn’t find any images. Did you select some pics from your computer?');
}

if ( !empty($_FILES['file_change']) && !empty($new_page_id) ) {

	$fileops-> up_set_destination_folder('../'.DIR_COMICS_IMG);
	$success = $fileops-> up_process('file_change');

	if ( $success && $new_page_id ) {
		foreach ( $success as $filename ) {

			// Figure the real file name to make an alt attribute.
			/*$alt = explode('/', $filename); // Break into parts
			$alt = end($alt); // Get the last part (should be the file name)
			$alt = explode('.',$alt); // Break into parts
			array_pop($alt); // Remove the last part (should be the extension)
			$alt = implode('.', $alt); // Put it back together*/
			//Does anybody acttually WANT the filename as the alt text?

			// Create the image DB record.
			$new_image_id = $comic_image-> createImageRecord ( '/'.DIR_COMICS_IMG.$filename, $alt?? null );

			// Assign the image to the page.
			if ( $new_image_id && $new_page_id ) {
				$new_assignment_id = $comic_image-> assignImageToPage($new_image_id,$new_page_id);
			}
		}

		header('location:book.page-edit.php?created=1&page_id='.$new_page_id);
	}
}




// ! Display logic


// Build calendar options (month list, day list, year list)
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

// Build select elements for each date part.

$sl-> setName('pub_year');
$sl-> setCurrent(($_POST && $pub_year > 0)? $pub_year : date('Y'));
$sl-> setList($year_list);
$sl-> setValueID('id');
$sl-> setValueTitle('title');
$sl-> setStyle('width:6rem');
$year_select_output = $sl-> buildSelect();

$sl-> setName('pub_month');
$sl-> setCurrent(($_POST && $pub_month > 0)? $pub_month : date('m'));
$sl-> setList($month_list);
$sl-> setValueID('id');
$sl-> setValueTitle('title');
$sl-> setStyle('width:9rem');
$month_select_output = $sl-> buildSelect();

$sl-> setName('pub_day');
$sl-> setCurrent(($_POST && $pub_day > 0)? $pub_day : date('d'));
$sl-> setList($day_list);
$sl-> setValueID('id');
$sl-> setValueTitle('title');
$sl-> setStyle('width:5rem');
$day_select_output = $sl-> buildSelect();


if ( !is_writable('../'.DIR_COMICS_IMG) ) {
	$alert_output .= $message-> alert_dialog('I can’t write to the '.DIR_COMICS_IMG.' directory. Looks like a permissions problem.');
}

$marker_type_list = $db-> get ('marker_type',null,'id,title');
$marker_type_list = rekey_array($marker_type_list,'id');


$choose_marker_output  = <<<EOL
<input type="radio" name="beginning_end" value="beginning" id="beginning"/>
<label for="beginning" style="display:inline">Beginning of</label>

EOL;
$choose_marker_output .= <<<EOL
<input type="radio" checked="checked" name="beginning_end" value="end" id="end_of"/>
<label for="end_of">End of</label>

EOL;

$choose_marker_output .= '<select name="into_marker_id" style="width:12rem">'."\n";
if ( $book-> markerList && count($book-> markerList) > 0 ) {
	$marker_list = array_reverse($book-> markerList);
	foreach ( $marker_list as $key => $val ) {

		$type = $marker_type_list[$val['marker_type_id']];
		$choose_marker_output .= '<option value="'.$val['id'].'">'.$type['title'].': '.$val['title'].'</option>'."\n";
	}
}
else {
	$choose_marker_output .= '	<option value="the_book">the book</option>'."\n";
}
$choose_marker_output .= '</select>'."\n";


$meta_output  = '		<label for="new_page_name">Page title</label>'."\n";
$meta_output .= '		<input type="text" name="new_page_name" id="new_page_name" value="'.$new_page_name.'" style="max-width:20rem"/>';

$meta_output .= '		<label for="custom_url">Custom URL</label>'."\n";
$meta_output .= '		<input type="text" name="custom_url" id="custom_url" value="'.$custom_url.'" style="max-width:20rem"/>';

$meta_output .= '<label>Publication date</label>'."\n";
$meta_output .= $day_select_output;
$meta_output .= $month_select_output;
$meta_output .= $year_select_output;
$meta_output .= '&nbsp;Time: <input type="text" name="pub_time" style="width:6rem;display:inline" value="'.(($_POST && strlen($pub_time) > 0)? $pub_time : date('H:i:s')).'"/>'."\n";



$blog_output = <<<EOL
<label for="blog_headline">Headline</label>
<input type="text" name="blog_headline" id="blog_headline" value="$blog_headline"/>
<label for="blog_post">Post</label>
<textarea name="blog_post" id="blog_post" rows="7">$blog_post</textarea>

EOL;

$transcript_output = <<<EOL
<label for="transcript">Transcript</label>
<textarea name="transcript" id="transcript" rows="7">$transcript</textarea>
<button class="btn primary new" name="submit" type="submit" value="save"/><i></i>Create</button>

EOL;


$action_output = '';
if (is_file('book.list.php'))
{
	$link->url('book.list.php');
	$link->tap('Switch books');
	$link->reveal(false);
	$action_output = $link->text_link('back');
}

$link->url('book.import.php');
$link->tap('Create multiple pages');
$link->reveal(false);
$action_output .= $link->button_secondary('new');

$view->action($action_output);

$new_image = <<<EOL
<label for="file_change">Comic page image</label>
<input type="file" id="file_change" name="file_change[]" value="" multiple/>


EOL;


$content_output  = '<form accept-charset="UTF-8" action="book.page-create.php" method="post" enctype="multipart/form-data">'."\n";
$content_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";

$view->group_css('page');
$view->group_h2('Image');
$view->group_instruction('Upload the graphic(s) that readers will see on this page. ('.$max_file_size.')');
$view->group_contents($new_image);
$content_output .= $view->format_group()."<hr/>\n";

$view->group_css('page');
$view->group_h3('Order');
$view->group_instruction('Choose where in your book the new page will go.');
$view->group_contents($choose_marker_output);
$content_output .= $view->format_group()."<hr/>\n";

$link-> title = 'Learn more about metadata';
$link-> url = 'http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/metadata';
$link-> tap = 'information that describes';
$link-> transpose = false;

$view->group_css('page');
$view->group_h3('Metadata');
$view->group_instruction('Enter information about this page. Learn more about '.$link-> external_link().' this comic page.');
$view->group_contents($meta_output);
$content_output .= $view->format_group()."<hr/>\n";

$view->group_css('page');
$view->group_h3('Blog');
$view->group_instruction('Your thoughts of the day.');
$view->group_contents($blog_output);
$content_output .= $view->format_group()."<hr/>\n";

$link-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/seo');
$link-> tap('SEO');

$view->group_css('page');
$view->group_h3('Transcript');
$view->group_instruction('Dialogue, scene descriptions, etc — great '.$link-> external_link().' stuff.');
$view->group_contents($transcript_output);
$content_output .= $view->format_group();

$content_output .= '<input type="hidden" name="book_id" value="'.$book_id.'"/>'."\n";
$content_output .= '</form>'."\n";

if ($created == 1)
{
	$alert_output .= $message->success_dialog('Page created. <a href="book.page-create.php">Make another</a>?');
}






// ! Display


$view->page_title('Comic page creator');
$view->tooltype('page');
if (is_file('book.list.php'))
{
	$view->headline('New page in '.$book->info['title']);
}
else
{
	$view->headline('New page');
}
$view->action($action_output);

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
//$output .= $modal->modal_container();
//$output .= $content_output;
print($output);

?>



<?=$images_output ?? '' ?>

<?=$content_output ?>

<?php
$view->add_jquery_ui();
$view->add_inline_script($js_call ?? null);
print($view->close_view());
?>
