<?php

/* ! ------ Setup */

require_once('panl.init.php');

$view = new GrlxView;
$message = new GrlxAlert;
$link = new GrlxLinkStyle;
$form = new GrlxForm;

$view-> yah = 3;

$form->send_to($_SERVER['SCRIPT_NAME']);
$form->row_class('config');

$options_list = array (
	'image'  => 'Comic image',
	'number' => 'Page number',
	'blog'   => 'Blog post',
	'transcript' => 'Transcript'
);

$var_list = array(
	'book_id','new_title','new_description','publish_frequency'
);
$var_type_list = array(
	'int','string','string','string'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val,$var_type_list[$key]);
	}
}

if ($_POST)
{
	$args['rssNew'] = $_POST['rss_options'];
}


if ( $book_id ) {
	$book = new GrlxComicBook($book_id);
	$_SESSION['book_id'] = $book_id;
}
else {
	$book = new GrlxComicBook();
	$book_id = $book-> bookID;
}





$frequency_list = array (
	'occasionally' => 'Occasionally',
	'Mon, Wed, Fri' => 'Mon, Wed, Fri',
	'Tue, Thu' => 'Tue, Thu',
	'weekdays' => 'Weekdays',
	'Saturdays' => 'Saturdays',
	'Sundays' => 'Sundays',
	'Mondays' => 'Mondays',
	'Tuesdays' => 'Tuesdays',
	'Wednesdays' => 'Wednesdays',
	'Thursdays' => 'Thursdays',
	'Fridays' => 'Fridays'
);

/* ! ------ Updates */

if ( $publish_frequency && $book_id ) {

	$data = array(
		'publish_frequency' => $publish_frequency,
		'date_modified' => $db -> NOW()
	);
	$db -> where('id', $book_id);
	$db -> update('book', $data);
	$success = $db -> count;

	//set_book_dates($book_id,$publish_frequency,$db);
}

if ( $book && $_POST ) {
	$data = array(
		'title' => $new_title,
		'description' => $new_description,
		'publish_frequency' => $publish_frequency,
		'date_modified' => $db -> NOW()
	);
	$db->where('id', $book_id);
	$db->update('book', $data);
	$success = $db->count;

	$link-> url('book.view.php?book_id='.$book_id);
	$link-> tap('Peruse this book’s pages');
	$alert_output .= $message->success_dialog('Book info saved. '.$link-> paint().'.');
}






/* ! ------ Build the display */

// ! Load a new book

if ( $book_id ) {
	$book = new GrlxComicBook($book_id);
}
else {
	$book = new GrlxComicBook();
	$book_id = $book-> bookID;
}
$args['bookID'] = $book_id;
$xml = new GrlxXML_Book($args);

// ! Feed options

if ( $options_list ) {
	foreach ( $options_list as $key => $val ) {

		if ( $xml->rss && in_array($key, $xml->rss))
		{
			$check = ' checked="checked"';
		}
		else
		{
			$check = '';
		}

		$rss_options_output .= '<div>';
		$rss_options_output .= '<input type="checkbox"'.$check.' id="'.$key.'" name="rss_options[option][]" value="'.$key.'"/>';
		$rss_options_output .= '<label class="option" for="'.$key.'">'.$val.'</label>';
		$rss_options_output .= '</div>';
	}
}

// ! Pub frequency

if ( $frequency_list ) {
	$publish_frequency_output .= '<label for="publish_frequency">Publish frequency</label>'."\n";
	$publish_frequency_output .= '<select id="publish_frequency" name="publish_frequency" style="width:8rem">'."\n";
	foreach ( $frequency_list as $key => $val ) {
		if ( $key == $book->info['publish_frequency']) {
			$publish_frequency_output .= '<option selected="selected" value="'.$key.'">- '.$val.'</option>'."\n";
		}
		else {
			$publish_frequency_output .= '<option value="'.$key.'">'.$val.'</option>'."\n";
		}
	}
	$publish_frequency_output .= '</select>'."\n";
}

//$marker_output = '<p><a href="marker-type.list.php">Edit marker types</a> (chapter, scene, etc)</p>';


// ! Metadata

$new_title_output .= '<label for="new_title">Title</label>'."\n";
$new_title_output .= '<input type="text" name="new_title" value="'.$book->info['title'].'" size="16" style="width:16rem"/>'."\n";

$new_description_output = '<label for="new_title">Summary</label>'."\n";
$new_description_output .= '<input type="text" name="new_description" value="'.$book->info['description'].'" size="32" style="width:24rem"/>'."\n";


// Group
$view->group_h2('Metadata');
$view->group_instruction('Change the name, description, and promised frequency of your book.');
$view->group_contents($new_title_output.$new_description_output.$publish_frequency_output);
$content_output .= $view->format_group();

$link->url('book.view.php');
$link->tap('Back to pages');
$view->action($link->text_link('back'));






/* ! ------ Output the display */

$view->page_title("Book: $book_info[title]");
$view->tooltype('chap');
$view->headline('Book <span>'.$book->info['title'].'</span>');

$view->group_h2('Feed options');
$view->group_instruction('Choose which bits of information readers will see in their RSS and JSON feeds.');
$view->group_contents($rss_options_output);
$feed_options_output  = $view->format_group();


$output  = $view->open_view();
$output .= $view->view_header();
$output .= $form->open_form();
$output .= $alert_output;
$output .= '<form accept-charset="UTF-8" method="post" action="book.edit.php">'."\n";
$output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
$output .= $content_output.'<hr/>';
$output .= $feed_options_output;
$output .= '<hr/><button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button>'."\n";
$output .= '<input type="hidden" name="book_id" value="'.$book_id.'">'."\n";
$output .= $form->close_form();
print($output);

print( $view->close_view() );
