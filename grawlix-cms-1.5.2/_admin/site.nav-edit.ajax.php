<?php

/* This script is called from the main site menu script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$modal = new GrlxForm_Modal;
$modal->row_class('widelabel');

$edit_id = register_variable('edit_id');

if ( $edit_id && is_numeric($edit_id) ) {
	$cols = array(
		'title',
		'url',
		'rel_id',
		'rel_type',
		'edit_path'
	);
	$item = $db
		-> where('id', $edit_id)
		-> getOne('path', $cols);
}
// Any new items can only be external links
elseif ( $edit_id === 'new' ) {
	$item['rel_type'] = 'external';
}
else {
	die('Invalid ID');
}


/*****
 * Display logic
 */

if ( $edit_id == 'new' ) {
	$modal->headline('Add <span>external link</span>');
	$modal->save_value('add');
}
else {
	$modal->headline('Edit <span>'.$item['title'].'</span>');
	$modal->input_hidden('edit_id');
	$modal->value($edit_id);
	$hidden_output = $modal->paint();
}

// Edits based on rel_type
if ( $item['rel_type'] == 'external' ) {
	if ( $item['url'] == '' ) {
		$item['url'] = 'http://';
	}
	$modal->input_url('url');
}
else {
	$modal->input_path('url');
	if ( $item['edit_path'] == 0 ) {
		$modal->readonly(true);
	}
}

if ( $item['rel_type'] == 'archive' ) {
	$comic = $db
		-> where('id', $item['rel_id'])
		-> getOne('path', 'url');
	$modal->prefix($comic['url']);
}

$modal->value($item['url']);
$modal->name('input[url]');
$url_field_output = $modal->paint();

$modal->input_clickable('title');
$modal->name('input[title]');
$modal->value($item['title']);
$title_field_output = $modal->paint();

$modal->contents($hidden_output.$title_field_output.$url_field_output);


/*****
 * Display
 */

print( $modal->paint_modal() );
