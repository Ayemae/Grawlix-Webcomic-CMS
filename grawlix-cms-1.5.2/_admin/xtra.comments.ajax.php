<?php

/* This script is called from the extra services script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$modal = new GrlxForm_Modal;
$modal->send_to('xtra.social.php');
$modal->row_class('widelabel');


/*****
 * Display logic
 */

if ( is_numeric($_GET['service_id']) ) {

	$service_id = $_GET['service_id'];

	$cols = array(
		'info_title AS label',
		'user_info',
		'title',
		'description'
	);
	$result = $db
		-> where('id', $service_id)
		-> getOne('third_service', $cols);
}

if ( $result ) {
	$modal->input_hidden('service_id');
	$modal->value($service_id);
	$modal_output = $modal->paint();

	$modal->input_text('comment_info');
	$modal->label("Enter your $result[label]");
	$modal->value($result['user_info']);
	$modal->autofocus(true);
	$modal->required(true);
	$modal->maxlength(32);
	$modal_output .= $modal->paint();

	$modal->headline("Enter your details <span>$result[title]</span>");
	$modal->instructions('<p>Here’s a sample of the code that '.$result['title'].' provides. The highlighted portion shows the info Grawlix needs.</p><div class="infobox"><code>'.$result['description'].'</code></div>');
	$modal->contents($modal_output);
}

$modal_output = $modal->paint_modal();


/*****
 * Display
 */

print($modal_output);
