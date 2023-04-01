<?php

/* ! Init * * * * * * * */

session_start();

// for utf-8 support
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Alert us if there are any problems.
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('America/Los_Angeles'); // Just in case. The user's timezone gets set later, after we've fetched the milieu data.

// Get required data for this specific website.
if ( !@include('../config.php')) {
	die('Holy 404, Batman! The config file isn\'t answering the bat-phone!');
}
if ( !@include('inc/functions.inc.php')) {
	die ('We\'re missing some essential functions. Don\'t blame me, I voted for Bill n\' Opus.');
}

// Types of image files we let artists upload.
$allowed_image_types = array
(
	'image/png',
	'image/gif',
	'image/jpeg',
	'image/svg'
);

include_once('../_system/constants.inc.php');
include_once('../_system/xss_clean.php');

/* ! Autoload * * * * * * * */

spl_autoload_register(null, false);
spl_autoload_extensions('.php');

function grlx_load($class) {
	$filename = $class.'.php';
	$file = 'lib/'.$filename;
	if ( !file_exists($file) ) {
		$filename = mb_strtolower($class,"UTF-8").'.php'; // Try all lowercase
		$file = 'lib/'.$filename;
		if ( !file_exists($file) ) {
			return false;
		}
	}
	include_once($file);
}
spl_autoload_register('grlx_load');

// Is the config file complete?
if (
	empty($setup['db_host']) ||
	empty($setup['db_user']) ||
	empty($setup['db_pswd']) ||
	empty($setup['db_name'])
) {
	die('Holy hole in the plot, Batman! Joker\'s made a laugh of the config file!');
}

// MySQLi wrapper
require_once('../_system/MysqliDb.php');
$db = new MysqliDb($setup['db_host'], $setup['db_user'], $setup['db_pswd'], $setup['db_name']);
$db-> setPrefix('grlx_');

// Grawlix db class
$db_ops = new GrlxDbOps($db);

// echo '<pre>$_SESSION|';print_r($_SESSION);echo '|</pre>';


/* ! Check security * * * * * * * */

if ( empty($except) || !$except ) {
	if ( empty($_SESSION['admin']) ) {
		header('location:panl.login.php?ref='.$_SERVER['REQUEST_URI']);
		die('no session');
	}
	else {
		$maybe_serial = $_SESSION['admin'];

		$db->where ('serial', $maybe_serial);
		$result = $db->get ('user',null,'id');

		$maybe_admin = $result[0];
	}
	if ( !$maybe_admin ) {
		header('location:panl.login.php');
		die('no permission');
	}
	if ( !empty($_POST) && !empty($_POST['grlx_xss_token']) && !empty($_SESSION['admin']) && $_POST['grlx_xss_token'] != $_SESSION['admin'] ) {
		die('invalid form');
	}
}

$frequency_list_init = display_pretty_publish_frequency();

// Get vital milieu data
$milieu_list = get_site_milieu($db);

//Set the timezone:
if(isset($milieu_list['timezone']) && isset($milieu_list['timezone']['value'])) {
	date_default_timezone_set($milieu_list['timezone']['value']);
	//Set the database session timezone:
	$db->setTimezone($milieu_list['timezone']['value']);
}

header("Content-Type: text/html; charset=utf-8");
