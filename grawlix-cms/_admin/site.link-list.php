<?php

/* Artists use this script to create a link list.
 */

/*****
 * Setup
 */

require_once('panl.init.php');

$view = new GrlxView;
$modal = new GrlxForm_Modal;
$message = new GrlxAlert;
$list = new GrlxList;
$form = new GrlxForm;
$fileops = new GrlxFileOps;

$image_path = $milieu_list['directory']['value'].'/assets/images/icons';

$view-> yah = 11;

/*****
 * Updates
 */

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

	$input = $_POST['input'];
	foreach ( $input as $val ) {
		$val = trim($val);
		$val = htmlspecialchars($val, ENT_COMPAT);
	}
	//$input['url'] = mb_strtolower($input['url'],"UTF-8"); //Some URLs are case-sensitive!
	if ( count($_FILES) > 0 ) {
		if ( in_array($_FILES['input']['type']['img_path'], $allowed_image_types) )
		{
			$file_name = basename($_FILES['input']['name']['img_path']);
			$uploadfile = '..'.$image_path.'/' . $file_name;
			$web_file_path = $image_path.'/' . $file_name;

			if (!move_uploaded_file($_FILES['input']['tmp_name']['img_path'], $uploadfile)) {
				$web_file_path = $_POST['old_img_url'];
			}
		}
		else
		{
			$alert_output .= $message->alert_dialog('You can only upload JPG, PNG, GIF or SVG files.');
		}
	}

	// Act on an edit from the reveal modal
	if ( $_POST['modal-submit'] && is_numeric($_POST['edit_id']) ) {
		if ( $input['title'] && $input['url'] ) {
			$data = array(
				'title' => $input['title'],
				'img_path' => $web_file_path,
				'url' => $input['url']
			);
			$db -> where('id', $_POST['edit_id']);
			$db -> update('link_list', $data);

			if ( $db -> count <= 0 ) {
				$alert_output .= $message->alert_dialog('Could not edit link.');
			}
		}
	}

	// Save new link
	if ( $_POST['submit'] ) {
		if ( $input['title'] && $input['url'] ) {

			$file_name = basename($_FILES['icon_file']['name']);
			$uploadfile = '..'.$image_path .'/'. $file_name;
			$web_file_path = $image_path .'/'. $file_name;

			if (move_uploaded_file($_FILES['icon_file']['tmp_name'], $uploadfile)) {
			} else {
			}

			$data = array(
				'title' => $input['title'],
				'url' => $input['url'],
				'sort_order' => 0,
				'img_path' => $web_file_path
			);
			$new_id = $db -> insert('link_list', $data);
			if ( $new_id <= 0 ) {
				$alert_output = $message->alert_dialog('Could not add new link.');
			}
		}
	}
}


/*****
 * Display logic
 */

$list->row_class('link');
$list->sort_by('link');
$list->draggable(true);

$delete_link = new GrlxLinkStyle;
$delete_link->i_only(true);
$delete_link->action('delete');

$edit_link = new GrlxLinkStyle;
$edit_link->url('site.link-edit.ajax.php');
$edit_link->reveal(true);
$edit_link->action('edit');

// Fetch the link list
$cols = array(
	'title',
	'url',
	'sort_order',
	'img_path',
	'id'
);
$result = $db
	-> orderBy('sort_order', 'ASC')
	-> get('link_list', NULL, $cols);

if ( $db -> count > 0 ) {
	foreach ( $result as $item ) {
		if ( $item['img_path'] ) {
			$icon = '<img src="'.$item['img_path'].'" alt="icon" />';
		}
		else {
			$icon = '';
		}

		$delete_link->id("id-$item[id]");
		$action_output = $delete_link->icon_link();
		$edit_link->query("edit_id=$item[id]");
		$action_output .= $edit_link->icon_link();

		$list_items[$item['id']] = array(
			$icon.' '.$item['title'],
			$item['url'],
			$action_output
		);
	}
}

$alert_output .= $fileops->check_or_make_dir('..'.$image_path);

$view->page_title('Link list');
$view->tooltype('link');
$view->headline('Link list');

if ( $list_items ) {
	$list->headings( array('Title', 'URL', 'Actions') );
	$list->content($list_items);
	$links_output  = $list->format_headings().'<br style="clear:both"/><!-- don’t judge me -->'."\n";
	$links_output .= $list->format_content();
}

if ( !$links_output ) {
	$links_output = '<b>You have no links to other sites.</b>';
}

$view->group_css('link');
$view->group_h2('Your links');
$view->group_contents($links_output);
$content_output = $view->format_group().'<hr />';

$form->multipart(true);
$form->send_to($_SERVER['SCRIPT_NAME']);

$form->input_title('input[title]');
$form->size('20');
$form->value('Site name');
$form_output = $form->paint();

$form->input_url('input[url]');
$form->value('http://');
$form_output .= $form->paint();

$form->input_file('icon_file');
$form->label('Icon');
$form_output .= $form->paint();

$form->contents($form_output);
$form_output = $form->build_form();

$view->group_h2('Add new');
$view->group_contents($form_output);
$content_output .= $view->format_group();


/*****
 * Display
 */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $modal->modal_container();
$output .= $content_output;
print($output);
$js_call = <<<EOL
	$( '[id^="id-"]' ).click( // delete an item
		function() { // update the db
			var item = $(this).attr('id'); // id of the item to change
			var parent = $('#'+item).parent().parent().parent(); // the li
			$.ajax({
				url: "site.link-delete.ajax.php",
				data: "delete=" + item,
				dataType: "html",
				success: function(data){
					$( parent ).fadeOut( "300", function() {
						$( parent ).remove();
					});
			}
		});
	});
	$( "i.sort" ).hover( // highlight a draggable row
		function() {
			$( this ).parent().parent().addClass("dragging");
		}, function() {
			$( this ).parent().parent().removeClass("dragging");
		}
	);
	$( "a.edit" ).hover( // highlight the editable item
		function() {
			$( this ).parent().parent().addClass("editme");
		}, function() {
			$( this ).parent().parent().removeClass("editme");
		}
	);
	$( "i.delete" ).hover( // highlight a row to be deleted
		function() {
			$( this ).parent().parent().addClass("red-alert");
		}, function() {
			$( this ).parent().parent().removeClass("red-alert");
		}
	);
	$( "#sortable" ).sortable({ // sort items
		activate: function(event, ui) { // highlight the dragged item
			$( ui.item ).children().addClass("dragging");
		},
		deactivate: function(event, ui) { // turn off the highlight
			$( ui.item ).children().removeClass("dragging");
		},
		update : function () {
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
?>
