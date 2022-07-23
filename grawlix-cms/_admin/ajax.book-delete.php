<?php

/* This script deletes a comic chapter or page.
 */

/*****
 * Setup
 */

require_once('panl.init.php');


/*****
 * Chapter
 */

if ( $_GET['delete-chapter'] ) {

	$id = strfunc_get_id($_GET['delete-chapter']);

	if ( $id ) {
		delete_chapter($id, $db);
	}
}


/*****
 * Page
 */

if ( $_GET['delete-page'] ) {

	$id = strfunc_get_id($_GET['delete-page']);

	if ( $id ) {
		delete_comic_page($id, $db);
	}
}
