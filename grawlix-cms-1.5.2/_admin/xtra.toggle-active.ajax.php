<?php

/* This script is called from the extra services script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

// Get match id from string
$parts = explode('-', $_GET['toggle']);
if ( is_numeric($parts[1]) ) {
	$match_id = $parts[1];
}

// Get the current visibility
$parts = explode(' ', $_GET['class']);
if ( $parts ) {
	foreach ( $parts as $part ) {
		if ( substr($part, 0, 4) == 'vis_' ) {
			$vis = explode('_', $part);
			( $vis[1] == 1 ) ? $vis_set = 0 : $vis_set = 1;
		}
	}
}


/*****
 * Updates
 */

// Make an item active or inactive based on its match id
if ( $match_id ) {
	$data = array('active' => $vis_set);
	$db -> where('id', $match_id);
	$db -> update('third_match', $data);
}