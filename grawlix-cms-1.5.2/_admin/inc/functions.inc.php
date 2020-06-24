<?php

function numfunc_check_empty($int) {
	$int = intval($int);
	if ( $int < 1 ) {
		$int = 0;
	}
	return $int;
}

function numfunc_register_var($str=null){
	if ($str) {
		$var = $_POST[$str];
		$var ? $var : $var = $_GET[$str];
		$var ? $var : $var = $_SESSION[$str];
	}
	$var = numfunc_check_empty($var);
	return $var;
}

function strfunc_check_empty($str) {
	$str = trim($str);
	if ( $str === null || $str == '' ) {
		$str = '—';
	}
	return $str;
}

// get ID off a string formatted like so: id-3
function strfunc_get_id($str) {
	$part = explode('-', $str);
	$int = end($part);
	if ( is_numeric($int) ) {
		return $int;
	}
	else {
		return null;
	}
}

// get table name and row ID from a string formatted like so: milieu-9
function strfunc_split_tablerow($str) {
	$part = explode('-', $str);
	if ( is_numeric(end($part)) ) {
		$var['table'] = $part[0];
		$var['id'] = end($part);
		return $var;
	}
	else {
		return null;
	}
}

function strfunc_li_wrap(&$str) {
	$str = '<li>'.$str.'</li>';
}

function strfunc_make_title($str) {
	return ucwords(str_replace('_',' ',$str));
}

// get the visibility of an item by finding the “vis_” css class name
// return the opposite setting
function strfunc_toggle_vis($str) {
	$names = explode(' ', $str);
	foreach ( $names as $name ) {
		if ( substr($name, 0, 4) == 'vis_' ) {
			$part = explode('_', $name);
			$from = end($part);
			$from == 1 ? $to = 0 : $to = 1;
		}
	}
	return $to;
}

/* Turn this:
		Array(
			[0]=>Array([column]=>val1)
			[1]=>Array([column]=>val2)
		)
	into:
		Array(
			[0]=>val1
			[1]=>val2
		)
*/
function flatten_array(&$arr) {
	if ( $arr ) {
		foreach ( $arr as $key=>$val ) {
			foreach ( $val as $str ) {
				$flat[] = $str;
			}
		}
	}
	$arr = $flat;
}

// Get the id for the one comic
function get_comic_book_id($db) {
	$db->orderBy('sort_order','DESC');
	$result = $db->getOne('book','id');
	return $result['id'];
}

// Return an array whose keys are based on a given bit of data per item in the array itself.
// It’s somewhat meta.
function rekey_array($list,$field='id'){
	if ( is_array($list) ) {
		foreach ( $list as $val ) {
			$key = $val[$field];
			$new_list[$key] = $val;
		}
	}
	return $new_list;
}

function upload_specific_file($which,$path){
	if ( is_array($_FILES[$which]['name']) ) {
		$file_name = basename($_FILES[$which]['name'][0]); // To do: make this handle multiples
	}
	else {
		$file_name = basename($_FILES[$which]['name']);

	}
	$uploadfile = '..'.$path.'/' . $file_name;
	$web_file_path = $path.'/' . $file_name;

	if ( is_array($_FILES[$which]['tmp_name']) ) {
		if (move_uploaded_file($_FILES[$which]['tmp_name'][0], $uploadfile)) {  // To do: make this handle multiples
			return array('success','File uploaded.');
		}
	}
	else {
		if (move_uploaded_file($_FILES[$which]['tmp_name'], $uploadfile)) {
			return array('success','File uploaded.');
		}
	}
	return array('alert','File failed to upload.');
}

/*
function upload_multiple_files($path){
	if ( $_FILES ) {
		foreach ( $_FILES as $superkey => $info ) {
			if ( $info['name'] ) {
				foreach ( $info['name'] as $key => $file_name ) {
					$tmp = $info['tmp_name'][$key];
					$uploadfile = '..'.$path.'/' . $file_name;
					// CB put this here
					if ( substr($uploadfile, 0, 4) == '....' ) {
						$x = strlen($uploadfile);
						$uploadfile = substr($uploadfile, 2, $x);
					}
					$web_file_path = $path.'/' . $file_name;
					if (move_uploaded_file($tmp, $uploadfile)) {}
					else {
					}
				}
			}
		}
	}
}
*/



/* Clean user-submitted text.
 */
function clean_text( $text, $strip_tags=true, $limit=2000 ) {
	$text = trim($text); // Remove excess space.
	$text = substr($text, 0, $limit); // Keep things to an accepted byte size.
	if ($strip_tags) {
		$text = strip_tags($text); // No XML!
	}
	$text = preg_replace('/\s\s+/', ' ', $text); // strip excess whitespace
	$text = str_replace(';', '', $text); // Dodge MySQL injections.
	$text = str_replace('javascript', '', $text); // Yeeeeeah … no.
	$text = htmlEntities($text, ENT_QUOTES);
	$text = str_replace("'","&#8217;",$text);
	return $text;
}

function check_type($value,$expected)
{
	switch ($expected)
	{
		case 'int':
			if (is_numeric($value))
			{
				return TRUE;
			}
			break;

		case 'string':
		case 'html':
			if (is_string($value))
			{
				return TRUE;
			}
			break;

		case 'float':
			if (is_float($value))
			{
				return TRUE;
			}
			break;
	}
	return FALSE;
}

function register_variable($variable_name=NULL,$variable_type=NULL){
	if ($variable_name) {
		$var = $_POST[$variable_name];
		$var ? $var : $var = $_GET[$variable_name];
		$var ? $var : $var = $_SESSION[$variable_name];
	}
	if ( is_array($var) ) {
		foreach ( $var as $key => $val ) {
			if ( $variable_type[$key] )
			{
				$valid_type = check_type($val,$variable_type);
			}
			else
			{
				$valid_type = TRUE; // Retain legacy checks … for now.
			}
			if ( $valid_type === TRUE )
			{
				$var[$key] = clean_text($val,FALSE);
				return $var;
			}
		}
	}
	else {
		if ( $variable_type )
		{
			// Is this what we expect?
			$valid_type = check_type($var,$variable_type);
		}
		else
		{
			// Retain legacy checks … for now.
			$valid_type = TRUE;
		}

		if ( $valid_type === TRUE )
		{
			if ( $variable_type == 'html' )
			{
				$var = clean_text($var,FALSE); // Allow tags
			}
			else
			{
				$var = clean_text($var,TRUE); // Remove tags
			}
			return $var;
		}
	}
	return FALSE;
}

// Generate a simple Foundation alert box
/*
function generate_alert( $class='', $text ) {
	$output  = '<div data-alert class="alert-box '.$class.'">'."\n";
	$output .= '	<a href="#" class="close"></a><section>'."\n";
	if ( $class == 'success' ) {
		$text = '<h4>Hurrah!</h4>'.$text;
	}
	if ( $class == 'alert' ) {
		$output .= '<img src="../assets/system/images/balloon-alert.svg" /><div>'.$text.'</div>'."\n";
	}
	else {
		$output .= '<img src="../assets/system/images/balloon-info.svg" /><div>'.$text.'</div>'."\n";
	}
	$output .= '</section></div>'."\n";
	return $output;
}
*/


function get_image_reference($db){
	$db->orderBy ('url','ASC');
	$result = $db->get ('image_reference',null,'url,id');
	$result = rekey_array($result,'url');

	return $result;
}

function figure_pixel_weight($width,$height,$kb){
	$result = $kb/($width*$height);
	return $result;
}
function interpret_image_weight($value,$bias = 1){
	// Higher bias = more lenient
	if ( $value < 0.125 * $bias ) {
		$status = 0;
	}
	elseif ( $value < 0.25 * $bias ) {
		$status = 1;
	}
	elseif ( $value < 0.4 * $bias ) {
		$status = 2;
	}
	elseif ( $value < 0.8 * $bias ) {
		$status = 3;
	}
	else {
		$status = 4;
	}
	return $status;
}



function build_select_simple($name='',$list=array(), $current='', $style=NULL){
	if($style)
	{
		$style = ' style="'.$style.'"';
	}
	$output .= '<select name="'.$name.'" id="'.$name.'"'.$style.'>'."\n";
	foreach ( $list as $key => $val ) {
		$current == $key ? $sel = ' selected="selected"' : $sel = '';
		$output .= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>'."\n";
	}
	$output .= '</select>'."\n";
	return $output;
}

/*
function build_select_val_as_key( $name = '', $list = array(), $current = '' ) {
	$output .= '<select name="'.$name.'" id="'.$name.'">'."\n";
	foreach ( $list as $key => $val ) {
		$current == $val ? $sel = ' selected="selected"' : $sel = '';
		$output .= '<option value="'.$val.'"'.$sel.'>'.$val.'</option>'."\n";
	}
	$output .= '</select>'."\n";
	return $output;
}
*/

/*
function build_radio($name='',$list=array(), $current=''){
	foreach ( $list as $key => $val ) {
		$current == $key ? $sel = ' checked="checked"' : $sel = '';
		$output .= '<input type="radio" name="'.$name.'" value="'.$key.'"'.$sel.'/>'.$val."\n";
	}
	return $output;
}
*/

function fetch_static_page_info($page_id=null,$db){
	if ( $page_id && is_numeric($page_id) ) {

		$sql = "
SELECT
	sp.title,
	sp.description,
	sp.id,
	options,
	sn.title AS nav_title,
	url
FROM
	".DB_PREFIX."static_page sp,
	".DB_PREFIX."path sn
WHERE
	sp.id = ?
	AND sp.id = sn.rel_id
	AND sn.rel_type = 'static'";

		$page_info = $db->rawQuery($sql,array($page_id));

		if ( $page_info ) {
			$page_info = $page_info[0];
		}

		$page_info['layout_info'] = interpret_xml_layout($page_info['layout']);
	}
	return $page_info;
}

function interpret_xml_layout($string){
	$set = explode('-',$string);
	$icon_path = '../assets/layouts/'.$set[1].'-'.$set[2].'.png';
//	$repeat = $set[0];
	$xml_format = $set[0];
	$arrangement = $set[1];
	return array (
		'icon_path' => $icon_path,
		'repeat' => $repeat,
		'xml_format' => $xml_format,
		'arrangement' => $arrangement
	);
}



/*
function get_static_pages($db){
  $sql = '
SELECT
	p.title,
	description,
	n.id AS nav_id,
	DATE_FORMAT(p.date_modified,"%b %d, %Y") AS date,
	edit_path,
	url,
	p.id AS page_id
FROM
	'.DB_PREFIX.'static_page p,
	'.DB_PREFIX.'path n
WHERE
	p.id = rel_id
	AND rel_type = "static"
ORDER BY
	edit_path DESC,
	p.title ASC';

	$static_list = $db->rawQuery($sql,null);

	if ( $static_list ) {
		$static_list = rekey_array($static_list,'page_id');
		foreach ( $static_list as $key => $val ) {
			$static_list[$key]['layout_info'] = interpret_xml_layout($val['layout']);
		}
	}

	return $static_list;
}
*/

function get_site_milieu($db){
	$cols = array('label, value, id');
	$result = $db->get('milieu', null, $cols);
	$result = rekey_array($result,'label');

	// An exception
	$result['directory']['value'] == '/' ? $result['directory']['value'] = '' : $result['directory']['value'];
	return $result;
}

// Exactly what it sounds like.
/*
function count_books($db){
	$result = $db->get ('book',null,'COUNT(id) AS tally');
	if ( $result ) {
		$result = $result[0]['tally'];
	}
	return $result;
}
*/

/*
function get_comic_books($db,$limit=null){
//	$query = "SELECT title, publish_frequency, sort_order, id FROM comic_book ORDER BY sort_order, title";
	$db->orderBy('sort_order,title','ASC');
	$comic_list = $db->get('book',$limit,'title,publish_frequency,sort_order,id');
	$comic_list = rekey_array($comic_list,'id');

	if ( $comic_list ) {
		foreach ( $comic_list as $key => $val ) {

			$db->where('comic_book_id',$key);
			$result = $db->get('comic_chapter',null,'COUNT(comic_book_id) AS tally');
			$comic_list[$key]['chapter_count'] = $result[0]['tally'];
		}

	}
	return $comic_list;
}
*/


/*
function get_comic_book_info($book_id,$db){

	$sql = "
SELECT
	url,
	cb.title,
	publish_frequency,
	date_start,
	options,
	cb.id
FROM
	".DB_PREFIX."book cb,
	".DB_PREFIX."path sn
WHERE
	cb.id = ?
	AND cb.id = sn.rel_id
	AND sn.rel_type = 'comic'";

	$info = $db->rawQuery($sql,array($book_id));
	if ( $info ) {
		$info = $info[0];
	}
	return $info;
}
*/

/*
function get_last_book_info($db){
//	$query = "SELECT id,sort_order FROM comic_book ORDER BY sort_order DESC, title ASC LIMIT 1";

	$db->orderBy ('sort_order','DESC');
	$db->orderBy ('title','ASC');
	$result = $db->get ('book',1,'id,sort_order');

	return $result[0];
}
*/

/*
function get_book_pages_by_chapter_id($chapter_id,$db){
	$sql = "
SELECT
	cc2.id,
	cp.id,
	cp.sort_order
FROM
	grlx_comic_book cb,
	grlx_comic_chapter cc1,
	grlx_comic_chapter cc2,
	grlx_book_page cp
WHERE
	cc1.id = ?
	AND cc2.comic_book_id = cb.id
	AND cp.chapter_id = cc2.id
ORDER BY
	cp.sort_order ASC
";
	$info = $db -> rawQuery($sql,array($chapter_id));
	return $info;
}
*/


/*
function f($page_id,$db){
	$sql = "
SELECT
	cb.id,
	sn.url
FROM
	grlx_comic_book cb,
	grlx_comic_chapter cc,
	grlx_book_page cp,
	grlx_path sn
WHERE
	cp.id = ?
	AND cp.chapter_id = cc.id
	AND cc.comic_book_id = cb.id
	AND sn.rel_id = cb.id
	AND sn.rel_type = 'comic'
";

	$comic_book_id = $db->rawQuery($sql,array($page_id));
	if ( $comic_book_id ) {
		$comic_book_id = $comic_book_id[0];
	}

	return $comic_book_id;
}
*/

/*
function get_comic_page_info($page_id,$db){

	$sql = "
SELECT
	url,
	book_id,
	cp.title,
	cp.sort_order,
	blog_title,
	blog_post,
	transcript,
	cp.id
FROM
	".DB_PREFIX."book_page cp,
	".DB_PREFIX."path sn
WHERE
	cp.id = ?
	AND sn.rel_type = 'comic'";


	$info = $db->rawQuery($sql,array($page_id));

	if ( $info ) {
		$info = $info[0];
	}

	if ( $info ) {
		$info['images'] = get_page_images($page_id,$db);
	}
	return $info;
}
*/

function get_page_images($page_id,$db){
	$sql = "
SELECT
	url,
	description,
	ir.id AS image_reference_id,
	im.id
FROM
	".DB_PREFIX."image_reference ir,
	".DB_PREFIX."image_match im
WHERE
	im.rel_id = ?
	AND im.rel_type = 'comic'
	AND im.image_reference_id = ir.id
ORDER BY
	im.sort_order
";

	$info = $db->rawQuery($sql,array($page_id));

	$info = rekey_array($info,'id');

	return $info;
}

/*
function get_chapter_info($chapter_id,$db){

	$db->where ('id', $chapter_id);
	$result = $db->get ('comic_chapter',null,'title,thumbnail_id,comic_book_id,id');
	$result = $result[0];

	return $result;
}
*/


/*
function get_pages_by_chapter($book_id,$db){
	$sql = "
SELECT
	cp.id
FROM
	grlx_comic_chapter cc,
	grlx_book_page cp
WHERE
	cc.comic_book_id = ?
	AND cp.chapter_id = cc.id
	AND cp.date_publish <= NOW()
ORDER BY
	cc.sort_order ASC,
	cp.sort_order ASC
";
	$info = $db->rawQuery($sql,array($book_id));
	$info = rekey_array($info,'id');
	return $info;
}
*/


/*
function get_book_upcoming_page($book_id,$db){
	$sql = "SELECT
	cp.date_publish,
	DATE_FORMAT(date_publish,'%a, %b %e %Y') AS date_publish_human,
	cp.id
FROM
	grlx_comic_chapter cc,
	grlx_book_page cp
WHERE
	cc.comic_book_id = ?
	AND cp.chapter_id = cc.id
	AND cp.date_publish >= NOW()
ORDER BY
	cc.sort_order ASC,
	cp.sort_order ASC
LIMIT
	1";

	$page_info = $db->rawQuery($sql,array($book_id));
	if ( $page_info ) {
		$page_info = $page_info[0];
	}

	return $page_info;
}
*/

/*
function get_book_most_recent_page($book_id,$db){
	$sql = "SELECT
	cp.date_publish,
	DATE_FORMAT(date_publish,'%a, %b %e %Y') AS date_publish_human,
	cp.id
FROM
	grlx_comic_chapter cc,
	grlx_book_page cp
WHERE
	cc.comic_book_id = ?
	AND cp.chapter_id = cc.id
	AND cp.date_publish <= NOW()
ORDER BY
	cc.sort_order DESC,
	cp.sort_order DESC
LIMIT
	1";

	$page_info = $db->rawQuery($sql,array($book_id));
	if ( $page_info ) {
		$page_info = $page_info[0];
	}

	return $page_info;
}
*/

/*
function get_book_chapters($book_id,$db){
	if ( $book_id ) {
		$query = "
SELECT
	title,
	sort_order,
	tone_id,
	id
FROM
	grlx_comic_chapter
WHERE
	comic_book_id = '$book_id'
ORDER BY
	sort_order ASC
";
		$db->where ('comic_book_id', $book_id);
		$db->orderBy ('sort_order','ASC');
		$list = $db->get ('comic_chapter',null,'title,sort_order,tone_id,id');
		$list = rekey_array($list,'id');
	}

	if ( $list ) {
		foreach ( $list as $key => $val ) {
		$db->where ('chapter_id', $key);
		$result = $db->get ('comic_page',null,'COUNT(id) AS tally');
		if ( $result ) {
			$tally = $result[0]['tally'];
		}

		$list[$key]['page_count'] = $tally;

	$db->where ('chapter_id', $key);
	$db->orderBy ('date_publish','ASC');
	$result = $db->get ('comic_page',1,'date_publish');
	if ( $result ) {
		$first = $result[0];
	}


//		$first = db_peek($query,$db);
		$list[$key]['first_date'] = $first['date_publish'];
		$list[$key]['first_date_human'] = format_date($first['date_publish'],'D, F j');

//		$query = "SELECT date_publish FROM comic_page WHERE chapter_id = '$key' ORDER BY date_publish DESC LIMIT 1";
	$db->where ('chapter_id', $key);
	$db->orderBy ('date_publish','DESC');
	$result = $db->get ('comic_page',1,'date_publish');
	if ( $result ) {
		$last = $result[0];
	}


//		$last = db_peek($query,$db);
		$list[$key]['last_date'] = $last['date_publish'];
		$list[$key]['last_date_human'] = format_date($last['date_publish'],'D, F j');

		}
	}

	return $list;
}
*/

/*
function get_chapter_pages($chapter_id,$db){

	$db->where ('chapter_id', $chapter_id);
	$db->orderBy ('sort_order','ASC');
	$page_list = $db->get ('comic_page',null,'title, chapter_id, sort_order, date_publish, date_publish_custom, DATE_FORMAT(date_publish,"%a, %b %e %Y") AS date_publish_human, id');
	$page_list = rekey_array($page_list,'id');


	if ( $page_list ) {
		foreach ( $page_list as $key => $val ) {

			$sql = "
SELECT
	url,
	description,
	ir.id
FROM
	grlx_image_reference ir,
	grlx_image_match im
WHERE
	im.rel_id = ?
	AND im.rel_type = 'comic'
	AND im.image_reference_id = ir.id
";

			$image_list = $db->rawQuery($sql,array($key));
			$image_list = rekey_array($image_list,'id');

			$page_list[$key]['images'] = $image_list;
		}
	}

	return $page_list;
}
*/


function reset_page_order($book_id,$db){

	$db-> orderBy ('sort_order','ASC');
	$db-> where ('book_id',$book_id);
	$page_list = $db-> get ('book_page',null,'id,sort_order');
	$sort_order = 1;
	if ( $page_list ) {
		foreach ( $page_list as $this_page ) {
			$data = array ('sort_order' => $sort_order);
			$db->where('id',$this_page['id']);
			$success = $db->update('book_page', $data);
			$sort_order++;
		}
	}
}

function get_ads($type=null,$db){
	$sql = "
SELECT
	a.id,
	a.title,
	source_id,
	source_rel_id,
	small_width,
	small_height,
	small_image_url,
	medium_width,
	medium_height,
	medium_image_url,
	large_width,
	large_height,
	large_image_url,
	tap_url,
	code,
	date_modified
FROM
	".DB_PREFIX."ad_reference a,
	".DB_PREFIX."ad_source src
WHERE
	src.id = a.source_id
";
	if ( $type ) {
		$sql .= "AND a.source_id = ?";
		$info = $db->rawQuery($sql,array($type));
	}
	else {
		$info = $db->rawQuery($sql,null);
	}

	$info = rekey_array($info,'id');
	return $info;
}

function get_ad_info($ad_id,$db){
	$db->join('ad_source src', 'src.id = a.source_id', 'LEFT');
	$db->where('a.id',$ad_id);
	$info = $db->getOne('ad_reference a', 'a.id, a.title, source_id, large_image_url, large_width, large_height, tap_url, code, date_modified');
	return $info;
}

/*
function time_calc($start_date='',$limit=10,$frequency='mwf'){

	$start_date ? $start_date : $start_date = date('Y-m-d');
	$start_set = explode_date($start_date);
	$date_format = 'Y-m-d';

		switch ( $frequency ){
			default:
			case 'mwf':

				$first_date = date('Y-m-d',strtotime(
					'first monday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				$repeat_watch = 1;
				$day = 0;
				for($i=0; $i<$limit; $i++){
					if ( $repeat_watch > 3 ) {
						$repeat_watch = 1;
						$day += 1;
					}
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+$day,$first_set[0]));
					$day += 2;
					$repeat_watch++;
				}
				break;


			case 'weekdays':
				$first_date = date('Y-m-d',strtotime(
					'first monday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				$repeat_watch = 1;
				for($i=0; $i<$limit; $i++){
					if ( $repeat_watch > 5 ) {
						$repeat_watch = 1;
						$i-=1;
					}
					$repeat_watch++;
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+$i,$first_set[0]));
				}
				break;


			case 'saturdays':
				$first_date = date('Y-m-d',strtotime(
					'first saturday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				for($i=0; $i<$limit; $i++){
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+($i*7),$first_set[0]));
				}
				break;

			case 'sundays':
				$first_date = date('Y-m-d',strtotime(
					'first sunday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				for($i=0; $i<$limit; $i++){
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+($i*7),$first_set[0]));
				}
				break;

			case 'mondays':
				$first_date = date('Y-m-d',strtotime(
					'first monday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				for($i=0; $i<$limit; $i++){
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+($i*7),$first_set[0]));
				}
				break;

			case 'tuesdays':
				$first_date = date('Y-m-d',strtotime(
					'first tuesday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				for($i=0; $i<$limit; $i++){
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+($i*7),$first_set[0]));
				}
				break;

			case 'wednesdays':
				$first_date = date('Y-m-d',strtotime(
					'first wednesday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				for($i=0; $i<$limit; $i++){
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+($i*7),$first_set[0]));
				}
				break;

			case 'thursdays':
				$first_date = date('Y-m-d',strtotime(
					'first thursday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				for($i=0; $i<$limit; $i++){
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+($i*7),$first_set[0]));
				}
				break;

			case 'fridays':
				$first_date = date('Y-m-d',strtotime(
					'first friday',
					mktime(0,0,0,$start_set[1],$start_set[2],$start_set[0]))
				);

				$first_set = explode_date($first_date);
				for($i=0; $i<$limit; $i++){
					$date_list[] = date($date_format,mktime(0,0,0,$first_set[1],$first_set[2]+($i*7),$first_set[0]));
				}
				break;

		}

	return $date_list;
}
*/


// English-savvy quantity indicator.
function qty($string=null,$count){

	$ap_list = array ('no','one','two','three','four','five','six','seven','eight','nine','ten');

	if ( $ap_list[$count] ) {
		$friendly_count = $ap_list[$count];
	}
	else {
		$friendly_count = $count;
	}

	switch ( $string ) {
	default:
		$x = $friendly_count;
		break;

	case 'ad':
		$count == 1 ? $x = $friendly_count.' ad' : $x = $friendly_count . ' ads';
		break;

	case 'chapter':
		$count == 1 ? $x = $friendly_count.' chapter' : $x = $friendly_count . ' chapters';
		break;

	case 'page':
		$count == 1 ? $x = $friendly_count.' page' : $x = $friendly_count . ' pages';
		break;

	case 'theme':
		$count == 1 ? $x = $friendly_count.' theme' : $x = $friendly_count . ' themes';
		break;

	case 'tone':
		$count == 1 ? $x = $friendly_count.' tone' : $x = $friendly_count . ' tones';
		break;

	}

	return $x;
}

function get_site_theme($tone_id=1,$db){
	$db-> where('id',$tone_id);
	$result = $db->getOne ('theme_tone',null,'theme_id');
	return $result;
}

function get_current_theme_directory($db){
	$sql = "
SELECT
	gtl.directory
FROM
	".DB_PREFIX."milieu gm,
	".DB_PREFIX."theme_tone gtt,
	".DB_PREFIX."theme_list gtl
WHERE
	gm.label = 'tone_id'
	AND gtt.id = gm.value
	AND gtt.theme_id = gtl.id
";
	$result = $db->rawQuery($sql,FALSE,FALSE);
	if ($result)
	{
		return $result[0]['directory'];
	}
	return FALSE;
}


function explode_date($date){
	$date = explode(':', trim($date));
	$date = explode('-', $date[0]);
	return $date;
}

function format_date($date='',$format='M d, Y'){
	if ( $date ) {
		$date = explode_date($date);
		$output = date($format,mktime(0,0,0,$date[1],$date[2],$date[0]));
		return $output;
	}
}



/*
function set_book_dates($book_id='',$publish_frequency='mwf',$db){
	if ( $book_id ) {

		// Get all this book’s pages, in order.

		$db->join('comic_chapter cc', 'cc.id = cp.chapter_id', 'LEFT');
		$db->where('cc.comic_book_id', $book_id);
		$db->orderBy('cc.sort_order,cp.sort_order');
		$info = $db->get ('comic_page cp', null, 'cp.id');
		$info = rekey_array($info,'id');

	}

	if ( $full_page_list ) {

		$date_list = time_calc($book_info['date_start'],count($full_page_list),$publish_frequency);
		$i=0;
		foreach ( $full_page_list as $key => $val ) {

			$data = Array ('date_publish' => $date_list[$i]);
			$db->where ('id', $key);
			$db->update ('comic_page', $data);

			$i++;
		}
	}
}
*/

function fa($key) {
	$list = array (
		'ad'        => 'fa-bar-chart',
		'alert'     => 'fa-bug',
		'bookshelf' => 'fa-archive',
		'chapter'   => 'fa-bookmark',
		'comic'     => 'fa-book',
		'comment'   => 'fa-comment',
		'config'    => 'fa-cog',
		'database'  => 'fa-database',
		'debug'     => 'fa-medkit',
		'delete'    => 'delete',
		'edit'      => 'edit',
		'extra'     => 'fa-cube',
		'external'  => 'fa-external-link-square',
		'folder'    => 'fa-folder-open',
		'home'      => 'fa-certificate',
		'image'     => 'fa-file-image-o',
		'link'      => 'fa-link',
		'nav'       => 'fa-bars',
		'new'       => 'fa-plus-circle',
		'page'      => 'fa-picture-o',
		'security'  => 'fa-unlock-alt',
		'sort'      => 'sort',
		'static'    => 'fa-file-text',
		'more-files'=> 'fa-copy',
		'theme'     => 'fa-paint-brush',
		'traffic'   => 'fa-sitemap',
		'upload'    => 'fa-download',
		'user'      => 'fa-user',
		'verified'  => 'fa-check-circle',
	);
	return '<i class="'.$list[$key].'"></i>';
}


function display_pretty_publish_frequency($value=''){
	$frequency_list = array (
		'mwf' => 'Mon-Wed-Fri',
		'tth' => 'Tue-Thu',
		'weekdays' => 'Every weekday (M&#8211;F)',
		'mondays' => 'Every Monday',
		'tuesdays' => 'Every Tuesday',
		'wednesdays' => 'Every Wednesday',
		'thursdays' => 'Every Thursday',
		'fridays' => 'Every Friday',
		'saturdays' => 'Every Saturday',
		'sundays' => 'Every Sunday'
	);
	if ( $value ) {
		if ( $frequency_list[$value]) {
			$publish_frequency = $frequency_list[$value];
		}
		else {
			$publish_frequency = $value;
		}
		return $publish_frequency;
	}
	if ( !$value ) {
		return $frequency_list;
	}
}

/*
function delete_static_page($static_page_id,$db){

	$db-> where('id',$static_page_id);
	$db-> delete('static_page');

	$db-> where('rel_id',$static_page_id);
	$db-> where('rel_type','static');
	$db-> delete('path');
}
*/

function delete_comic_page($comic_page_id,$db){

	// Delete the images (MATCHES and REFERENCES).
	delete_comic_images($comic_page_id,$db);

	// Delete the COMIC PAGE itself.
	$db->where('id', $comic_page_id);
	$db->delete('book_page');

	// Renumber everything
	$book_id = get_comic_book_id($db);
	reset_page_order($book_id,$db);
}

function delete_comic_images($comic_page_id,$db){

	// Get the image IDs.
	$image_id_list = get_page_images($comic_page_id,$db);

	// String the image REFERENCE IDs together.
	if ( $image_id_list ) {
		foreach ( $image_id_list as $key => $val ) {
			$image_reference_id_list[] = $val['image_reference_id'];
			unlink('../'.$val['url']);
		}
	}
	if ( $image_reference_id_list && count($image_reference_id_list) > 0 ) {
		$image_reference_id_set = implode(',',$image_reference_id_list);
	}

	// Delete the REFERENCES
	if ( $image_reference_id_set ) {
		$sql = 'DELETE FROM '.DB_PREFIX.'image_reference WHERE id IN ('.$image_reference_id_set.') LIMIT '.count($image_reference_id_list);

		$db->rawQuery($sql,null);

	}

	// Delete the MATCHES.
	$db->where('rel_id', $comic_page_id);
	$db->where('rel_type', 'comic');
	$db->delete('image_match');
}

/*
function delete_chapter($chapter_id,$db){
	$delete_these_pages = get_chapter_pages($chapter_id,$db);
	if ( $delete_these_pages ) {
		foreach ( $delete_these_pages as $key => $val ) {
			delete_comic_page($key,$db);
		}
	}

	$db->where('id', $chapter_id);
	$db->delete('comic_chapter');

	// Renumber everything
	$book_id = get_comic_book_id($db);
	reset_page_order($book_id,$db);
}
*/

/*
function delete_book($book_id,$db){
	$delete_these_chapters = get_book_chapters($book_id,$db);

	if ( $delete_these_chapters ) {
		foreach ( $delete_these_chapters as $key => $val ) {
			delete_chapter($key,$db);
		}
	}

//	$sql = 'DELETE FROM comic_book WHERE id = ?';
	$db->where('id', $book_id);
	$db->delete('book');

	$sql = 'DELETE FROM grlx_path WHERE rel_id = ? AND (rel_type = \'comic\' OR rel_type = \'archive\')';
	$db->rawQuery($sql,array($book_id));
}
*/



/*
function create_chapter($new_chapter_name,$new_tone_id,$book_id,$sort_order,$db){
//	$query = "INSERT INTO comic_chapter (title, comic_book_id, tone_id, sort_order, date_created) VALUES (?,?,?,?,NOW())";

	$data = array (
		'title' => $new_chapter_name,
		'comic_book_id' => $book_id,
		'tone_id' => $new_tone_id,
		'sort_order' => $sort_order,
		'date_created' => 'NOW()'
	);
	$success = $db->insert('comic_chapter', $data);

	return $success;
}
*/

function get_third_login($label='projectwonderful',$db){
	$result = $db->join('third_match tm', 'ts.id = tm.service_id', 'LEFT')
	->where ('label', $label)
	->getOne('third_service ts', 'label,user_info,ts.id,active');
	return $result;
}


function interpret_wonderful_xml($wonderful_xml_obj,$ad_list,$db){
	if ( $wonderful_xml_obj->adboxes->adbox ) {
		foreach ( $wonderful_xml_obj->adboxes->children() as $key => $val ) {

			$found = false;
			$attr = $val->attributes();
			$source_rel_id = (string)$attr['adboxid'];
			if ( $attr )
			{
				foreach ( $attr as $key3 => $val3 )
				{
					$attr_list[$key3] = (string)$val3[0];
				}
			}
			// Compare the database ad list to the XML. We want
			// to find new ads, if any.
			if ( $wonderful_xml_obj && $ad_list ) {
				foreach ( $ad_list as $key2 => $val2 ) {
					if ( $val2['source_rel_id'] == $source_rel_id ) {
						$found = true;
					}
				}
			}

			// What? We don’t have this ad in MySQL? Then add it.
			if ( $found === FALSE ) {
				$code = (string)$val->advancedcode;
				$source_rel_id = (string)$attr->adboxid;
				$data = array (
					'title' => $attr_list['sitename'],
					'source_rel_id' => $source_rel_id,
					'source_id' => 2,
					'large_width' => $attr_list['width'],
					'large_height' => $attr_list['height'],
					'code' => $code
				);
				$new_ad_id = $db->insert('ad_reference', $data);
			}

			// Either way, add it to the ad list.
			$wonderful_ad_list[] = array (
				'source_rel_id' => $source_rel_id,
				'title' => $attr['sitename'],
				'large_width' => $attr_list['width'],
				'large_height' => $attr_list['height'],
				'thumbnail' => $attr['thumbnail'],
				'source_id' => '2',
				'code' => $code
			);
		}
	}
	return $wonderful_ad_list;
}


function get_slot_info($slot_id,$db){
	$db->where ('id', $slot_id);
	$result = $db->getOne('theme_slot',null,'label,theme_id,max_width,max_height');
	return $result;
}

function get_ad_slot_matches($slot_id=null,$ad_id=null,$db){

	if ( $slot_id ) {
		$sql = "
SELECT
	am.id AS match_id,
	ar.id AS ad_id,
	source_id,
	source_rel_id,
	small_width,
	small_height,
	small_image_url,
	medium_width,
	medium_height,
	medium_image_url,
	large_width,
	large_height,
	large_image_url
FROM
	".DB_PREFIX."ad_slot_match am,
	".DB_PREFIX."ad_reference ar
WHERE
	am.slot_id = ?
	AND am.ad_reference_id = ar.id";
		$info = $db->rawQuery($sql,array($slot_id));
		$info = rekey_array($info,'ad_id');
	}

	if ( $ad_id ) {
		$sql = "
SELECT
	am.id AS match_id,
	ar.id AS slot_id,
	source_id,
	source_rel_id,
	small_width,
	small_height,
	small_image_url,
	medium_width,
	medium_height,
	medium_image_url,
	large_width,
	large_height,
	large_image_url
FROM
	".DB_PREFIX."ad_slot_match am,
	".DB_PREFIX."theme_slot ta
WHERE
	am.ad_id = ?
	AND am.ad_reference_id = theme_slot.id";
		$info = $db->rawQuery($sql,array($ad_id));
		$info = rekey_array($info,'slot_id');
	}

	if ( !$slot_id && $ad_id ) {
		$sql = "
SELECT
	ar.id AS ad_id,
	am.id AS match_id,
	ta.id AS slot_id,
	source_id,
	source_ref_id,
	small_width,
	small_height,
	small_image_url,
	medium_width,
	medium_height,
	medium_image_url,
	large_width,
	large_height,
	large_image_url
FROM
	".DB_PREFIX."theme_slot ta,
	".DB_PREFIX."ad_slot_match am,
	".DB_PREFIX."ad_reference ar
WHERE
	AND slot_id = ar.id
	AND ar.large_width <= am.max_width
	AND ar.large_height <= am.max_height
	";
		$info = $db->rawQuery($sql,null);
		$info = rekey_array($info,'id');
	}
	return $info;
}

function get_slots($theme_id=null,$type='ad',$db){

	if ( $theme_id ) {
		$db->where ('theme_id', $theme_id);
	}
	$db->where ('type', $type);
	$db->orderBy ('title','ASC');
	$result = $db->get ('theme_slot',null,'id,label,title,theme_id,max_width,max_height');
	$result = rekey_array($result,'id');
	return $result;
}

/*
function get_tones($db){
	$db->orderBy ('title','ASC');
	$result = $db->get ('theme_tone',null,'title,theme_id,id');
	$result = rekey_array($result,'id');
	return $result;
}
*/

// Get everything in the third_data table relating to a given service.
/*
function gather_ad_data($service='Project Wonderful',$db){
	$sql = "
SELECT
	label,
	value,
	sm.id
FROM
	".DB_PREFIX."milieu sm,
	".DB_PREFIX."milieu_group smg
WHERE
	smg.title = ?
	AND sm.group_id = smg.id";

	$list = $db->rawQuery($sql,array($service));
	$list = rekey_array($list,'label');

	return $list;
}
*/

/*
function fetch_xml_file($filepath=''){
	if ( is_file ( $filepath) ) {
		$file_contents = file_get_contents($filepath);
	}
	if ( $file_contents ) {
		$xml_object = simplexml_load_string($file_contents);
	}
	return $xml_object;
}
*/

/*
function read_theme_xml($xml_object){
	if ( $xml_object-> tone ) {
		foreach ( $xml_object-> tone-> group as $key => $val ) {

			// Value per group
			$value = (string)$val->value;

			// Attributes per group
			$attributes = array(); // reset
			if ( $val-> attributes() ) {
				foreach ( $val-> attributes() as $key2 => $val2 ) {
					$group_attributes[$key2] = (string)$val2;
				}
			}

			// Properties per group
			$selector_set = array(); // reset
			$property_attributes = $val-> property-> attributes();
			$property = (string)$property_attributes->name;

			// Selectors per property per group
			if ( $val-> property ) {
				foreach ( $val-> property as $prop ) {
					$attr = $prop-> attributes();
					$property = (string)$attr->name;

					foreach ( $prop->selector as $selector ) {
						$selector = (string)$selector;
						$overall_set[$value][$property][] = $selector;
					}
				}
			}

			// Attributes per property
			$selector_set = array(); // reset
		}
	}
	return $overall_set;
}
*/

function is_image($path)
{
	$ext_list = array('png','jpg','gif','svg');
	if ( in_array(substr($path,-3,3), $ext_list) )
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function is_image_type($str='', $allowed_image_types=array())
{
	if ( in_array($str, $allowed_image_types) )
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}


function create_thumbnail( $source_file, $destination_file, $max_dimension)
{
	list($img_width,$img_height) = getimagesize($source_file); // Get the original dimentions
	$aspect_ratio = $img_width / $img_height;

	// What should the new dimensions be?
	if ( $img_width > $img_height )
	{
		$new_width = $max_dimension;
		$new_height = $new_width / $aspect_ratio;
	}
	elseif ( $img_width < $img_height )
	{
		$new_height = $max_dimension;
		$new_width = $new_height * $aspect_ratio;
	}
	elseif ( $img_width == $img_height )
	{
		$new_width = $max_dimension;
		$new_height = $max_dimension;
	}
	else {
		return FALSE;
	}
	
	// Make sure these are integers.
	$new_width = intval($new_width);
	$new_height = intval($new_height);

	// What kind of file do we have?
	$extension = explode('.',$source_file);
	$extension = array_pop($extension);
	// Get the source file.
	switch($extension)
	{
		case 'gif':
			@$img_source = imagecreatefromgif($source_file);
			break;
		case 'jpg':
		case 'jpeg':
			@$img_source = imagecreatefromjpeg($source_file);
			break;
		case 'png':
			@$img_source = imagecreatefrompng($source_file);
			break;
		case 'gif':
			@$img_source = imagecreatefromgif($source_file);
			break;
	}
	
	// Resample and create the new thumbnail file.
	if ($img_source)
	{

		// Create a new image in memory.
		$thumbnail = imagecreatetruecolor($new_width,$new_height);

		imagecopyresampled($thumbnail, $img_source, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);


		switch($extension)
		{
			case 'gif':
				imagegif( $thumbnail, $destination_file, 6 );
				break;
			case 'jpg':
			case 'jpeg':
				imagejpeg( $thumbnail, $destination_file, 75 );
				break;
			case 'png':
				imagepng( $thumbnail, $destination_file, 6 );
				break;
			case 'gif':
				imagegif( $thumbnail, $destination_file, 6 );
				break;
		}
	
		// Finally, we destroy the two images in memory.
		imagedestroy($img_source);
		imagedestroy($thumbnail);
		return TRUE;
	}
	return FALSE;
}

function report_image_error($image_info,$error_code)
{
	if ( !is_writable('../'.$image_info['directory'])) {
		return 'Unable to upload image. Looks like a folder permissions problem.';
		return $alert_output;
	}
	else {
		switch ( $_FILES['error'][$key] ) {
			case 1:
				$alert_output = $alert_output = 'I couldn’t upload the image. It exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit.';
				break;
			case 2:
				$alert_output = $alert_output = 'I couldn’t upload the image. It exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit.';
				break;
			case 3:
				$alert_output = $alert_output = 'I couldn’t receive the image. There was nothing to receive.';
				break;
			case 6:
				$alert_output = $alert_output = 'I couldn’t receive the image. There was no “temp” folder on the server — contact your host.';
				break;
			case 8:
				$alert_output = $alert_output = 'I couldn’t upload the image. It doesn’t look like a PNG, GIF, JPG, JPEG or SVG.';
				break;
		}
	}
	return $alert_output;
}
