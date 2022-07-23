<?php

/* This script generates a modal that can rename a comic book, chapter, or page.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$modal = new GrlxForm_Modal();

$book_id = $_GET['book_id'];
$page_id = $_GET['page_id'];


/*****
 * Display logic
 */

// PAGE
if ( is_numeric($page_id) && is_numeric($chapter_id) ) {

	$item = $db
		-> where('id', $page_id)
		-> getOne('book_page', 'title');

	$modal->send_to('book.view.php');

	$modal->input_hidden('chapter_id');
	$modal->value($chapter_id);
	$form_output = $modal->paint();

	$modal->input_hidden('page_id');
	$modal->value($page_id);
	$form_output .= $modal->paint();

	$modal->input_title('title');
	$modal->name('page_rename');
	$modal->value($item['title']);
	$form_output .= $modal->paint();
}

/*
// CHAPTER
elseif ( is_numeric($book_id) && is_numeric($chapter_id) ) {

	$item = $db
		-> where('id', $chapter_id)
		-> getOne('comic_chapter', 'title');

	$modal->send_to('book.edit.php');
	$modal->form_id('TEST');

	$modal->input_hidden('book_id');
	$modal->value($book_id);
	$form_output = $modal->paint();

	$modal->input_hidden('chapter_id');
	$modal->value($chapter_id);
	$form_output .= $modal->paint();

	$modal->input_title('title');
	$modal->name('chapter_rename');
	$modal->value($item['title']);
	$form_output .= $modal->paint();
}
*/
// BOOK
elseif ( is_numeric($book_id) ) {

	$item = $db
		-> where('id', $book_id)
		-> getOne('book', 'title');

	$modal->send_to('book.view.php');

	$modal->input_hidden('book_id');
	$modal->value($book_id);
	$form_output = $modal->paint();

	$modal->input_title('title');
	$modal->name('book_rename');
	$modal->value($item['title']);
	$form_output .= $modal->paint();
}
else {
	header('location:index.php');
	die();
}

//$modal->input_file('image_url');
//$modal->label('Thumbnail');
//$form_output .= $modal->paint();


/*****
 * Display
 */

$modal->multipart(true);
$modal->headline("Rename <span>$item[title]</span>");
$modal->contents($form_output);
print( $modal->paint_modal() );
