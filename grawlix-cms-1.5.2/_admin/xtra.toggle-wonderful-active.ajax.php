<?php


/*****
 * Setup
 */

require_once('panl.init.php');


/*****
 * Updates
 */

// Make an item active or inactive based on its match id
if ( ($_SERVER['REQUEST_METHOD'] == 'GET') && $_GET['type_adbox_id'] ) {

	$class = explode(' ',$_GET['class_info']);
	$mysql_id = $class[2];
	$mysql_id = explode('-', $mysql_id);
	$mysql_id = $mysql_id[1];
	$type_adbox_id = explode('-',$_GET['type_adbox_id']);

	$result = $db
		-> where('label', $type_adbox_id[1])
		-> where('value', $type_adbox_id[2])
		-> get('third_data', null, 'id');
	$existing_third_data_id = $result['id'];

	if ( !$existing_third_data_id || $existing_third_data_id == '' ) {
		$sql = "
INSERT INTO third_data
	(label,value,name_id)
VALUES
	('$type_adbox_id[1]','$type_adbox_id[2]',15)";
		$db -> query($sql);
		$db -> close();
	}

	if ( $existing_third_data_id && is_numeric($existing_third_data_id) ) {
		$sql = "
DELETE FROM
	third_data
WHERE
	label = '$type_adbox_id[1]'
	AND value = '$type_adbox_id[2]'
";
		$db -> query($sql);
		$db -> close();
	}

}

