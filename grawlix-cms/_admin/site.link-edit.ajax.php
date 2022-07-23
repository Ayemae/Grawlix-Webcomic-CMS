<?php

/* This script is called from the link list script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$modal = new GrlxForm_Modal;


/*****
 * Display logic
 */

if ( is_numeric($_GET['edit_id']) ) {

	$edit_id = $_GET['edit_id'];
	$cols = array(
		'title',
		'img_path',
		'url'
	);
	$item = $db
		-> where('id', $edit_id)
		-> getOne('link_list', $cols);
	if ( $db -> count > 0 ) {
		$input = $item;
	}
}

$modal->multipart(true);
$modal->send_to('site.link-list.php');

$modal->input_hidden('edit_id');
$modal->value($edit_id);
$modal_output = $modal->paint();

$modal->input_title('input[title]');
$modal->autofocus(true);
$modal->value($input['title']);
$modal_output .= $modal->paint();

$modal->input_url('input[url]');
$modal->value($input['url']);
$modal_output .= $modal->paint();

$modal->input_file('input[img_path]');
$modal->label('Icon');
$modal_output .= $modal->paint();

$modal->input_hidden('old_img_url');
$modal->value($input['img_path']);
$modal_output .= $modal->paint();

$modal->headline("Edit <span>$input[title]</span>");
$modal->contents($modal_output);
$modal_output = $modal->paint_modal();


/*****
 * Display
 */

print($modal_output);
