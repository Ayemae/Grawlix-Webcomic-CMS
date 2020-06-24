<?php

/* This script is called from the link list script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');


/*****
 * Updates
 */

// Delete an item from the link list
if ( $_GET['delete'] ) {

	$id = explode('-',$_GET['delete']);
	$id = $id[1];

	if ( is_numeric($id) ) {
		$db -> where('id', $id);
		$db -> delete('link_list');
	}
}
