<?php

/* This script toggles the visibility of many item types.
 */

/*****
 * Setup
 */

require_once('panl.init.php');


/*****
 * Updates
 */

if ( isset($_GET['class']) ) {
	$vis_set = strfunc_toggle_vis($_GET['class']);

	if ( isset($_GET['site-menu']) ) {
		$id = strfunc_get_id($_GET['site-menu']);
		if ( $id ) {
			$data = array('in_menu' => $vis_set);
			$db -> where('id', $id);
			$db -> update('path', $data);
		}
	}

	if ( isset($_GET['social']) ) {
		$id = strfunc_get_id($_GET['social']);
		if ( $id ) {
			$data = array('active' => $vis_set);
			$db -> where('id', $id);
			$db -> update('third_match', $data);
		}
	}

	if ( isset($_GET['widget']) ) {
		$id = strfunc_get_id($_GET['widget']);
		if ( $id ) {
			$data = array('active' => $vis_set);
			$db -> where('id', $id);
			$db -> update('third_widget', $data);
		}
	}
}
