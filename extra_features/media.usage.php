<?php

// Media library v1.0, July 2017

// ! ------ Setup

require_once('panl.init.php');
require_once('lib/htmLawed.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;
$fileops = new GrlxFileOps;
$list = new GrlxList;

$view->yah = 15;

$var_list = array(
	array('image_id','int'),
	array('add_to_page_id','int'),
	array('remove_from_page_id','int'),
	array('keyword','string')
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		${$val[0]} = register_variable($val[0],$val[1]);
	}
}

if ( !$image_id )
{
	header('location:media.list.php');
	die();
}
if ( $image_id && !is_numeric($image_id))
{
	header('location:media.list.php');
	die();
}





// ! ------ Updates

// ! Add to a comic page

if ($image_id && $add_to_page_id)
{
	// Hold it, does this image already belong to that comic page?
	$db->where('rel_type','page');
	$db->where('rel_id',$add_to_page_id);
	$db->where('image_reference_id',$image_id);
	$existing = $db->get('image_match',NULL,'id');

	// As long as there was no record, create a new one.
	if (!$existing || count($existing) == 0)
	{
		// What’s the last sort order for this page?
		$db->where('rel_type', 'page');
		$db->where('rel_id', $add_to_page_id);
		$sort_order = $db->getOne('image_match',NULL,'MAX(sort_order) AS so');
		if ($sort_order)
		{
			$sort_order = $sort_order['so'] + 1;
		}
		else
		{
			$sort_order = 1;
		}

		// Here’s what we’re putting into the record.
		$data = array(
			'image_reference_id' => $image_id,
			'rel_id' => $add_to_page_id,
			'rel_type' => 'page',
			'sort_order' => $sort_order,
			'date_created' => $db->NOW()
		);
		$success = $db->insert('image_match',$data);
	}
	

	if ($success)
	{
		$alert_output .= $message->success_dialog('Image added to the page. <a href="book.page-edit.php?page_id='.$add_to_page_id.'">Check it out</a>.');
	}
}

// ! Remove an image from a comic page

if ($image_id && $remove_from_page_id)
{
	$db->where('rel_type','page');
	$db->where('rel_id',$remove_from_page_id);
	$db->where('image_reference_id',$image_id);
	$success = $db->delete('image_match');
	if ($success)
	{
		$alert_output .= $message->success_dialog('Image removed from page.');
	}
}







// ! ------ Display logic


// ! Get image info

$db->where('id',$image_id);
$image_info = $db->getOne('image_reference','id,description,url');


// ! Where is this image used?
$db->where('image_reference_id',$image_id);

$match_list = $db->get('image_match',NULL,'rel_id,rel_type,id,image_reference_id');
if ($match_list && count($match_list) > 0)
{
	$match_list = rekey_array($match_list,'rel_id');
}

// ! Get all comic pages

if ($keyword)
{
	$db->where('title','%'.$keyword.'%','LIKE');
	$limit == NULL;
	$label = '<strong>Search results:</strong>';
	$reset_button = '<a href="media.usage.php?image_id='.$image_id.'">Reset</a>';
}
else
{
	$limit = 10;
	$label = '10 most recent pages';
}
$db->orderBy('date_created','DESC');
$db->orderBy('title','ASC');
$comic_page_list = $db->get('book_page',$limit,'id,title,date_created');



// ! Static page output
if ( $static_page_list )
{
	foreach ( $static_page_list as $key => $val )
	{
		
	}
}

// ! Comic page output
if ( $comic_page_list )
{
	foreach ( $comic_page_list as $key => $val )
	{
		if ($match_list[$val['id']] && $match_list[$val['id']]['rel_type'] == 'page')
		{
//			$actions = '<strong>Already on '.$val['id'].'</strong>';
			$actions = '<a href="media.usage.php?image_id='.$image_id.'&amp;remove_from_page_id='.$val['id'].'&amp;keyword='.$keyword.'"><strong>Remove</strong></a>';
		}
		else
		{
			$actions = '<a href="media.usage.php?image_id='.$image_id.'&amp;add_to_page_id='.$val['id'].'&amp;keyword='.$keyword.'">Add</a>';
		}

		if ($val['title'] && $val['title'] != '')
		{
			$title = '<a href="book.page-edit.php?page_id='.$val['id'].'">'.$val['title'].'</a>';
		}
		else
		{
			$title = '<a href="book.page-edit.php?page_id='.$val['id'].'">(Untitled)</a>';
		}

		$date_created = substr($val['date_created'],0,10);
		$date_created = explode('-',$date_created);
		$date_created = date('F j, Y',mktime(0,0,0,$date_created[1],$date_created[2],$date_created[0]));


		$comic_page_list_items[$val['id']] = array(
			'title' => $title,
			'date' => $date_created,
			'action'=> $actions
		);
	}
}
else
{
	echo $comic_page_list_items;
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
<br/><p>$label</p>

EOL;



$comic_page_list_output = $search_form_output;

if ( $comic_page_list_items ) {

	$heading_list[] = array(
		'value' => 'Page title'
	);
	$heading_list[] = array(
		'value' => 'Date'
	);
	$heading_list[] = array(
		'value' => 'Actions'
	);

	$list->headings($heading_list);
	$list->draggable(false);
	$list->row_class('bookshelf');

	// Mix it all together.
	$list->content($comic_page_list_items);
	$comic_page_list_output .= $list->format_headings();
	$comic_page_list_output .= $list->format_content();

}
else
{
	$comic_page_list_output .= '<p>No comic pages found.</p>'."\n";
}




// ! ------ Display

$view->group_css('page');
$view->group_h2('Comic pages');
$view->group_contents($comic_page_list_output);
$view->group_instruction('Choose on which comic page(s) in your site this image will appear.');
$comic_page_output = $view->format_group();

$link->url('media.edit.php?image_id='.$image_id);
$link->tap('Image editor');
$link->id('browse-media');
$action_output = $link->text_link('back');

$view->page_title("Image");
$view->tooltype('chap');
$view->headline('Image <span>'.$image_info['description'].'</span>');
$view->action($action_output);

print ($view->open_view());
print ($view->view_header());
print ($alert_output);

?>

<form accept-charset="UTF-8" action="media.usage.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="grlx_xss_token" value="<?=$_SESSION['admin']?>"/>
	<input type="hidden" name="image_id" value="<?=$image_id?>"/>

<?=$comic_page_output ?>


</form>

<?php
print($view->close_view());
?>
