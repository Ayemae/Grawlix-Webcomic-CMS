<?php

require_once('panl.init.php');

$list = new GrlxList;
$link = new GrlxLinkStyle;

// What it sounds like.
$allowed_sttc_file_types = array ('gif','png','jpg','jpeg','svg');

$variable_list = array(
	'page_title',
	'page_description',
	'original_sort_order',
	'sort_order',
	'title',
	'content',
	'image',
	'page_id',
	'msg',
	'pattern_id',
	'url',
	'layout_id',
	'delete_block_id'
);
if ( $variable_list ) {
	foreach ( $variable_list as $val ) {
		$$val = register_variable($val);
	}
}

$page_id = $_GET['page_id'];
$page_id ? $page_id : $page_id = $_POST['page_id'];

// Hold it — no ID, no entrance.
if ( !$page_id ) {
	header('location:sttc.page-list.php');
	die();
}
if ( $page_id && !is_numeric($page_id))
{
	header('location:sttc.page-list.php');
	die();
}

$view = new GrlxView;
$link = new GrlxLinkStyle;
$message = new GrlxAlert;
$form = new GrlxForm;
$fileops = new GrlxFileOps;
$sl = new GrlxSelectList;

// Make sure the image folder exists and is accessible.
$alert_output .= $fileops->check_or_make_dir('../'.DIR_STATIC_IMG);

$view-> yah = 6;


if ( $msg == 'created' ) {
	$alert_output .= $message->success_dialog('New page built. Add your content below.');
}






// ! Delete old blocks
if ( $delete_block_id && is_numeric($delete_block_id) && $page_id )
{
	$db->where('id', $delete_block_id);
	$success = $db->delete('static_content');
}


// ! Write blocks’ sort order
if ( $_POST && $page_id ) {

	// “default” matches the pattern filenames, e.g. /assets/patterns/hilt.default.php
	$pattern_id = register_variable('pattern_id');
	if ( !$pattern_id || $pattern_id == NULL || $pattern_id == '' )
	{
		$pattern_id = 'default';
	}


	// ! Update the database
	// Prep the database update statement.
	$data = array (
		'title' => $page_title,
		'description' => $page_description,
		'layout' => $layout_id,
		'options' => $pattern_id,
		'date_modified' => $db->now()
	);

	$db->where('id',$page_id);
	$success_item = $db->update('static_page', $data);

	$link-> url('sttc.page-list.php');
	$link-> tap('Return to the page list');

	$static = new GrlxStaticPage($page_id);
	$static-> getInfo();

	$alert_output .= $message->success_dialog('Page saved. '.$link-> paint().'.');
}

if ( !$static && $page_id )
{
	$static = new GrlxStaticPage($page_id);
	$static->getInfo();
}

// ! Update the content sort_order

// Automatically resort blocks to remove gaps in sort_order.

if ( $page_id && $original_sort_order && $sort_order )
{
	$resorted_list = array();
	foreach ( $original_sort_order as $key => $val )
	{
		if ( $sort_order[$key] < $val )
		{
			$val = ($sort_order[$key] - 0.5) . 'a';
		}
		elseif ( $sort_order[$key] > $val )
		{
			$val = ($sort_order[$key] + 0.5) . 'b';
		}
		$resorted_list[$val] = $key;
	}
}

// Update the official records.
if ( $resorted_list && count($resorted_list) > 0 )
{
	ksort($resorted_list);
	$i = 1;
	foreach ( $resorted_list as $key => $val )
	{
		$data = array(
			'sort_order' => $i
		);
		$db -> where('id', $val);
		$db -> update('static_content', $data);
		$i++;
	}
}

// Check sort_order anyway. Don’t leave room for any gaps.
elseif ($page_id)
{
	$cols = array('id','sort_order');
	$db->where('page_id', $page_id);
	$db->orderBy('sort_order', 'ASC');
	$block_list = $db->get('static_content',NULL,$cols);
}
if ( $block_list )
{
	$new_order = array();
	$i = 1;
	foreach ( $block_list as $key => $val )
	{
		$data = array('sort_order' => $i);
		$db->where('id',$val['id']);
		$db->update('static_content', $data);
		$i++;
	}
}






// ! Display logic

// Get all relevant info about this page.
if ( $page_id && is_numeric($page_id) ) {

	$cols = array(
		'id',
		'title',
		'options',
		'description',
		'layout'
	);
	$db->where('id',$page_id);
	$page_info = $db->getOne('static_page', $cols);


	// Content blocks
	$cols = array(
		'id',
		'title',
		'sort_order',
		'image'
	);
	$db->where('page_id',$page_id);
	$db->orderBy('sort_order', 'ASC');
	$content_list = $db->get('static_content', NULL, $cols);
}


if ( $page_info )
{
	$settings_form .= <<<EOL
<input type="hidden" name="page_id" id="page_id" value="$page_id"/>
<p>
	<label for="page_title">Title</label>
	<input type="text" name="page_title" id="page_title" maxlength="64" value="$page_info[title]" style="width:16rem"/>
</p>
<p>
	<label for="page_description">Description</label>
	<input type="text" name="page_description" id="page_description" value="$page_info[description]" style="width:16rem"/>
</p>

EOL;
}

if ( $content_list )
{

	$edit_link = new GrlxLinkStyle;
	$edit_link->url('sttc.block-edit.php');
	$edit_link->title('Edit block info.');
	$edit_link->reveal(false);
	$edit_link->action('edit');

	$delete_link = new GrlxLinkStyle;
	$delete_link->url('sttc.page-edit.php');
	$delete_link->title('Delete block.');
	$delete_link->reveal(false);
	$delete_link->action('delete');


	$heading_list[] = array(
		'value' => 'Order',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Title',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Image',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Actions',
		'class' => null
	);

	$list-> headings($heading_list);
	$list-> draggable(false);
	$list-> row_class('content-blocks');


	foreach ( $content_list as $key => $val )
	{

		// Set up options unique to this item in the list.
		$delete_link->query("delete_block_id=$val[id]&page_id=$page_id");
		$edit_link->query("block_id=$val[id]");

		// General actions for this item.
		$action_output = $delete_link->icon_link('delete').$edit_link->icon_link();

		if ( $val['image'] && $val['image'] != '' )
		{
			$preview = '<img src="'.$milieu_list['directory']['value'].$val['image'].'" alt="'.$val['image'].'" style="max-height:120px"/>';
		}
		else
		{
			$preview = "-";
		}

		$order  = '<input type="hidden" name="original_sort_order['.$val['id'].']" value="'.$val['sort_order'].'"/>';
		$order .= '<input type="text" size="3" style="width:3rem" name="sort_order['.$val['id'].']" value="'.$val['sort_order'].'"/>';

		if ( $val['title'] && $val['title'] != '' )
		{
			$title = $val['title'];
		}
		else
		{
			$title = '(untitled)';
		}

		$list_items[$val['id']] = array(
			'sort_order'=> $order,
			'title'=> '<a href="sttc.block-edit.php?block_id='.$val['id'].'">'.$title.'</a>',
			'image'=> '<a href="sttc.block-edit.php?block_id='.$val['id'].'">'.$preview.'</a>',
			'action'=> $action_output
		);
	}
	$list->content($list_items);
	$block_output  = $list->format_headings();
	$block_output .= $list->format_content();
}

if ( $page_id )
{
	$block_output .= '<a href="sttc.block-edit.php?page_id='.$page_id.'" class="create btn primary new"><i></i>Create a block</a>'."\n";
}




// Which theme directory does this site use?
$theme_directory = get_current_theme_directory($db);

if (!$theme_directory || $theme_directory === FALSE)
{
	$alert_output .= $message->alert_dialog('I couldn’t determine the <a href="./site.theme-manager.php">site’s theme</a>, and so can’t find any theme pattern files.');
}


// ! Pattern select

// Scan the current theme for pattern files in case the user
// renamed some or created new ones.
if ( $theme_directory && $theme_directory !== FALSE)
{
	$file_list = scandir('../themes/'.$theme_directory);
	if($file_list)
	{
		foreach($file_list as $filename)
		{
			if ( substr($filename,0,8) == 'pattern.')
			{
				$filename = explode('.',$filename);
				$pattern_order_list[$filename[1]] = str_replace('-',' ',$filename[1]);
			}
		}
	}
}

// Build a list to let the artist chooses a pattern for this static page.

if ( $pattern_order_list )
{
	$order_output = build_select_simple('pattern_id',$pattern_order_list, $page_info['options'],'width:200px');
}
else {
	if ( !$theme_directory )
	{
		$theme_directory = '(unknown)';
	}
	$order_output = 'I couldn’t find any <a href="http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/static-patterns">pattern files</a> in the /themes/'.$folder_name.' folder.';
}


$form->row_class('widelabel');
$order_output = '
<div class="'.$form->row_class.'">
		<label>Default block pattern</label>
'."\n".$order_output."\n";


$layout_options['list'] = array (
	'id' => 'list',
	'title' => 'List'
);
$layout_options['grid'] = array (
	'id' => 'grid',
	'title' => 'Grid'
);

$sl-> setName('layout_id');
$sl-> setCurrent($page_info['layout']);
$sl-> setList($layout_options);
$sl-> setStyle('width:6rem');
$sl-> setValueID('id');
$sl-> setValueTitle('title');

$layout_select_output = '
<div class="'.$form->row_class.'">
	<label>Page layout</label>
	'.$sl-> buildSelect().'</div>';

if ( $page_info['title'] ) {
	$page_title = $page_info['title'];
}
else {
	$page_title = 'Untitled';
}



$link-> url('site.nav.php');
$link-> tap($static-> info['url']);
$path_link = $link-> paint();


$view->page_title('Edit static page: '.$page_title);
$view->tooltype('sttc');
$view->headline('Static page <span>'.$page_title.'</span>');

$link->url('sttc.page-list.php');
$link->tap('Back to list');
$action_output = $link->text_link('back');

$link->url('..'.$static-> info['url']);
$link->tap('View page live');
$action_output .= $link->button_secondary('view');

$view->action($action_output);


$form->multipart(true);
$form->send_to('sttc.page-edit.php');


$layout_form  = $layout_select_output . $order_output;

$view->group_css('Layout');
$view->group_h2('Metadata');
$view->group_instruction('General information for this static page.');
$view->group_contents($settings_form);
$settings_output = $view->format_group().'<hr />';
$form_output .= $form->form_buttons();

$view->group_h2('Layout');
$view->group_contents($layout_form);
$view->group_instruction('Content block arrangement. “Patterns” are how each block’s image, text, etc. are laid out in HTML. Edit them in your site’s /themes/'.$theme_directory.' folder, and override them per block.');
$layout_output = $view->format_group().'<hr/>';

$view->group_h2('Content blocks');
$view->group_instruction('Stuff the readers see. Each block represents a “chunk” of information that may include a title, an image, a paragraph or two, HTML, and a link to another website.');
$view->group_contents($block_output);
$content_output = $view->format_group();



// ! Display


$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $form->open_form();
$output .= $hidden_fields;
$output .= $settings_output;
$output .= $layout_output;
$output .= $content_output;

print($output);
print('<hr/><button class="btn primary save right" name="submit" type="submit" value="save"/><i></i>Save</button>');

$output  = $form->close_form();
$output .= $view->close_view();
print($output);
