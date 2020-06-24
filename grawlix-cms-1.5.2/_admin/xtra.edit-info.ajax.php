<?php

/* This script is called from the extra services script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$modal = new GrlxForm_Modal;
$modal->row_class('widelabel');
$modal->send_to('xtra.social.php');

$var_list = array('service_id', 'match_id', 'widget_id');
foreach ( $var_list as $var_name ) {
	if ( is_numeric($_GET[$var_name]) ) {
		$$var_name = $_GET[$var_name];
	}
}


/*****
 * Display logic
 */

// Widgets
if ( $widget_id ) {
	$cols = array(
		'w.id AS widget_id',
		's.id AS service_id',
		's.title AS title',
		'w.title AS widget',
		's.info_title',
		's.user_info',
		'w.value_title AS widget_info',
		'w.value AS widget_value',
		'w.description AS description'
	);
	$result = $db
		-> join('third_service s', 's.id = w.service_id', 'INNER')
		-> orderBy('s.title', 'ASC')
		-> orderBy('w.title', 'ASC')
		-> where('w.id', $widget_id)
		-> get('third_widget w', null, $cols);
}

// Everything else
if ( $service_id ) {
	$cols = array(
		'id AS service_id',
		'title',
		'user_info',
		'info_title',
		'description'
	);
	$result = $db
		-> where('id', $service_id)
		-> get('third_service', null, $cols);
}

// Build form
if ( $db->count > 0 ) {
	foreach ( $result as $item ) {
		$modal->instructions($item['description']);

		$modal->input_text("edit[$item[service_id]]");
		$modal->label("Enter your $item[info_title]");
		$modal->autofocus(true);
		$modal->required(true);
		$modal->value($item['user_info']);
		$modal->maxlength(32);
		$modal_output .= $modal->paint();

		if ( $widget_id ) {
			$modal->input_text('widget_value');
			$modal->label("Enter your $item[widget_info]");
			$modal->required(true);
			$modal->value($item['widget_value']);
			$modal->maxlength(32);
			$modal_output .= $modal->paint();
			$modal->headline('Enter your details <span>'.$item['title'].' '.lcfirst($item['widget']).'</span>');
		}
		else {
			$modal->headline("Enter your details <span>$item[title]</span>");
		}
	}
	if ( $match_id ) {
		$modal->input_hidden('match_id');
		$modal->value($match_id);
		$modal_output .= $modal->paint();
	}
	if ( $widget_id ) {
		$modal->input_hidden('widget_id');
		$modal->value($widget_id);
		$modal_output .= $modal->paint();
	}
}

$modal->contents($modal_output);
$modal_output = $modal->paint_modal();


/*****
 * Display
 */

print($modal_output);
