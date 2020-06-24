<?php

require_once('panl.init.php');
$view = new GrlxView;
$view-> yah = 6;




// Does the static content table already exist?
$sql = 'SHOW TABLES';
$table_list = $db->rawQuery ($sql, FALSE, FALSE);
if ( $table_list )
{
	foreach ( $table_list as $table )
	{
		$table_list_2[] = reset($table);
	}
}

$made_new_table = FALSE; // Bit of a hack, but it works.

// Create the content table so we have a place to put items from XML.
if ( $table_list_2 && !in_array('grlx_static_content',$table_list_2) ) {

	$sql = "
CREATE TABLE IF NOT EXISTS `grlx_static_content` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(9) NOT NULL DEFAULT '0',
  `sort_order` int(3) NOT NULL DEFAULT '0',
  `title` varchar(64) DEFAULT '',
  `url` varchar(160) DEFAULT '',
  `image` varchar(160) DEFAULT '',
  `content` text,
  `pattern` varchar(32) DEFAULT '',
  `created_on` datetime NOT NULL,
  `modified_on` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

	$db->rawQuery ($sql);
	$message_output .= '<p>OK: Content table created.</p>';
	$made_new_table = TRUE;

}

if ( $made_new_table == TRUE )
{
	// Get the existing pages.
	$page_list = $db->get('static_page',NULL,'id,options');
}

if ( $page_list )
{
	$message_output .= '<p>OK: Got the page list</p>';
	foreach ( $page_list as $key => $val )
	{
		$page_id = $val['id'];
		$sort_order = 0; // reset

		$this_info = fetch_static_page_info($page_id,$db);

		// What does this page contain?
		if ( $this_info['options'] && substr($this_info['options'],0,5) == '<?xml' ) {

			// Try to load it as XML.
			$xml = simplexml_load_string($this_info['options']);

			// Get this page’s overall layout.
			$layout_info = (string)$xml->options->layout;
			$layout_list[$page_id] = $layout_info;

			// Loop through the XML’s <item> elements.
			if ( $xml && $xml->content->item ) {
				foreach ( $xml->content->item as $key2 => $val2 ) {
					$sort_order++;
					$data['page_id'] = $page_id;
					$data['sort_order'] = $sort_order;
					$data['title'] = (string)$val2->heading;
					$data['content'] = (string)$val2->text;
					$data['url'] = (string)$val2->link;
					$data['image'] = (string)$val2->image;
					$data['created_on'] = $db->NOW();

					$new_content_id = $db->insert('static_content', $data);
					$message_output .= '<p>OK: Content block ID '.$new_content_id.' (page ID '.$page_id.') created.</p>';
				}
			}
		}
		elseif ( strlen($this_info['options']) > 1 )
		{
			$data['page_id'] = $page_id;
			$data['sort_order'] = $sort_order;
			$data['title'] = 'Freeform';
			$data['content'] = $this_info['options'];
			$data['created_on'] = $db->NOW();
			$new_content_id = $db->insert('static_content', $data);
			$message_output .= '<p>OK: Freeform content block ID '.$new_content_id.' (page ID '.$page_id.') created.</p>';
		}
	}
}
else
{
	$message_list['Pages'][] = 'I didn’t find any find static pages to upgrade. Most likely you already ran this script, or didn’t have any static pages to begin with.';
	$message_list['Next steps'][] = 'First, <strong>did you have any static pages?</strong> If not, then you’re good to go — ignore all this.';
	$message_list['Next steps'][] = 'If you had static pages, then test a static page on <a href="/">your site</a>. If it works, then <strong>delete the script named “_upgrade-to-1.3php”</strong> in your _admin folder.';
	$message_list['Next steps'][] = 'If you had static pages, but they don’t work, then <a href="mailto:grawlixcomix@gmail.com"><strong>contact support</strong></a>.';
}



// Does the layout field already exist?
$sql = 'DESCRIBE grlx_static_page layout';
$table_info = $db->rawQuery ($sql, FALSE, FALSE);

// No field? Then add it.
if ( !$table_info || !$table_info[0] ) {
	// Edit the page table
	$sql = '
ALTER TABLE grlx_static_page
ADD COLUMN layout varchar(32) NULL
	';

	$success = $db->rawQuery ($sql, FALSE, FALSE);

	$sql = 'DESCRIBE grlx_static_page layout';
	$table_info = $db->rawQuery ($sql, FALSE, FALSE);
	if ( $table_info && $table_info[0] ) {
		$message_output .= '<p>OK: Page table updated.</p>';
	}
	else
	{
		$message_list['Page table'][] = 'I couldn’t change the static_page table. Looks like might not have permission to do so.';
		$message_list['Next steps'][] = 'Ask your web host if you have permission to “alter tables in MySQL.”';
	}
}
else {
	$message_list['Page table'][] = 'I couldn’t change the static_page table. Looks like you already ran this upgrade script.';
	$message_list['Next steps'][] = 'Delete the script named “_upgrade-to-1.3.php” in your _admin folder.';
}




// Loop through $layout_list, updating the layout info to static_page

if ( $page_list && $layout_list )
{
	$data = array(); // reset
	foreach ( $layout_list as $key => $val )
	{
		$data['layout'] = $val;
		$db->where('id',$key);
		$success = $db->update('static_page', $data);
		if ( $success ) {
			$message_output .= '<p>OK: Page ID '.$key.' layout updated.</p>';
		}
		else {
			$message_list['Layout'][] = 'Failed to update a page’s layout (ID '.$key.')';
			$message_list['Next steps'][] = 'None.';
		}
	}
}
elseif ( $page_list ) {
	$message_list['Layout'][] = 'Layout list unavailable';
}

if ( !$message_list || count($message_list) == 0 )
{
	$message_output .= '<h2>Success!</h2><p><strong>Please delete the <code>_update-to-1.3.php</code> script</strong></p>';
}
else
{
//	$message_output .= '<h2>Uh-oh, I hit a problem.</h2>';
}




/*****
 * Display
 */

$view->page_title('V1.3 upgrade');
$view->tooltype('sttc');
$view->headline('Version 1.3 upgrade');

$output  = $view->open_view();
$output .= $view->view_header();

print($output . $message_output);
if ( $message_list )
{
	foreach ( $message_list as $key => $val )
	{
		print('<h3>'.$key.'</h3>');
		if ( $val )
		{
			print('<ul>');
			foreach ( $val as $key2 => $val2 )
			{
				print ( '<li>'.$val2.'</li>');
			}
			print('</ul>');
		}
	}
}


print($view->close_view());
?>
