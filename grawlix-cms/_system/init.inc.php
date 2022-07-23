<?php

/* ! Setup * * * * * * * */

// Alert us if there are any problems.
error_reporting(E_ALL ^ E_NOTICE);

date_default_timezone_set('America/Los_Angeles');

session_start();

// For utf-8 support
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Need to install.
if ( file_exists('firstrun.php') && !file_exists('config.php') ) {
	header('location:firstrun.php');
}

include_once('constants.inc.php');

// Get necessary files.
$system_files = array(
	'config.php',
	'functions.inc.php'
);

foreach ( $system_files as $val ) {
	if ( file_exists($val) ) {
		include_once($val);
	}
	else {
		die ('<h1>Holy epic fail, Batman!</h1><p>The file "'.$val.'" is missing!</p>');
	}
}

if ( !$setup || !$setup['db_host'] || !$setup['db_user'] || !$setup['db_pswd'] || !$setup['db_name'] ) {
	if ( is_file('./firstrun.php'))
	{
		header('location:firstrun.php');
		die();
	}
	else
	{
		die ('<h1>Holy misalignment, Batman! Something\'s wrong with the config file!</h1><p>That\'s right, Robin. Looks like some joker\'s been having fun with vital database login info.</p>');
	}
}


/* ! Functions * * * * * * * */

function prepend_slash(&$str) {
	$str = '/'.$str;
}

function remove_first_slash(&$str) {
	$str = ltrim($str,'/');
}

function remove_trailing_slash(&$str) {
	$str = rtrim($str,'/');
}

// Return an array whose keys are based on a given bit of data per item in the array itself.
// It’s somewhat meta.
function rekey_array($list,$field='id') {
	if ( $list ) {
		foreach ( $list as $val ) {
			$key = $val[$field];
			$new_list[$key] = $val;
		}
	}
	return $new_list;
}

function get_menu_items() {
	global $_db;
	$cols = array('id,url,title,rel_type,rel_id,in_menu,sort_order');
	$result = $_db->get('path',null,$cols);
	if ( $result ) {
		$list = rekey_array($result,'id');
		// Build paths for archive items
		foreach ( $list as $key=>$val ) {
			if ( $val['rel_type'] == 'archive' ) {
				$comic_url = $list[$val['rel_id']]['url'];
				$list[$key]['url'] = $comic_url.$val['url'];
			}
		}
		$list = rekey_array($list,'url');
	}
	return $list;
}

function get_milieu_list() {
	global $_db;
	$cols = array('label,value');
	$result = $_db->get('milieu',null,$cols);
	if ( $result ) {
		foreach ( $result as $val ) {
			$list[$val['label']] = $val['value'];
		}
		if ( $list['directory'] == '/' ) {
			// Install is in root web directory
			$list['directory'] = '';
		}
		else {
			// Format directories as ‘/name’
			prepend_slash($list['directory']);
			$list['directory'] = str_replace('//','/',$list['directory']);
		}
		// Set path to site home
		$list['home_url'] = $list['directory'].'/';

		// Triple-check that double slash this one time.
		if ( $list['home_url'] == '//' )
		{
			$list['home_url'] = '/';
		}
	}
	return $list;
}

function grlx_load($class) {
	$filename = $class.'.php';
	$file = DIR_SYSTEM.$filename;
	if ( !file_exists($file) ) {
		$filename = mb_strtolower($class,"UTF-8").'.php'; // Try all lowercase
		$file = DIR_SYSTEM.$filename;
		if ( !file_exists($file) ) {
			return false;
		}
	}
	include_once($file);
}

function memory_used() {
	$usage = memory_get_usage(true);
	$unit = array('b','kb','mb','gb','tb','pb');
	return @round($usage/pow(1024,($i=floor(log($usage,1024)))),2).' '.$unit[$i];
}

function build_hyperlink($href='',$title='',$class='',$tap='',$rel='') {
	$class ? $class = ' class="'.$class.'"' : $class;
	$title ? $title = ' title="'.$title.'"' : $title;
	$rel ? $rel = ' rel="'.$rel.'"' : $rel;
	$href ? $href = ' href="'.$href.'"' : $href = '#';
	$output = '<a '.$href.$title.$class.$rel.'/>'.$tap.'</a>';
	return $output;
}

function build_head_link($attributes=array()) {
	if ( $attributes ) {
		$output = "\t\t".'<link';
		foreach ( $attributes as $key => $val ) {
			$output .= ' '.$key.'="'.$val.'"';
		}
		$output .= '/>'."\n";
	}
	return $output;
}

function build_link_list($list) {
	if ( $list ) {
		foreach ( $list as $key => $val ) {
			if ( $val['img_path'] ) {
				$img = '<img src="'.$val['img_path'].'" alt="'.$val['title'].'" /> ';
			}
			else {
				$img = '';
			}
			if ( substr($val['url'],0,4) != 'http' ) {
				$val['url'] = 'http://'.$val['url'];
			}
			$output .= '<li>'.build_hyperlink($val['url'],'','',$img.$val['title']).'</li>'."\n";
		}
	}
	return $output;
}


/* ! Load classes * * * * * * * */

require_once(DIR_SYSTEM.'MysqliDb.php');
$_db = new MysqliDb($setup['db_host'], $setup['db_user'], $setup['db_pswd'], $setup['db_name']);
$_db->setPrefix('grlx_');

if ( !$_db ) {
	die ('<h1>Holy null records, Batman! The database is missing!</h1><p>Even worse, Robin, the first-run installation script is gone. This is a critical error. <a href="http://twitter.com/grawlixcomix">Better call for backup</a>.</p>');
}

spl_autoload_register(null, false);
spl_autoload_extensions('.php');
spl_autoload_register('grlx_load');
