<?php

/* Artists use this script to browse their static site pages.
 */

/* ! ------ Setup */

require_once('panl.init.php');

$view = new GrlxView;
$link = new GrlxLinkStyle;
$list = new GrlxList;
$message = new GrlxAlert;

$view-> yah = 2;


/* ! ------ Updates */

if ($_GET['yes_delete_id'] && is_numeric($_GET['yes_delete_id']))
{
	$delete_id = $_GET['yes_delete_id'];

	// Get a list of pages so we know what markers to delete.
	$db->where('book_id', $delete_id);
	$doomed_page_list = $db->get('book_page',NULL,'marker_id');
	if ( $doomed_page_list )
	{
		foreach ( $doomed_page_list as $key => $val )
		{
			if ($val['marker_id'] && $val['marker_id'] > 0)
			{
				$doomed_marker_list[] = $val['marker_id'];
			}
		}
		if ($doomed_marker_list)
		{

			$db->where('id',$doomed_marker_list,'IN');
			$db->delete('marker');
		}

	}
	$db->where('book_id', $delete_id);
	$db->delete('book_page');

	$db->where('id',$delete_id);
	$db->delete('book');

	$db->where('rel_id',$delete_id);
	$db->where('rel_type','book');
	$db->delete('path');

	$db->where('rel_id',$delete_id);
	$db->where('rel_type','archive');
	$db->delete('path');

	$alert_output = $message->success_dialog('Book deleted.');

}


if ($_GET['delete_id'] && is_numeric($_GET['delete_id']))
{
	$db->where('id',$_GET['delete_id']);
	$book_title = $db->getOne('book','title');
	$book_title = $book_title['title'];

	$db->where('book_id',$_GET['delete_id']);
	$page_count = $db->getOne('book_page','COUNT(id) AS tally');
	$page_count = $page_count['tally'];
	$yes_delete = '<a href="book.list.php?yes_delete_id='.$_GET['delete_id'].'">Yes, delete</a>';
	if ($page_count == 1)
	{
		$alert_output = $message->alert_dialog('Are you sure you want to delete <em>'.$book_title.'</em> with '.$page_count.' page? '.$yes_delete.'.');
	}
	else
	{
		$alert_output = $message->alert_dialog('Are you sure you want to delete <em>'.$book_title.'</em> with '.$page_count.' pages? '.$yes_delete.'.');
	}
}



/* ! ------ Display logic */


// Grab all books from the database.

$db->orderBy('title','ASC');
$book_list = $db->get ('book', NULL, 'title,id');

// Which is the default home page, if any?
$cols = array('id,rel_type,rel_id');
$home = $db
	->where('url','/')
	->getOne('path',$cols);


if ( $book_list ) {
	foreach ( $book_list as $key => $val ) {

		// Get its path.
		$db->where('rel_id',$val['id']);
		$db->where('rel_type','book');
		$db->where('url','/', '!=');
		$url = $db->getOne('path','url');

		if ($url && $url['url'])
		{
			$url = $_SERVER['HTTP_HOST'].'<a href="site.nav.php" title="Edit path.">'.$url['url'].'</a>';
		}
		else
		{
			$url = $_SERVER['HTTP_HOST'].'<a href="site.nav.php" title="Edit path.">(none)</a>';
		}

		$edit_link = new GrlxLinkStyle;
		$edit_link->url('book.view.php');
		$edit_link->title('Edit book pages.');
		$edit_link->reveal(false);
		$edit_link->action('edit');

		$info_link = new GrlxLinkStyle;
		$info_link->url('book.edit.php');
		$info_link->title('Edit book info.');
		$info_link->reveal(false);
		$info_link->action('info');

		// Only let the user delete books if there’s more than one.
		if (count($book_list) > 1)
		{
			$delete_link = new GrlxLinkStyle;
			$delete_link->url('book.list.php');
			$delete_link->title('Delete book.');
			$delete_link->reveal(false);
			$delete_link->action('edit');
			$delete_link->query("delete_id=$val[id]");
			$delete_link_output = $delete_link->icon_link('delete');

		}
	
		// Get the number of pages per book.
		$db->where('book_id',$val['id']);
		$tally = $db->getOne('book_page','COUNT(id) AS tally');

		// Make the title clickable.
		$title = urlencode($val['title']);
		$edit_link->query("book_id=$val[id]");

		$info_link->query("book_id=$val[id]");

		// General actions for this item.
		$action_output = $delete_link_output.$edit_link->icon_link().$info_link->icon_link();

		if ($home['rel_type'] == 'book' && $home['rel_id'] == $val['id'])
		{
			$is_home = '<strong>yes</strong>';
		}
		else
		{
			$is_home = '<span class="disabled">no</span>';
		}

		// Assemble the list item.
		$list_items[$val['id']] = array(
			'title'=> '<a href="book.view.php?book_id='.$val['id'].'">'.$val['title'].'</a>',
			'page_tally'=> $tally['tally'],
			'is_home' => $is_home,
			'url' => $url,
			'action'=> $action_output
		);
	}
}

if ( $list_items ) {

	$heading_list[] = array(
		'value' => 'Title',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Pages',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Home',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'URL',
		'class' => null
	);
	if ( $marker_type_list ) {
		$heading_list[] = array(
			'value' => 'Marker',
			'class' => null
		);
	}
	$heading_list[] = array(
		'value' => 'Actions',
		'class' => null
	);

	$list-> headings($heading_list);
	$list-> draggable(false);
	$list-> row_class('bookshelf');

	// Mix it all together.
	$list->content($list_items);
	$book_list_output  = $list->format_headings();
	$book_list_output .= $list->format_content();

}

$view->page_title('Bookshelf');
$view->tooltype('sttc');
$view->headline('Bookshelf');

if(is_file('book.book-create.php')) {
	$link->url('book.book-create.php');
	$link->tap('New book');
	$action_output = $link->button_secondary('new');
	$view->action($action_output);
}



$options_output  = '<ul>'."\n";
$options_output .= '	<li><a href="site.config.php">Set home page</a></li>'."\n";
$options_output .= '	<li><a href="site.menu.php">Set menu paths</a></li>'."\n";
if (is_file('book.import-wp.php'))
{
	$options_output .= '	<li><a href="book.import-wp.php">New book from ComicEasel</a></li>'."\n";
}
$options_output .= '	<li><a href="book.page-create.php">New page in the current book</li>'."\n";
$options_output .= '<ul>'."\n";


// Group
$view->group_h2('Options');
$view->group_instruction('Other actions and settings.');
$view->group_contents($options_output);
$options_output = $view->format_group();



/* ! ------ Display */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
print($output);

if ( $book_list_output ) {
	print($book_list_output);
}
elseif(is_file('book.book-create.php')) {
	$message = new GrlxAlert;
	print( $message->info_dialog('Your site has no books. <a href="book.book-create.php">Create one</a>.') );
}

print('<hr>'.$options_output);

print( $view->close_view() );
