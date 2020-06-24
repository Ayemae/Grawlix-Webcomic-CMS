<?php

/**
 * Update an existing tone record.
 *
 * @param string $_GET['item'] = db table name w/out prefix.'-'.value of the record’s id field
 * @param int $_GET['tone_id'] = the tone id for the record
 */

require_once('panl.init.php');

$db_vars = strfunc_split_tablerow($_GET['item']);
$tone_id = $_GET['tone_id'];

if ( is_numeric($tone_id) && is_numeric($db_vars['id']) && $db_vars['table'] ) {
	$db_vars['table'] == 'milieu' ? $col = 'value' : $col = 'tone_id';
	$data = array($col=>$tone_id);
	$db->where('id',$db_vars['id']);
	$db->update($db_vars['table'],$data);
}