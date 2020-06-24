<?php

/* This script is called from the site nav script.
 */

/*****
 * Setup
 */

require_once('panl.init.php');


/*****
 * Updates
 */

// Delete an item from the site nav. External links only.
if ( $_GET['delete'] ) {

	$id = explode('-',$_GET['delete']);
	$id = $id[1];

	if ( is_numeric($id) ) {
		$db -> where('id', $id);
		$db -> where('rel_type', 'external');
		$db -> delete('path');
	}
}