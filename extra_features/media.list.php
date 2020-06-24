<?php

// Media library v1.0, July 2017

// ! ------ Setup

require_once('panl.init.php');

$view = new GrlxView;
$link = new GrlxLinkStyle;
$list = new GrlxList;
$message = new GrlxAlert;
$form = new GrlxForm;

$form->send_to($_SERVER['SCRIPT_NAME']);

$gd_enabled = FALSE; // Until proven otherwise

$view->yah = 15;

$mode_list = array(
	'comic' => 'Comic pages',
	'static' => 'Static pages'
);

$var_list = array(
	array('delete_image_id','int'),
	array('keyword','string'),
	array('remove_block_image_id','int'),
	array('start','int'),
	array('mode','string')
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		${$val[0]} = register_variable($val[0],$val[1]);
	}
}
$mode ? $mode : $mode = 'comic';
$start ? $start : $start = 0;
$limit = 20; // Number of results to display per screen.

$gd_info = gd_info();
if ($gd_info && $gd_info['GD Version'] != '')
{
	$gd_enabled = TRUE;
}



// ! ------ Updates

// ! Permanently delete an image.
if ($delete_image_id && is_numeric($delete_image_id))
{

	$db->where('id',$delete_image_id);
	$image_info = $db->getOne('image_reference','url');

	if ($image_info && substr($image_info['url'],0,4) != 'http' && is_file('..'.$image_info['url']))
	{
		$doomed_directory = explode('/',$image_info['url']);
		array_pop($doomed_directory);
		$doomed_directory = implode('/',$doomed_directory);
		unlink('..'.$image_info['url']);
		@rmdir('..'.$doomed_directory);
	}

	$db->where('image_reference_id',$delete_image_id);
	$db->delete('image_match');

	$db->where('id',$delete_image_id);
	$success = $db->delete('image_reference');

	if ($success)
	{
		$alert_output = $message->success_dialog('Image deleted.');
	}
}

if ($remove_block_image_id)
{
	$db->where('id',$remove_block_image_id);
	$data = array('image'=>'');
	$success = $db->update('static_content',$data);
	if ($success)
	{
		$alert_output = $message->success_dialog('Image deleted.');
	}
}



// ! ------ Display logic


// ! Grab all COMIC images from the database.

if ($mode == 'comic')
{
	$db->orderBy('date_created','DESC');
	if ($keyword)
	{
		$db->where('description','%'.$keyword.'%','LIKE');
		$db->orWhere('url','%'.$keyword.'%','LIKE');
		$comic_image_list = $db->get ('image_reference', NULL, 'description,id,url,date_created');
		$reset_button = '<a href="media.list.php">Reset</a>';
	}
	else
	{
		$comic_image_list = $db->get ('image_reference', array($start,$limit), 'description,id,url,date_created');
		$reset_button = '';
	}
}

// ! Grab all STATIC images from the database.


if ($mode == 'static')
{
	$db->orderBy('created_on','DESC');
	if ($keyword)
	{
		$db->where('image','%'.$keyword.'%','LIKE');
		$db->where('image','NULL','!=');
		$static_image_list = $db->get ('static_content', NULL, 'title,id,image,created_on');
		$reset_button = '<a href="media.list.php">Reset</a>';
	}
	else
	{
		$static_image_list = $db->get ('static_content', NULL, 'title,id,image,created_on');
		$reset_button = '';
	}
}

// ! Build COMIC pagination

if (!$keyword && $mode == 'comic')
{
	$result = $db->getOne('image_reference','COUNT(id) AS total');
	if ($result)
	{
		$total_comic_images = $result['total'];
	}
	else
	{
		$total_comic_images = 0;
	}

	if ($total_comic_images > $limit)
	{
		for($i=0;$i<=$total_comic_images;$i+=$limit)
		{
			if ($start == $i)
			{
				$pagination_list[] = '<strong>'.($i+1).'–'.($i+$limit).'</strong>'."\n";
			}
			else
			{
				$pagination_list[] = '<a href="?start='.$i.'">'.($i+1).'–'.($i+$limit).'</a>'."\n";
			}
		}
	}
	if ($pagination_list)
	{
		$pagination_output = '<br/><p>'.implode(', ',$pagination_list).'</p>'."\n";
	}
}

// ! Build mode options

if ( $mode_list )
{
	foreach ( $mode_list as $key => $val )
	{
		if ($mode == $key)
		{
			$mode_output_list[] = '<strong>'.$val.'</strong>'."\n";
		}
		else
		{
			$mode_output_list[] = '<a href="?mode='.$key.'&amp;keyword='.$keyword.'">'.$val.'</a>'."\n";
		}
	}
}
if ($mode_output_list)
{
	$mode_output = '<p>'.implode(' | ',$mode_output_list).'</p>'."\n";
}


// ! Build the display for COMIC images

if ($comic_image_list) {

	$edit_link = new GrlxLinkStyle;
	$edit_link->url('media.edit.php');
	$edit_link->title('Edit this image.');
	$edit_link->reveal(false);
	$edit_link->action('edit');

	$delete_link = new GrlxLinkStyle;
	$delete_link->url('media.list.php');
	$delete_link->title('Delete this image.');
	$delete_link->reveal(false);
	$delete_link->action('delete');

	foreach ( $comic_image_list as $key => $val ) {

		// Where is this image used?
		$db->where('image_reference_id',$val['id']);

		$match_list = $db->get('image_match',NULL,'rel_id,rel_type,id,image_reference_id');

		if ( $match_list && count($match_list) > 0 )
		{
			$matches = ''; // reset
			foreach ( $match_list as $key2 => $val2 )
			{
				$db->where('id',$val2['rel_id']);
				$info = $db->getOne('book_page','title');
				if ($info && $info['title'])
				{
					$matches .= $info['title'];
				}
				else
				{
					$matches .= '(Unknown page)';
				}
				$matches .= '<br/>'."\n";
			}
		}
		else
		{
			$matches = '(not used)'."\n";
		}

		$edit_link->query("image_id=$val[id]");

		$delete_link->query("delete_image_id=$val[id]&keyword=$keyword");

		// General actions for this item.
		$action_output = $delete_link->icon_link().$edit_link->icon_link();

		// Do we have a thumbnail?
		$thumb_filename = $val['url'];
		$thumb_filename = explode('/',$thumb_filename);
		$thumb_extension = array_pop($thumb_filename);
		$thumb_extension = explode('.',$thumb_extension);
		$thumb_extension = array_pop($thumb_extension);
		$thumb_filename = implode('/',$thumb_filename).'/thumb.'.$thumb_extension;

		if ($thumb_filename && substr($thumb_filename,0,4) != 'http' && is_file('..'.$thumb_filename))
		{
			$thumb_element = '<img src="..'.$thumb_filename.'?reset='.date('ymdhis').'" alt="pic"/>';
		}
		else
		{
			$thumb_element = '<img src="'.$val['url'].'" alt="pic"/>';
		}
		if (!$thumb_element)
		{
			$thumb_element = '(Missing file)';
		}

		if ($val['url'] && substr($val['url'],0,4) == 'http')
		{
			$thumbnail = '<a href="media.edit.php?image_id='.$val['id'].'">'.$thumb_element.'</a>'."\n";
		}
		elseif ($val['url'] && is_file('..'.$val['url']))
		{
			$thumbnail = '<a href="media.edit.php?image_id='.$val['id'].'">'.$thumb_element.'</a>'."\n";
		}
		else
		{
			$thumbnail = '<a href="media.edit.php?image_id='.$val['id'].'">(No file)</a>'."\n";
		}

		if ($val['description'])
		{
			$description = $val['description']."\n";
		}
		else
		{
			$description = '(None)'."\n";
		}

		if ($val['date_created'] && strlen($val['date_created']) >= 10)
		{
			$date_created = substr($val['date_created'],0,10);
			$date_created = explode('-',$date_created);
			$date_created = date('F j, Y',mktime(0,0,0,$date_created[1],$date_created[2],$date_created[0]));
		}
		else
		{
			$date_created = '(Undated)';
		}

		// Assemble the list item.
		$list_items[$val['id']] = array(
			'thumbnail' => $thumbnail,
			'metadata' => $date_created,
			'matches'=> $matches,
			'description'=> $description,
			'action'=> $action_output
		);
	}
}


// ! Build the display for STATIC images.

if ($static_image_list) {

	$edit_link = new GrlxLinkStyle;
	$edit_link->url('sttc.block-edit.php');
	$edit_link->title('Edit this image.');
	$edit_link->reveal(false);
	$edit_link->action('edit');

	$delete_link = new GrlxLinkStyle;
	$delete_link->url('media.list.php');
	$delete_link->title('Delete this image (but not its block).');
	$delete_link->reveal(false);
	$delete_link->action('delete');

	foreach ( $static_image_list as $key => $val ) {

		$edit_link->query("block_id=$val[id]");

		$delete_link->query("remove_block_image_id=$val[id]&keyword=$keyword");

		// General actions for this item.
		$action_output = $delete_link->icon_link().$edit_link->icon_link();

		$thumb_element = '<img src="..'.$val['image'].'" alt="pic"/>';

		// Build the row.
		if ($val['image'])
		{
			if ($val['image'] && substr($val['image'],0,4) == 'http')
			{
				$thumbnail = '<a href="sttc.block-edit.php?block_id='.$val['id'].'">'.$thumb_element.'</a>'."\n";
			}
			elseif ($val['image'] && is_file('..'.$val['image']))
			{
				$thumbnail = '<a href="sttc.block-edit.php?block_id='.$val['id'].'">'.$thumb_element.'</a>'."\n";
			}
			else
			{
				$thumbnail = '<a href="sttc.block-edit.php?block_id='.$val['id'].'">(No file)</a>'."\n";
			}

			if ($val['title'])
			{
				$description = $val['title']."\n";
			}
			else
			{
				$description = '(None)'."\n";
			}

			if ($val['created_on'] && strlen($val['created_on']) >= 10)
			{
				$date_created = substr($val['created_on'],0,10);
				$date_created = explode('-',$date_created);
				$date_created = date('F j, Y',mktime(0,0,0,$date_created[1],$date_created[2],$date_created[0]));
			}
			else
			{
				$date_created = '(Undated)';
			}

			// Assemble the list item.
			$list_items[$val['id']] = array(
				'thumbnail' => $thumbnail,
				'metadata' => $date_created,
				'matches'=> $description,
				'description'=> '-',
				'action'=> $action_output
			);
		}
	}
}



if ( $list_items ) {

	$heading_list[] = array(
		'value' => 'Image'
	);
	$heading_list[] = array(
		'value' => 'Date created'
	);
	$heading_list[] = array(
		'value' => 'Used on'
	);
	$heading_list[] = array(
		'value' => 'Description (alt text)'
	);
	$heading_list[] = array(
		'value' => 'Actions'
	);

	$list->headings($heading_list);
	$list->draggable(false);
	$list->row_class('medialist');

	// Mix it all together.
	$list->content($list_items);
	$image_list_output  = $list->format_headings();
	$image_list_output .= $list->format_content();

}

if ( !$list_items ) {
	$image_list_output  = '<p>No images found.</p>';
}

	$search_form_output = <<<EOL
<div class="row">
	<div class="large-12 columns">
		<div class="row">
			<div class="small-3 columns">
				<input type="search" name="keyword" id="keyword" placeholder="Search for" value="$keyword"/>
			</div>
			<div class="small-9 columns">
				<button class="btn secondary search" name="submit" type="submit" value="reorder"><i></i>Search</button>
				$reset_button
			</div>
		</div>
	</div>
</div>
<input type="hidden" name="mode" value="$mode"/>

EOL;

if ($gd_enabled === TRUE )
{
	$utility_output = '<a href="media.thumbnails.php">Thumbnail generator</a>';
	$view->group_css('page');
	$view->group_h2('Options');
	$view->group_contents($utility_output);
	$view->group_instruction('Tools for images.');
	$utility_output = '<hr/>'.$view->format_group();
}




// ! ------ Display

$view->page_title('Media library');
$view->tooltype('sttc');
$view->headline('Media library');

print ( $view->open_view() );
print ( $view->view_header() );
print ( $alert_output );

print ( $form->open_form() );
print ( $search_form_output );
print ( $mode_output );
print ( $image_list_output );
print ( $pagination_output );
print ( $utility_output );

print ( $form->close_form() );
print ( $view->close_view() );
