<?php

/* This script resorts lists of various items.
 */

/*****
 * Setup
 */

require_once('panl.init.php');


/*****
 * Chapters
 */

/*
if ( $_POST['chapter'] ) {
	$chapter_list = $_POST['chapter'];
	$total = count($chapter_list);
	$i = 1;
	foreach ( $chapter_list as $val ) {
		$data = array('sort_order' => $i);
		$db -> where('id', $val);
		$db -> update('comic_chapter', $data);
		$i+=1;
	}
	$book_id = get_comic_book_id($db);
	reset_page_order($book_id,$db);
}
*/


/*****
 * Pages
 */

if ( $_POST['page'] ) {
	$page_list = $_POST['page'];
	$total = count($page_list);
	$i = 1;
	foreach ( $page_list as $val ) {
		$data = array('sort_order' => $i);
		$db -> where('id', $val);
		$db -> update('book_page', $data);
		$i+=1;
	}
	$book_id = get_comic_book_id($db);
	reset_page_order($book_id,$db);
}


/*****
 * Images
 */

if ( $_POST['image_match_id'] ) {
	$i = 1;
	foreach ( $_POST['image_match_id'] as $val ) {
		$data = array('sort_order' => $i);
		$db -> where('id', $val);
		$db -> update('image_match', $data);
		$i++;
	}
}


/*****
 * Site menu items
 */

if ( $_POST['menu'] ) {
	$i = 1;
	foreach ( $_POST['menu'] as $val ) {
		$data = array('sort_order' => $i);
		$db -> where('id', $val);
		$db -> update('path', $data);
		$i++;
	}
}


/*****
 * Link list items
 */

if ( $_POST['link'] ) {
	$i = 1;
	foreach ( $_POST['link'] as $val ) {
		$data = array('sort_order' => $i);
		$db -> where('id', $val);
		$db -> update('link_list', $data);
		$i++;
	}
}
