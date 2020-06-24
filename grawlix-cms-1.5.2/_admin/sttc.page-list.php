<?php

/* Artists use this script to browse their static site pages.
 */

/* ! Setup */

require_once('panl.init.php');

$view = new GrlxView;
$modal = new GrlxForm_Modal;
$link = new GrlxLinkStyle;

$view-> yah = 6;
$preview_limit = 5;


/* ! Updates */

if ( $_POST['modal-submit'] && is_numeric($_POST['delete_id']) ) {
	$delete_id = $_POST['delete_id'];
	$result = $db
		-> where('id', $delete_id)
		-> delete('static_page');
	$result = $db
		-> where('rel_id', $delete_id)
		-> where('rel_type', 'static')
		-> where('edit_path', 1)
		-> delete('path');
}


/* ! Display logic */

// Grab all pages from the database.
$sql = "
SELECT
	pg.id,
	pg.title,
	edit_path,
	url
FROM
	".DB_PREFIX."static_page pg,
	".DB_PREFIX."path pt
WHERE
	pt.rel_id = pg.id
	AND pt.rel_type = 'static'
";
$page_list = $db->rawQuery ($sql, FALSE, FALSE);


// Get some content so we can make a sensible preview for artists.
if ( $page_list ) {
	foreach ( $page_list as $key=>$val ) {
		$page_id = $val['id'];
		$preview = ''; // reset
		$cols = array('title');
		$db->where('page_id',$page_id);
		$db->orderBy('sort_order','ASC');
		$content = $db->get ('static_content', NULL, $cols);
		if ( $content )
		{
			$i = 1; // reset
			foreach ( $content as $key2 => $val2 )
			{
				$i++;
				if ( strlen($val2['title']) > 20 )
				{
					$val2['title'] = substr($val2['title'],0,18).'…';
				}
				$preview .= $val2['title'].'<br/>';
				if ( $i > $preview_limit ) {
					break;
				}
			}
		}
		else {
			$preview = '<p>No preview available</p>';
		}
		$page_list[$key]['preview'] = $preview;
	}
}

if ( $page_list ) {
	$page_list_output = '<ul class="small-block-grid-2 medium-block-grid-3 large-block-grid-4">'."\n";
	foreach ( $page_list as $key => $val ) {
		if ( $val['edit_path'] == 1 ) {
			$title = urlencode($val['title']);
			$delete_link = new GrlxLinkStyle;
			$delete_link->url('sttc.page-delete.ajax.php');
			$delete_link->title('Delete this page.');
			$delete_link->reveal(true);
			$delete_link->query("id=$val[id]&amp;title=$title");
			$this_action = $delete_link->icon_link('delete');
		}
		else {
			$delete_link = new GrlxLinkStyle;
			$delete_link->i_only(true);
			$delete_link->id();
			$this_action = $delete_link->icon_link('locked');
		}

		// Truncate titles that are way too long for the preview tiles.
		if ( strlen($val['title']) > 20 )
		{
			$val['title'] = substr($val['title'],0,18).'…';
		}

		$page_list_output .= <<<EOL
		<li id="page-$val[id]">
		<div class="page sttc">
			<a href="sttc.page-edit.php?page_id=$val[id]">
				<h3>$val[title]</h3>
				<p>$val[preview]</p>
			</a>
			$this_action
			<a class="edit" href="sttc.page-edit.php?page_id=$val[id]">
				<i class="edit"></i>
			</a>
		</div>
		</li>
EOL;
	}
	$page_list_output .= '</ul>'."\n";
}


$view->page_title('Static pages');
$view->tooltype('sttc');
$view->headline('Static pages');

$link->url('site.nav.php');
$link->tap('Edit menu');
$action_output = $link->text_link('menu');

$link->url('sttc.page-new.php');
$link->tap('New page');
$action_output .= $link->button_secondary('new');

$view->action($action_output);


/* ! Display */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $modal->modal_container();
$output .= $alert_output;
print($output);
?>
	<section>
<?php
if ( $page_list_output ) {
	print($page_list_output);
}
else {
	$message = new GrlxAlert;
	print( $message->info_dialog('Your site has no static pages. No FAQ, no About the Artist, nothing.') );
}
?>
	</section>
<?php
$js_call = <<<EOL
	$( "a.delete i" ).hover( // highlight item to be deleted
		function() {
			$( this ).parent().parent().addClass("red-alert");
		}, function() {
			$( this ).parent().parent().removeClass("red-alert");
		}
	);
	$( "i.edit" ).hover( // highlight the editable item
		function() {
			$( this ).parent().parent().addClass("editme");
		}, function() {
			$( this ).parent().parent().removeClass("editme");
		}
	);
EOL;

$view->add_inline_script($js_call);
$output = $view->close_view();
print($output);
?>
