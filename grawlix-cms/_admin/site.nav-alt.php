<?php

/* Artists use this script to control the main site navigation.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$view = new GrlxView;
$link = new GrlxLinkStyle;
$form = new GrlxForm;
$modal = new GrlxForm_Modal;
$message = new GrlxAlert;
$list = new GrlxList;

$view-> yah = 7;

/*****
 * Updates
 */

// Act on an edit from the reveal modal
if ( $_POST ) {
	foreach($_POST['title'] as $key=>$val){
		


		$data = array(
			'title' => $_POST['title'][$key],
			'url' => $_POST['url'][$key],
			'in_menu' => $_POST['in_menu'][$key],
			'sort_order' => $_POST['sort_order'][$key]
		);
		$db->where('id', $key);
		$db->update('path',$data);

/*
		if ( $db->count == 0 ) {
			$current_alert_output = $message->alert_dialog('Edits were not saved.');
		}
*/
	}
}


/*****
 * Display logic
 */

// Fetch site navigation
$cols = array(
	'id',
	'title',
	'url',
	'rel_id',
	'sort_order',
	'rel_type',
	'in_menu'
);
$result = $db
	-> orderBy('sort_order', 'ASC')
	-> get('path', NULL, $cols);

if ( $result ) {
  $nav_item = rekey_array($result,'id');
}


// Fetch site static pages
$result = $db-> get ('static_page',null,'id,title');
$static_list = rekey_array($result,'id');

// Replace static pages’ labels with the pages’ actual meta titles.
/*
if ( $static_list && $nav_item ) {
  foreach ( $nav_item as $key => $val ) {
    if ( $val['rel_type'] == 'static' ) {
      $rel_id = $val['rel_id'];
      $nav_item[$key]['title'] = $static_list[$rel_id]['title'];
    }
  }
}
*/

if ( $nav_item ) {
	foreach ( $nav_item as $key => $item ) {
		// Construct archive paths
		if ( $item['rel_type'] == 'archive' ) {
//			$nav_item[$key]['url'] = $nav_item[$item['rel_id']]['url'].$item['url'];
		}
		$path_items[] = $nav_item[$key]['url'];
	}
}
else {
	$current_alert_output = $message->alert_dialog('You have no static pages, comics or external links to put in your site menu.');
}

// Check for duplicate paths
if ( $path_items ) {
	$dupe_check = array_count_values($path_items);
	foreach ( $dupe_check as $key => $val ) {
		if ( $val > 1 ) {
			$dupes[] = $key;
		}
	}
}

if ( isset($dupes) ) {
	$dupe_alert_output = $message->alert_dialog('Duplicates detected! You should create unique path names to avoid problems.');
}


if ( $nav_item )
{
	$output_2 = '<table>'."\n";
	$output_2 .= '<tr>'."\n";
	$output_2 .= '<th>Clickable text</th><th>Path</th><th>In menu</th><th>Order</th>'."\n";
	$output_2 .= '</tr>'."\n";
	foreach ( $nav_item as $key => $val )
	{
		$output_2 .= '<tr>'."\n";
		$output_2	.= '<td><input style="color:#000;padding:4px" name="title['.$key.']" value="'.$val['title'].'"/></td>'."\n";
		$output_2	.= '<td><input style="color:#000;padding:4px" name="url['.$key.']" value="'.$val['url'].'"/></td>'."\n";
		$output_2	.= '<td><input style="color:#000;padding:4px" name="in_menu['.$key.']" value="'.$val['in_menu'].'"/></td>'."\n";
		$output_2	.= '<td><input style="color:#000;padding:4px" name="sort_order['.$key.']" value="'.$val['sort_order'].'"/></td>'."\n";
		$output_2 .= '</tr>'."\n";
	}
	$output_2 .= '<table>'."\n";
}



// Build output
if ( $nav_item ) /*
{

	$list->row_class('menu');
	$list->sort_by('menu');
	$list->draggable(true);

	$form->title('Show/hide this item in the site menu.');

	$vis_link = new GrlxLinkStyle;
	$vis_link->i_only(true);

	$edit_link = new GrlxLinkStyle;
	$edit_link->url('site.nav-edit.ajax.php');
	$edit_link->title('Change this item’s tappable text or destination URL.');
	$edit_link->reveal(true);
	$edit_link->action('edit');


	foreach ( $nav_item as $item ) {
		$edit_link->query("edit_id=$item[id]");
		if ( $item['rel_type'] == 'external' ) {
			$vis_link->action('delete');
			$vis_link->id("delete-$item[id]");
			$vis_link->title('Delete this link from your site’s menu.');
			$vis_output = $vis_link->icon_link();
		}
		else {
			$form->id("id-$item[id]");
			$vis_output = $form->checkbox_switch($item['in_menu']);
		}
//		$action_output = $edit_link->icon_link();

		if ( isset($dupes) && in_array($item['url'], $dupes) ) {
			$item['url'] = '<span class="fixme">'.$item['url'].'</span>';
		}

		$list_items[$item['id'].'||'.$item['in_menu']] = array(
			$item['title'],
			$item['url'],
			$vis_output, //$item['rel_type'],
			$edit_link->icon_link() // $action_output
		);
	}
}
*/

$view->page_title('Main menu');
$view->tooltype('menu');
$view->headline('Main menu');

$link->url('sttc.page-new.php');
$link->tap('New page');
$action_output = $link->text_link('static');

$link->url('site.nav-edit.ajax.php');
$link->reveal(true);
$link->query('edit_id=new');
$link->title = 'External links go to other websites — like those you enjoy reading soooo much you just have to share it with your readers.';
$link->tap('New external link');
$action_output .= $link->button_secondary('new');
$view->action($action_output);

/*
$heading_list[] = array(
	'value' => 'Title',
	'class' => null
);
$heading_list[] = array(
	'value' => 'URL',
	'class' => null
);
$heading_list[] = array(
	'value' => 'In menu',
	'class' => null
);
$heading_list[] = array(
	'value' => 'Actions',
	'class' => null
);
*/

//$list->headings($heading_list);
//$list->content($list_items);
$display_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
$display_output .= $output_2;
$display_output .= '<input type="submit" />';




/*****
 * Display
 */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $modal->modal_container();
$output .= $current_alert_output;
$output .= $dupe_alert_output;
$output .= '<form action="site.nav-alt.php" method="post">'.$display_output.'</form>';
print($output);

$view->add_jquery_ui();
$view->add_inline_script($js_call);
$output = $view->close_view();
print($output);
