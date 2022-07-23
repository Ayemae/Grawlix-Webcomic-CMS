<?php

/* This script is called from the theme options script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$modal = new GrlxForm_Modal();


/*****
 * Display logic
 */

if ( is_numeric($_GET['theme_id']) && is_numeric($_GET['tone_id']) ) {
	$theme_id = $_GET['theme_id'];
	$tone_id = $_GET['tone_id'];
}
else {
	die('invalid data');
}

$result = $db
	-> where('theme_id', $theme_id)
	-> where('id', $tone_id)
	-> orderBy('title', 'ASC')
	-> getOne('theme_tone', 'title');
$tone_title = ucfirst($result['title']);

if ( $db-> count <= 0 ) {
	die('invalid ID');
}

$modal->send_to('site.theme-options.php');

$modal->input_hidden('theme_id');
$modal->value($theme_id);
$form_output = $modal->paint();

$modal->input_hidden('tone_id');
$modal->value($tone_id);
$form_output .= $modal->paint();

$modal->input_title('tone_title');
$modal->label('Enter a name');
$modal->maxlength(32);
$form_output .= $modal->paint();


/*****
 * Display
 */

$modal->headline('New tone <span>using “'.$tone_title.'” as a base</span>');
$modal->contents($form_output);
print( $modal->paint_modal() );
