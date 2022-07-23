<?php

/* Artists use this script to control the main site navigation.
 */

/* ! Setup */

require_once('panl.init.php');

$view = new GrlxView;
$link = new GrlxLinkStyle;
$form = new GrlxForm;
$modal = new GrlxForm_Modal;
$message = new GrlxAlert;
$list = new GrlxList;

$view-> yah = 7;

/* ! Updates */

// Act on an edit from the reveal modal
if ( $_POST['modal-submit'] ) {

	$input = $_POST['input'];
	foreach ( $input as $val ) {
		$val = trim($val);
		$val = htmlspecialchars($val, ENT_COMPAT);
	}
	//$input['url'] = mb_strtolower($input['url'],"UTF-8"); //Some URLs are case-sensitive!

	// Add an external URL to nav
	if ( $_POST['modal-submit'] == 'add' ) {
		$data = array(
			'title' => $input['title'],
			'url' => $input['url'],
			'rel_type' => 'external',
			'sort_order' => 0,
			'in_menu' => 1
		);
		$new_id = $db->insert('path', $data);

		if ( $new_id == 0 ) {
			$current_alert_output = $message->alert_dialog('Item was not added.');
		}
	}

	// Save an edit
	if ( ($_POST['modal-submit'] == 'save') && is_numeric($_POST['edit_id']) ) {
		$data = array(
			'title' => $input['title'],
			'url' => $input['url']
		);
		$db->where('id', $_POST['edit_id']);
		$db->update('path', $data);

		if ( $db->count == 0 ) {
			$current_alert_output = $message->alert_dialog('Edits were not saved.');
		}
	}
}


/* ! Display logic */

// Fetch site navigation
$cols = array(
	'id',
	'title AS clickable_title',
	'url',
	'rel_id',
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

// Fetch books
$result = $db-> get ('book',null,'id,title');
$book_list = rekey_array($result,'id');

if ( $static_list && $nav_item ) {
  foreach ( $nav_item as $key => $val ) {
    if ( $val['rel_type'] == 'static' ) {
      $rel_id = $val['rel_id'];
      $nav_item[$key]['title'] = $static_list[$rel_id]['title'];
    }
  }
}

if ( $book_list && $nav_item ) {
  foreach ( $nav_item as $key => $val ) {
    if ( $val['rel_type'] == 'book' || $val['rel_type'] == 'archive' ) {
      $rel_id = $val['rel_id'];
      $nav_item[$key]['title'] = $book_list[$rel_id]['title'];
    }
  }
}

if ( $nav_item ) {
	foreach ( $nav_item as $key => $item ) {
		// Construct archive paths
		if ( $item['rel_type'] == 'archive' ) {
			$nav_item[$key]['url'] = $nav_item[$item['rel_id']]['url'].$item['url'];
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

// Build output
if ( $nav_item ) {

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

		if ($item['title'])
		{
			$title = $item['title'];
		}
		else
		{
			$title = '-';
		}

		if (strlen($item['url']) > 32)
		{
			$url = substr($item['url'],0,28).'…';
		}
		else
		{
			$url = $item['url'];
		}

		if (strlen($title) > 24)
		{
			$title = substr($title,0,20).'…';
		}

		$list_items[$item['id'].'||'.$item['in_menu']] = array(
			$item['clickable_title'],
			$title,
			$url,
//			$item['rel_type'],
			$vis_output,
			$edit_link->icon_link() // $action_output
		);
	}
}

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

$heading_list[] = array(
	'value' => 'Clickable text',
	'class' => null
);
$heading_list[] = array(
	'value' => 'Title',
	'class' => null
);
$heading_list[] = array(
	'value' => 'URL',
	'class' => null
);
/*
$heading_list[] = array(
	'value' => 'Type',
	'class' => null
);
*/
$heading_list[] = array(
	'value' => 'Visible',
	'class' => null
);
$heading_list[] = array(
	'value' => 'Actions',
	'class' => null
);

$list->headings($heading_list);
$list->content($list_items);
$display_output  = $list->format_headings().'<br style="clear:both"/><!-- don’t judge me -->'."\n";
$display_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
$display_output .= $list->format_content();


/* ! Display */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $modal->modal_container();
$output .= $current_alert_output;
$output .= $dupe_alert_output;
$output .= '<p>Drag to reorder the items in your main site menu.</p>';
$output .= '<form>'.$display_output.'</form>';
print($output);

$js_call = <<<EOL
	$( '[id^="delete-"]' ).click( // delete an item
		function() { // update the db
			var item = $(this).attr('id'); // id of the item to change
			var parent = $('#'+item).parent().parent(); // the li
			$.ajax({
				url: "site.nav-delete.ajax.php",
				data: "delete=" + item,
				dataType: "html",
				success: function(data){
					$( parent ).fadeOut( "300", function() {
						$( parent ).remove();
					});
			}
		});
	});
	$( '[id^="id-"]' ).click( // toggle visibility of item in the menu
		function() {
			var item = $(this).attr('id'); // id of the item to change
			var parent = $('#'+item).parent().parent().parent().attr('class'); // the class contains current visibility of item
			$.ajax({
				url: "ajax.visibility-toggle.php",
				data: "site-menu=" + item + "&class=" + parent,
				dataType: "html",
				success: function(data){
					if ( $('#'+item).parent().parent().parent().hasClass('vis_0') ) {
						$('#'+item).parent().parent().parent().removeClass("vis_0").addClass("vis_1");
					} else {
						$('#'+item).parent().parent().parent().removeClass("vis_1").addClass("vis_0");
					}
			}
		});
	});
	$( "i.delete" ).hover( // highlight a row to be deleted
		function() {
			$( this ).parent().parent().addClass("red-alert");
		}, function() {
			$( this ).parent().parent().removeClass("red-alert");
		}
	);
	$( "a.edit" ).hover( // highlight the editable item
		function() {
			$( this ).parent().parent().addClass("editme");
		}, function() {
			$( this ).parent().parent().removeClass("editme");
		}
	);
	$( "i.sort" ).hover( // highlight a draggable row
		function() {
			$( this ).parent().parent().addClass("dragging");
		}, function() {
			$( this ).parent().parent().removeClass("dragging");
		}
	);
	$( "#sortable" ).sortable({ // sort the menu items
		activate: function(event, ui) { // highlight the dragged item
			$( ui.item ).children().addClass("dragging");
		},
		deactivate: function(event, ui) { // turn off the highlight
			$( ui.item ).children().removeClass("dragging");
		},
		update : function() {
			serial = $('#sortable').sortable('serialize');
			$.ajax({
				url: "ajax.sort.php",
				type: "post",
				data: serial,
				error: function(){
					alert("AJAX error");
				}
			});
		}
	});
	$( "#sortable" ).disableSelection();
EOL;

$view->add_jquery_ui();
$view->add_inline_script($js_call);
$output = $view->close_view();
print($output);
