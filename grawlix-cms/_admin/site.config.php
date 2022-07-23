<?php

/* Artists use this script to control general site settings.
 */

/* ! Setup */

require_once('panl.init.php');
require_once('lib/htmLawed.php');

$view = new GrlxView;
$link = new GrlxLinkStyle;
$form = new GrlxForm;

$form->send_to($_SERVER['SCRIPT_NAME']);
$form->row_class('config');
$sl = new GrlxSelectList;

$view-> yah = 12;

$date_list[] = array(
	'id' => 'm/d/y',
	'title' => '01/01/'.date('y')
);
$date_list[] = array(
	'id' => 'm-d-Y',
	'title' => '01-01-'.date('Y')
);
$date_list[] = array(
	'id' => 'd-M-Y',
	'title' => '01-Jan-'.date('Y')
);
$date_list[] = array(
	'id' => 'F j, Y',
	'title' => 'January 1, '.date('Y')
);
$date_list[] = array(
	'id' => 'j F Y',
	'title' => '1 October '.date('Y')
);

$timezone_master_list['none'] = array(
	'id' => 'America/Los_Angeles',
	'title' => 'None (default to San Francisco, because … reasons)'
);

$timezone_master_list['America/Los_Angeles'] = array(
	'id' => 'America/Los_Angeles',
	'title' => 'UTC−08:00: San Francisco, Vancouver, Tijuana'
);

$timezone_master_list['America/Denver'] = array(
	'id' => 'America/Denver',
	'title' => 'UTC−07:00: Denver, Phoenix, Calgary, Ciudad Juárez'
);

$timezone_master_list['America/Chicago'] = array(
	'id' => 'America/Chicago',
	'title' => 'UTC−06:00: Chicago, Guatemala City, Mexico City, San José, San Salvador, Tegucigalpa, Winnipeg'
);

$timezone_master_list['America/New_York'] = array(
	'id' => 'America/New_York',
	'title' => 'UTC−05:00: New York, Lima, Toronto, Bogotá, Havana, Kingston'
);

$timezone_master_list['America/Caracas'] = array(
	'id' => 'America/Caracas',
	'title' => 'UTC−04:30: Caracas'
);

$timezone_master_list['America/Santiago'] = array(
	'id' => 'America/Santiago',
	'title' => 'UTC−04:00: Santiago, La Paz, San Juan de Puerto Rico, Manaus, Halifax'
);

$timezone_master_list['America/Argentina/Buenos_Aires'] = array(
	'id' => 'America/Argentina/Buenos_Aires',
	'title' => 'UTC−03:00: Buenos Aires, Montevideo, São Paulo'
);

$timezone_master_list['America/Canada/Newfoundland'] = array(
	'id' => 'America/Canada/Newfoundland',
	'title' => 'UTC−03:30: St. John’s'
);

$timezone_master_list['Europe/Lisbon'] = array(
	'id' => 'Europe/Lisbon',
	'title' => 'UTC±00:00: Accra, Abidjan, Casablanca, Dakar, Dublin, Lisbon, London'
);

$timezone_master_list['Europe/Berlin'] = array(
	'id' => 'Europe/Berlin',
	'title' => 'UTC+01:00: Belgrade, Berlin, Brussels, Lagos, Madrid, Paris, Rome, Tunis, Vienna, Warsaw'
);

$timezone_master_list['Europe/Istanbul'] = array(
	'id' => 'Europe/Istanbul',
	'title' => 'UTC+02:00: Athens, Sofia, Cairo, Kiev, Istanbul, Beirut, Helsinki, Jerusalem, Johannesburg, Bucharest'
);

$timezone_master_list['Europe/Moscow'] = array(
	'id' => 'Europe/Moscow',
	'title' => 'UTC+03:00: Moscow, Nairobi, Baghdad, Doha, Khartoum, Minsk, Riyadh'
);

$timezone_master_list['Europe/Samara'] = array(
	'id' => 'Europe/Samara',
	'title' => 'UTC+04:00: Baku, Dubai, Samara, Muscat'
);

$timezone_master_list['Asia/Karachi'] = array(
	'id' => 'Asia/Karachi',
	'title' => 'UTC+05:00: Karachi, Tashkent, Yekaterinburg'
);

$timezone_master_list['Asia/Kathmandu'] = array(
	'id' => 'Asia/Kathmandu',
	'title' => 'UTC+05:45, Kathmandu'
);

$timezone_master_list['Asia/Almaty'] = array(
	'id' => 'Asia/Almaty',
	'title' => 'UTC+06:00: Almaty, Dhaka, Novosibirsk'
);

$timezone_master_list['Asia/Jakarta'] = array(
	'id' => 'Asia/Jakarta',
	'title' => 'UTC+07:00: Jakarta, Bangkok, Krasnoyarsk, Hanoi'
);

$timezone_master_list['Australia/Perth'] = array(
	'id' => 'Australia/Perth',
	'title' => 'UTC+08:00: Perth, Beijing, Manila, Singapore, Kuala Lumpur, Denpasar, Irkutsk'
);

$timezone_master_list['Asia/Tokyo'] = array(
	'id' => 'Asia/Tokyo',
	'title' => 'UTC+09:00: Seoul, Tokyo, Pyongyang, Ambon, Yakutsk'
);

$timezone_master_list['Australia/Adelaide'] = array(
	'id' => 'Australia/Adelaide',
	'title' => 'UTC+09:30, Adelaide'
);

$timezone_master_list['Australia/Canberra'] = array(
	'id' => 'Australia/Canberra',
	'title' => 'UTC+10:00: Canberra, Vladivostok, Port Moresby'
);

$timezone_master_list['Pacific/Noumea'] = array(
	'id' => 'Pacific/Noumea',
	'title' => 'UTC+11:00: Honiara, Noumea'
);

$timezone_master_list['Pacific/Auckland'] = array(
	'id' => 'Pacific/Auckland',
	'title' => 'UTC+12:00: Auckland, Suva'
);

$timezone_master_list['Pacific/Honolulu'] = array(
	'id' => 'Pacific/Honolulu',
	'title' => 'UTC−10:00: Papeete, Honolulu'
);

$timezone_master_list['Pacific/Samoa'] = array(
	'id' => 'Pacific/Samoa',
	'title' => 'UTC−11:00: American Samoa'
);

$timezone_master_list['America/Anchorage'] = array(
	'id' => 'America/Anchorage',
	'title' => 'UTC−09:00: Anchorage'
);

// Get a list of books.
$db->orderBy('sort_order,title', 'ASC');
$book_list = $db->get('book',NULL,'id,title');



/* ! Updates */


$book_id = $_POST['book_id'];
$book_id ? $book_id : $book_id = $_GET['book_id'];
if ( !$book_id ) {
	$book = new GrlxComicBook;
	$book_id = $book->bookID;
}

/*
$options_list = array (
//	'title'  => 'Comic page title',
	'image'  => 'Comic image',
	'number' => 'Page number',
	'blog'   => 'Blog post',
	'transcript' => 'Transcript'
);
*/

/*
if ( $_POST['submit'] == 'save' )
{
	$args['rssNew'] = $_POST['rss_options'];
}

$args['bookID'] = $book_id;
$xml = new GrlxXML_Book($args);
*/


// Save changes to milieu items
if ( $_POST['submit'] ) {

	$input = $_POST['input'];
	$ga_input = $_POST['googleanalytics'];
//	$milieu_group = explode('-', $_POST['submit']);
//	$milieu_group = $milieu_group[1];
	$count = 0;

	$homepage = strfunc_split_tablerow($_POST['homepage']);

	// Safety checks for site root
	if ( !(trim($input['directory'])) ) {
		unset($input['directory']);
	}
	else {
		$string = trim($input['directory']);
		$string = trim($string,'/');
		$input['directory'] = '/'.$string;
	}

	foreach ( $input as $key => $val ) {
		$val = trim($val);
		$val = htmLawed($val);
		$data = array('value' => $val);
		$db -> where('label', $key);
//		$db -> where('milieu_type_id', $milieu_group);
		$db -> update('milieu', $data);
		if ( $db -> count > 0 ) {
			$count++;
		}
	}
	foreach ( $ga_input as $id=>$array ) {
		foreach ( $array as $key=>$val ) {
			$info = trim($val);
			$data = array($key=>$info);
			$db->where('id',$id);
			$db->update('third_service',$data);
			if ( $db -> count > 0 ) {
				$count++;
			}
			// Set switch in third service
			( $info === null || $info == '' ) ? $active = 0 : $active = 1;
			$data = array('active'=>$active);
			$db->where('service_id',$id);
			$db->update('third_match',$data);
		}
	}
	if ( $homepage['table'] && is_numeric($homepage['id']) ) {
		$data = array('rel_type'=>$homepage['table'],'rel_id'=>$homepage['id']);
		$db->where('url','/');
		$db->update('path',$data);
		if ( $db -> count > 0 ) {
			$count++;
		}
	}
	$message = new GrlxAlert;
	if ( $count > 0 ) {
		$alert_output = $message->success_dialog('Changes were saved.');
	}
	else {
		$alert_output = $message->alert_dialog('Changes failed to save.');
	}
}


/* ! Display logic */

/*
if ( $options_list ) {
	foreach ( $options_list as $key => $val ) {

		if ( $xml->rss && in_array($key, $xml->rss))
		{
			$check = ' checked="checked"';
		}
		else
		{
			$check = '';
		}

		$rss_options_output .= '<div class="row form config">';
		$rss_options_output .= '<div>';
		$rss_options_output .= '<input type="checkbox"'.$check.' id="'.$key.'" name="rss_options[option][]" value="'.$key.'"/>';
		$rss_options_output .= '</div>';
		$rss_options_output .= '<div>';
		$rss_options_output .= '<label class="option" for="'.$key.'">'.$val.'</label>';
		$rss_options_output .= '</div>';
		$rss_options_output .= '</div>';
	}
}
*/



if ( $xml->saveResult == 'success' ) {
	$message = new GrlxAlert;
	$alert_output = $message->success_dialog('Changes saved.');
}

if ( $xml->saveResult == 'error' ) {
	$message = new GrlxAlert;
	$alert_output = $message->alert_dialog('Changes failed to save.');
}

$db->where('label', 'directory');
$site_directory = $db->getOne('milieu', 'value');
$site_directory = 'http://'.$_SERVER['SERVER_NAME'].$site_directory['value'];

/*
if ( $book_list )
{
	foreach($book_list as $key => $val)
	{
	$locations_output .= '<div class="">'."\n";
	$locations_output .= '	<strong>'.$val['title'].'</strong><br/>'."\n";
	$locations_output .= '	<a href="'.$site_directory.'rss?id='.$val['id'].'">RSS</a> | '."\n";
	$locations_output .= '	<a href="'.$site_directory.'json?id='.$val['id'].'">JSON</a>'."\n";
	$locations_output .= '</div><br/>'."\n";
	}
}
*/



// ! Add thumbnail option, if necessary.

$db->where('label','thumb_max');
$result = $db->getOne('milieu','id');
if (!$result || !$result['id'])
{
	$data = array(
		'title' => 'Thumbnail size',
		'description' => 'Thumbnails’ maximum dimension',
		'label' => 'thumb_max',
		'value' => '200',
		'milieu_type_id' => '1',
		'data_type' => 'int',
		'sort_order' => '8'
	);
	$db->insert('milieu', $data);
}


// Get Google Analytics info
$cols = array(
	'id',
	'title',
	'label',
	'description',
	'user_info',
	'info_title'
);
$ga_info = $db
	->where('id',16)
	->getOne('third_service',$cols);

// Fetch site milieu settings
$cols = array(
	'sm.id',
	'sm.title',
	'description',
	'label',
	'value',
	'sm.sort_order'
);
$result = $db
	-> orderBy('sm.sort_order', 'ASC')
	-> orderBy('label', 'ASC')
	-> where('sort_order', 1, '>=')
	-> get('milieu sm', NULL, $cols);

if ( $result ) {
	foreach ( $result as $key => $val ) {
		if ( $val['label'] == 'timezone' ) {
			$current_timezone = $val['value'];
		}
	}
}

if ( $timezone_master_list ) {
	$sl-> setName('input[timezone]');
	$sl-> setList($timezone_master_list);
	$sl-> setCurrent($current_timezone);
	$sl-> setValueID('id');
	$sl-> setValueTitle('title');
	$select_options = $sl-> buildSelect();
}

if ( $db -> count > 0 ) {
	foreach ( $result as $item ) {
		if ( $item['description'] ) {
			$tooltip = '<span data-tooltip aria-haspopup="true" class="info has-tip" title="'.$item['description'].'"><i></i></span>';
		}
		else {
			$tooltip = null;
		}
		if ( $item['label'] == 'date_format' && $date_list ) {
			$sl->setName('input['.$item['label'].']');
			$sl->setList($date_list);
			$sl->setCurrent($item['value']);
			$sl->setValueID('id');
			$sl->setValueTitle('title');
			$date_options = $sl->buildSelect();
			$form_output .= '<div class="row form config"><div><label for="item-'.$item['id'].'">'.$item['title'].'</label></div><div>'.$date_options.'</div></div>';
		}
		elseif ( $item['label'] != 'timezone') {
			$required = true;
			$value = $item['value'];
			if ( $item['label'] == 'directory' ) {
				$required = false;
				if ( $item['value'] == '/' ) {
					$value = '';
				}
			}
			$form->input_text("item-$item[id]");
			$form->name('input['.$item['label'].']');
			$form->label($item['title'].$tooltip);
			$form->required($required);
			$form->value($value);
			$form->maxlength(255);
			$form_output .= $form->paint();
		}
		else {
			$form_output .= <<<EOL
<div class="row form config"><div><label for="item-6">Timezone</label></div><div>$select_options</div></div>
EOL;
		}
	}
	if ( $ga_info ) {
		$tooltip = '<span data-tooltip aria-haspopup="true" class="info has-tip" title="'.$ga_info['description'].'"><i></i></span>';
		$form->input_text($ga_info['label'].'['.$ga_info['id'].']'.'[user_info]');
		$form->label($ga_info['title'].'<br/>'.$ga_info['info_title'].$tooltip);
		$form->required(false);
		$form->value($ga_info['user_info']);
		$form->maxlength(32);
		$form_output .= $form->paint();
	}
//	$form_output .= $form->form_buttons();
}
else {
	$message = new GrlxAlert;
	$result_1 = $db->get ('milieu',null,'id');
	if ( $db-> count == 0 ) {
		$link-> url('mailto:grawlixcomix@gmail.com');
		$link-> tap('Contact support');
		$alert_output .= $message->alert_dialog('Site milieu table is empty. That’s bad. '.$link-> paint().'.');
	}
	$result_2 = $db->get ('milieu_group',null,'id');
	if ( $db-> count == 0 ) {
		$link-> url('mailto:grawlixcomix@gmail.com');
		$link-> tap('Contact support');
		$alert_output .= $message->alert_dialog('Site milieu <em>group</em> table is empty. That’s really bad. '.$link-> paint().'.');
	}
}


// Get static home page id
$result = $db
	->where('title','Home')
	->getOne('static_page','id');
$static_id = $result['id'];

// Get current home reference
$cols = array('id,rel_type,rel_id');
$home = $db
	->where('url','/')
	->getOne('path',$cols);

$current_home = $home['rel_type'].'-'.$home['rel_id'];

if ( $home['rel_type'] == 'static' ) {
	$link->url('sttc.page-edit.php?page_id='.$static_id);
	$link->tap('Edit home page');
	$edit_home = $link->text_link('editmeta');
}

// Site homepage settings
if ( $home && $book_id && $static_id ) {
	$home_instruction = 'Your site’s home can display the latest comic or a static page.';
	if ( $book_list )
	{
		foreach ( $book_list as $key => $val )
		{
			$home_list[] = array(
				'id' => 'book-'.$val['id'],
				'title' => 'Latest page of “'.$val['title'].'”'
			);
		}
	}

	$home_list[] = array(
		'id' => 'static-'.$static_id,
		'title' => 'Static home page'
	);
	$sl->setName('homepage');
	$sl->setList($home_list);
	$sl->setCurrent($current_home);
	$sl->setValueID('id');
	$sl->setValueTitle('title');
	$home_options = $sl->buildSelect();
	$home_output  = '<div class="row form config">';
	$home_output .= '<div><label for="home-id">Homepage</label></div>';
	$home_output .= '<div>'.$home_options.'</div>';
	$home_output .= '</div>';
}

$link-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/settings');
$link-> tap('Read the docs');
$instruction = 'Use this panel to customize your overall site. '.$link-> external_link().' for details.';

$view->page_title('Site settings');
$view->tooltype('config');
$view->headline('Site settings');
$view->group_css('config');

$view->group_h2('Basics');
$view->group_instruction($instruction);
$view->group_contents($form_output);
$content_output .= $view->format_group();

$view->group_h2('Homepage');
$view->group_instruction($home_instruction);
$view->group_contents($home_output);
$home_output = $view->format_group();

/*
$view->group_h2('Feed options');
$view->group_instruction('Choose which bits of information readers will see in their RSS and JSON feeds.');
$view->group_contents($rss_options_output);
$feed_options_output  = $view->format_group();

$view->group_h3('Feed locations');
$view->group_instruction('Where to find your site’s feeds.');
$view->group_contents($locations_output);
$locations_output = $view->format_group();
*/



/* ! Display */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $form->open_form();
$output .= $alert_output;
$output .= $content_output.'<hr/>';
//$output .= $feed_options_output;
//$output .= $locations_output.'<hr/>';
$output .= $home_output.'<hr/>';
$output .= $form->form_buttons($edit_home);
$output .= $form->close_form();
$output .= $view->close_view();
print($output);
