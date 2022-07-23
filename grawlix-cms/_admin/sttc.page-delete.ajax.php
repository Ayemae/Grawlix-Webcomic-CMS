<?php

/* This script is called from the static page view.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$modal = new GrlxForm_Modal;
$modal->send_to('sttc.page-list.php');


/*****
 * Display logic
 */

// Delete a static page
if ( is_numeric($_GET['id']) ) {
	$title = urldecode($_GET['title']);
	if ( $title ) {
		$modal->headline('Really delete <span>'.$title.' ?</span>');
	}
	else {
		$modal->headline('Are you really, really sure?');
	}
	$modal->instructions('There is no undo.');
	$modal->input_hidden('delete_id');
	$modal->value($_GET['id']);
	$modal_output = $modal->paint();
}

$modal->contents($modal_output);
$modal_output = $modal->paint_confirm_modal();


/*****
 * Display
 */

print($modal_output);
