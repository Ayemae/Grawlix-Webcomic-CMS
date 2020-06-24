<?php

/* Artists use this script to add connections to social media services.
 */

// ! ------ Setup

require_once('panl.init.php');

$view = new GrlxView;
$form = new GrlxForm;
$modal = new GrlxForm_Modal;
$message = new GrlxAlert;

$view-> yah = 8;

// ! ------ Updates

// Reset comment service
if ( is_numeric($_GET['reset_comments']) ) {

	$service_id = $_GET['reset_comments'];
	$data = array('user_info' => null);
	$db->where('id', $service_id);
	$db->update('third_service', $data);
	$data = array('active' => 0);
	$db->where('service_id', $service_id);
	$db->update('third_match', $data);
}

// Zap the now-defunct free Livefyre service.
$db->where('service_id', '14');
$db->delete('third_match');


// Save edits from the info modal
if ( $_POST['edit'] ) {

	foreach ( $_POST['edit'] as $key => $val ) {
		if ( is_numeric($key) ) {
			$data = array('user_info' => $val);
			$db -> where('id', $key);
			if ( $db -> update('third_service', $data) ) {
				$affected_count++;
			}
		}
	}

	// Newly setup info so set the service to active
	if ( is_numeric($_POST['match_id']) ) {
		$match_id = $_POST['match_id'];
		$data = array('active' => 1);
		$db -> where('id', $match_id);
		$db -> update('third_match', $data);
	}

	// Widget
	if ( is_numeric($_POST['widget_id']) && $_POST['widget_value'] ) {
		$widget_id = $_POST['widget_id'];
		$data = array(
			'active' => 1,
			'value' => $_POST['widget_value']
		);
		$db -> where('id', $widget_id);
		$db -> update('third_widget', $data);
	}

	if ( $affected_count == 0 ) {
		$alert_output = $message->alert_dialog('Edits to data were not saved.');
	}
	else {
		$alert_output = $message->success_dialog('Your user info has been saved.');
	}
}

// Save comment info
if ( $_POST['comment_info'] && is_numeric($_POST['service_id']) ) {

	$service_id = $_POST['service_id'];
	$comment_info = htmlspecialchars(trim($_POST['comment_info']), ENT_COMPAT);

	// enter id into correct line of data table
	$data = array('user_info' => $comment_info);
	$db -> where('id', $service_id);
	if ( $db -> update('third_service', $data) ) {
		// turn on correct line in match table
		$data = array('active' => 1);
		$db -> where('function_id', 3);
		$db -> where('service_id', $service_id);
		$db -> update('third_match', $data);
		$alert_output = $message->success_dialog('Your user info has been saved.');
	}
	else {
		$alert_output = $message->alert_dialog('Edits to data were not saved.');
	}
}

// ! Is Patreon in the mix?
// We added Patreon in v1.5. This bit updates the database for older installations.

$db->where('label','patreon');
$result = $db->getOne('third_service','id');
if (!$result || count($result) == 0)
{
	$data = array (
		'title' => 'Patreon',
		'label' => 'patreon',
		'url' => 'https://patreon.com',
		'description' => 'Your username is the same name with which you log in to Patreon.',
		'info_title' => 'Username'
	);
	$service_id = $db->insert('third_service', $data);
}
if ($service_id)
{
	$data = array (
		'service_id' => $service_id,
		'function_id' => '1',
		'active' => '0'
	);
	$success = $db->insert('third_match', $data);
}


// ! ------ Display logic

// Fetch the service categories -- but not ads
$cols = array('id', 'title', 'description');
$result = $db
	-> orderBy('id', 'ASC')
	-> get('third_function', 3, $cols);
if ( $db->count > 0 ) {
	foreach ( $result as $item ) {
		$service_list[$item['id']] = array(
			'title' => $item['title'],
			'description' => $item['description'],
			'items' => array()
		);
	}
}

// Fetch widgets (Twitter timeline)
$cols = array(
	'w.id AS id',
	's.id AS service_id',
	's.title AS title',
	'w.title AS widget',
	'user_info AS value',
	'w.value AS widget_value',
	'active'
);
$result = $db
	-> join('third_service s', 's.id = w.service_id', 'INNER')
	-> orderBy('s.title', 'ASC')
	-> orderBy('w.title', 'ASC')
	-> where('w.id', 1)
	-> get('third_widget w', null, $cols);
if ( $db->count > 0 ) {
	foreach ( $result as $item ) {
		$widget_info[$item['id']] = $item;
	}
}

// ! Fetch all the third party info
$cols = array(
	'function_id',
	'm.id AS match_id',
	's.id AS service_id',
	's.title AS title',
	's.url AS url',
	's.label AS service',
	'info_title AS label',
	'user_info AS value',
	'active'
);
$result = $db
	-> join('third_service s', 's.id = m.service_id', 'INNER')
	-> orderBy('function_id', 'ASC')
	-> orderBy('s.title', 'ASC')
	-> get('third_match m', null, $cols);

if ( $db->count > 0 ) {
	foreach ( $result as $item ) {

		if ( $item['active'] === null ) {
			$item['active'] = 0;
		}
		// Build arrays suited to each function's needs
		// Follow
		if ( $item['function_id'] == 1 ) {
			$temp = array(
				'title' => $item['title'],
				'active' => $item['active'],
				'service_id' => $item['service_id'],
				'match_id' => $item['match_id'],
				'url' => $item['url'],
				'value' => $item['value']
			);
		}
		// Share
		if ( $item['function_id'] == 2 ) {
			$temp = array(
				'title' => $item['title'],
				'active' => $item['active'],
				'match_id' => $item['match_id'],
				'url' => $item['url']
			);
		}
		// Comments
		if ( $item['function_id'] == 3 ) {
			$temp = array(
				'title' => $item['title'],
				'active' => $item['active'],
				'service_id' => $item['service_id'],
				'url' => $item['url'],
				'label' => $item['label'],
				'value' => $item['value']
			);
			if ( ($item['active'] == 1) && isset($item['value']) ) {
				$comments_info = $temp;
			}
			switch ($item['service_id']) {
				case 12:
					$temp['logo'] = 'disqus.logo.svg';
					break;
				case 13:
					$temp['logo'] = 'intensedebate.logo.png';
					break;
				case 14:
					$temp['logo'] = 'livefyre.logo.svg';
					$temp['url'] = 'https://www.livefyre.com/auth/register/';
					break;
			}
		}
		$service_list[$item['function_id']]['items'][] = $temp;
	}
}

if ( $service_list ) {

	$view->group_css('social');

	$list = new GrlxList;
	$list->draggable(false);

	$edit_link = new GrlxLinkStyle;
	$edit_link->url('xtra.edit-info.ajax.php');
	$edit_link->title('Edit this service’s info.');
	$edit_link->reveal(true);
	$edit_link->action('edit');

	foreach ( $service_list as $function_id => $section_group ) {
		$view->group_h2($section_group['title']);
		$view->group_instruction($section_group['description']);

		// ! Follow
		//
		if ( $function_id == 1 ) {
			$form->title('Show/hide a follow link for this service.');
			$heading_list[] = array(
				'value' => 'Service',
				'class' => 'nudge'
			);
			$heading_list[] = array(
				'value' => 'Your info',
				'class' => 'nudge'
			);
			$heading_list[] = array(
				'value' => 'Actions',
				'class' => null
			);
			$list->row_class('follow');
			$list->headings($heading_list);
			$follow_output = $list->format_headings();
			foreach ( $section_group['items'] as $key => $val ) {
				// Get the id entered before activating the service
				if ( !$val['value'] ) {
					$row_css = ' unset';
					$username = '—';
					$edit_link->query("service_id=$val[service_id]&match_id=$val[match_id]");
					$action_output = $edit_link->icon_link();
				}
				else {
					$row_css = $val['active'];
					$username = strfunc_check_empty($val['value']);
					$edit_link->query("service_id=$val[service_id]");
					$form->id("id-$val[match_id]");
					$vis_output = $form->checkbox_switch($val['active']);
					$action_output = $vis_output.$edit_link->icon_link();
				}
				$follow_list[$val['service_id'].'||'.$row_css] = array(
					$val['title'],
					$username,
					$action_output
				);
			}
			$list->content($follow_list);
			$follow_output .= $list->format_content();
			$view->group_contents($follow_output);
			$content_output = $view->format_group().'<hr />';
			unset($heading_list);
		}
		// ! Share
		//
		if ( $function_id == 2 ) {
			$form->title('Show/hide a share link for this service next to your comic.');
			$heading_list[] = array(
				'value' => 'Service',
				'class' => 'nudge'
			);
			$heading_list[] = array(
				'value' => 'Actions',
				'class' => null
			);
			$list->row_class('share');
			$list->headings($heading_list);
			$share_output = $list->format_headings();
			foreach ( $section_group['items'] as $key => $val ) {
				$row_css = $val['active'];
				$form->id("id-$val[match_id]");
				$action_output = $form->checkbox_switch($val['active']);
				$share_list[$val['match_id'].'||'.$row_css] = array(
					$val['title'],
					$action_output
				);
			}
			$list->content($share_list);
			$share_output .= $list->format_content();
			$view->group_contents($share_output);
			$content_output .= $view->format_group().'<hr />';
			unset($heading_list);
		}
		// ! Comments
		//
		if ( $function_id == 3 ) {
			$comment_link = new GrlxLinkStyle;
			$comment_link->url('xtra.comments.ajax.php');
			$comment_link->reveal(true);
			if ( isset($comments_info) ) {
				$heading_list[] = array(
					'value' => 'Your chosen service',
					'class' => 'nudge'
				);
				$heading_list[] = array(
					'value' => 'Your '.lcfirst($comments_info['label']),
					'class' => 'nudge'
				);
				$heading_list[] = array(
					'value' => 'Actions',
					'class' => null
				);
				$list->row_class('follow');
				$list->headings($heading_list);
				$comment_output = $list->format_headings();
				$vis_link = new GrlxLinkStyle;
				$vis_link->url($comments_info['url']);
				$vis_link->title('Go to '.$comments_info['title']);
				$vis_link->action('extlink');
				$comment_link->title('Edit your user info.');
				$comment_link->action('edit');
				$comment_link->query("service_id=$comments_info[service_id]");
				$action_output = $vis_link->icon_link().$comment_link->icon_link();

				$comment_list[$comments_info['service_id'].'||1'] = array(
					$comments_info['title'],
					$comments_info['value'],
					$action_output
				);
				$list->content($comment_list);
				$comment_output .= $list->format_content();
				$comment_link->url('xtra.social.php');
				$comment_link->tap('Reset service');
				$comment_link->query("reset_comments=$comments_info[service_id]");
				$reset_output = $comment_link->button_tertiary('reset');
				$comment_output .= '<div class="note reset">Do you need to switch to another comment service?'.$reset_output.'</div>';
			}
			else {
				$comment_link->tap('Setup');
				$comment_output  = '<p>You can choose one of these comment services to plug into Grawlix. <strong>Sign up with your chosen service and follow their directions.</strong> Next, we’ll need you to enter a unique ID that they will provide.</p>';
				$comment_output .= '<div class="row comments">';
				foreach ( $section_group['items'] as $key => $val ) {
					$comment_link->query("service_id=$val[service_id]");
					$comment_output .= '<div>';
					$comment_output .= '<a href="'.$val['url'].'">';
					$comment_output .= '<img class="logo" src="img/'.$val['logo'].'" alt="'.$val['title'].'" />';
					$comment_output .= '<p>Sign up with '.$val['title'].'</p>';
					$comment_output .= '</a>';
					$comment_output .= $comment_link->button_secondary('select');
					$comment_output .= '</div>';
				}
				$comment_output .= '</div>';
			}
			$view->group_contents($comment_output);
			$content_output .= $view->format_group();
			unset($heading_list);
		}
	}
}

/*
if ( $widget_info ) {
	$view->group_h2('Widgets');
	$view->group_instruction('If your site theme supports widgets, you can turn them on here.');
	$edit_link->title('Edit this widget’s info.');
	$form->title('Show/hide this widget on your site.');
	$heading_list[] = array(
		'value' => 'Service',
		'class' => 'nudge'
	);
	$heading_list[] = array(
		'value' => 'Widget',
		'class' => 'nudge'
	);
	$heading_list[] = array(
		'value' => 'Actions',
		'class' => null
	);
	$list->row_class('widget');
	$list->headings($heading_list);
	$widget_output = $list->format_headings();
	foreach ( $widget_info as $id => $val ) {
		$edit_link->query("widget_id=$val[id]");
		if ( !$val['widget_value'] || !$val['value'] ) {
			$row_css = ' unset';
			$userinfo = '—';
			$action_output = $edit_link->icon_link();
		}
		else {
			$row_css = $val['active'];
			$userinfo = $val['value'];
			$form->id("widget-$val[id]");
			$action_output = $form->checkbox_switch($val['active']).$edit_link->icon_link();
		}
		$widget_list[$val['id'].'||'.$row_css] = array(
			$val['title'],
			$val['widget'],
			$action_output
		);
	}
	$list->content($widget_list);
	$widget_output .= $list->format_content();
	$view->group_contents($widget_output);
	$content_output .= $view->format_group();
	unset($heading_list);
}
*/

$view->page_title('Social media');
$view->tooltype('social');
$view->headline('Social media');


// ! ------ Display


$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $modal->modal_container();
$output .= $content_output;
print($output);

$js_call = <<<EOL
	$( "a.edit" ).hover( // highlight the editable item
		function() {
			$( this ).parent().parent().addClass("editme");
		}, function() {
			$( this ).parent().parent().removeClass("editme");
		}
	);
	$('[id^="id-"]').click(function(){
		var item = $(this).attr('id'); // id of the item to change
		var parent = $('#'+item).parent().parent().parent().attr('class'); // the class contains current visibility of item
		$.ajax({
			url: "ajax.visibility-toggle.php",
			data: "social=" + item + "&class=" + parent,
			dataType: "html",
			success: function(data){
				toggleVisStyle(item);
			}
		});
	});
	$('[id^="widget-"]').click(function(){
		var item = $(this).attr('id'); // id of the item to change
		var parent = $('#'+item).parent().parent().parent().attr('class'); // the class contains current visibility of item
		$.ajax({
			url: "ajax.visibility-toggle.php",
			data: "widget=" + item + "&class=" + parent,
			dataType: "html",
			success: function(data){
				toggleVisStyle(item);
			}
		});
	});
EOL;

$view->add_jquery_ui();
$view->add_inline_script($js_call);
$output = $view->close_view();
print($output);