<?php

/*****
 * Setup
 */

require_once('panl.init.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$list = new GrlxList;
$list->draggable(false);
$image = new GrlxImage;
$message = new GrlxAlert;

$view-> yah = 10;

// Default value
$page_title = 'Ad location editor';

$var_list = array('slot_id','change_to','change_from','label','title');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

// No ad selected? Send ’em back to the list.
if ( !$slot_id || !is_numeric($slot_id) ) {
	header('location:ad.list.php');
}

// List of status levels.
$priority_list = array(
	'1' => 'High', // 1 = priority
	'0' => 'Normal', // 0 = nothing special
	'-1' => 'Hidden' // below zero = out of the loop.
);


/*****
 * Updates
 */

if ( $label && $title && $slot_id ) {
	$data = array (
		'label' => $label,
		'title' => $title
	);
		$db->where('id',$slot_id);
		$db->update('theme_slot', $data);
		$alert_output = $message->success_dialog('Slot data saved.');
}

if ( $change_from && $slot_id ) {
	$change_made = false;
	foreach ( $change_from as $key => $this_from ) {
		$this_to = $change_to[$key];

		if ( $this_from == 'off' && $this_to == 'on' ) {
			$data = array (
				'ad_reference_id' => $key,
				'slot_id' => $slot_id,
				'priority' => '1'
			);
			$id = $db->insert('ad_slot_match', $data);
			$change_made = true;
		}
		if ( $this_from == 'on' && !$this_to ) {
			$db->where('ad_reference_id',$key);
			$db->where('slot_id',$slot_id);
			$db->delete('ad_slot_match', null);
			$change_made = true;
		}
	}
}

if ( $change_made == true ) {
	$link-> title = 'Go back to the screen with all your site’s ads.';
	$link-> url = 'ad.list.php';
	$link-> tap = 'Return to the ad list';
	$alert_output = $message->success_dialog('Ads reassigned. '.$link-> paint());
}


/*****
 * Display logic
 */

if ( $slot_id ) {
	// Get basic information about this slot.
	$slot_info = get_slot_info($slot_id,$db);

	// What ads are in this slot?
	$match_list = get_ad_slot_matches($slot_id,null,$db);
}


// What ads might fit into this slot?
if ( $slot_info ) {
//	$possible_ad_list = $db->where('large_width', $slot_info['max_width'],'<=')
//	->where('large_height', $slot_info['max_height'],'<=')
	$possible_ad_list = $db->get ('ad_reference',null,'id,title,source_id,source_rel_id,large_width,large_height,large_image_url,code');
	if ( $possible_ad_list ) {
		$possible_ad_list = rekey_array($possible_ad_list,'id');
	}
}


if ( $possible_ad_list ) {
	$image = new GrlxImage;
	$image-> style = 'max-height:100px;max-width:468px';

	foreach ( $possible_ad_list as $key => $val ) {

		if ( $match_list[$key] ) {
			$action  = '<input type="checkbox" id="change_to['.$key.']" name="change_to['.$key.']" checked="checked" value="on"/> <label for="change_to['.$key.']">on</label>'."\n";
			$action .= '<input type="hidden" name="change_from['.$key.']" value="on"/>'."\n";
		}
		else {
			$action  = '<input type="checkbox" id="change_to['.$key.']" name="change_to['.$key.']" value="on"/> <label for="change_to['.$key.']">off</label>'."\n";
			$action .= '<input type="hidden" name="change_from['.$key.']" value="off"/>'."\n";
		}

		$title = $val['title'];
		$title ? $title : $title = 'Untitled';

		if ( $val['large_image_url'] ) {
			$image-> src = $val['large_image_url'];
			$image-> alt = $val['large_image_url'];
			$link-> url('ad.promo-edit.php?ad_id='.$key);
			$link-> tap = $image-> paint();
			$view_me = $link-> paint();
		}
		elseif($val['source_id'] == 3) {
			$view_me = '<a href="ad.adsense-edit.php?ad_id='.$key.'">(AdSense code)</a>';
		}
		elseif($val['code'] !== null) {
			$view_me = '(PW code)';
		}
		else {
			$view_me = '-';
		}

		$ad_list[] = array (
			$title,
			$view_me,
			$action
		);
	}
}

$number_o_ads = qty('ad',count($possible_ad_list));


if ( $ad_list ) {
	$heading_list = array('Ad','Action');
	$list->headings($heading_list);
	$list->row_class('ad');
	$list->content($ad_list);

//	$ad_list_instructions  = '<p>This slot measures <strong>'.$slot_info['max_width'].' &times; '.$slot_info['max_height'].'</strong> pixels. That means <strong>'.$number_o_ads.'</strong> can fit here.</p>'."\n";

	$ad_list_output .= $list->format_headings();
	$ad_list_output .= $list->format_content();

}
else {
	$ad_list_output = '<h2>No ad images fit into this slot.</h2>'."\n";
}


$label_output = '<input type="text" name="label" id="label" value="'.$slot_info['label'].'"/>';
$code_output = '<input type="text" id="template-code" value="&lt;?=show_ad(\''.$slot_info['label'].'\') ?&gt;"';
$title_output = '<input type="text" name="title" id="title" value="'.$slot_info['title'].'"/>';




// Group
$view->group_h2('Ad list');
$view->group_instruction($ad_list_instructions);
$view->group_contents( $ad_list_output );
$content_output .= $view->format_group().'<hr />';


// Group
$view->group_h2('Metadata');
$view->group_instruction('A label for your reference.');
$view->group_contents( $title_output );
$content_output .= $view->format_group().'<hr />';

// Group
$view->group_h2('Template code');
$view->group_instruction('Every slot in your site — the places where ads go — needs a unique ID. <strong>Change this only if you’re willing to edit your site’s theme.</strong>');
$view->group_contents( $label_output.$code_output );
$content_output .= $view->format_group();




// Setup
$view->page_title('Ad slot');
$view->headline('Ad slot <span>'.$slot_info['title'].'</span>');
$view->tooltype('ad');
$view->group_css('ad');


/*****
 * Display
 */
print( $view->open_view() );
print( $view->view_header() );
 ?>

<div id="edit_modal" class="reveal-modal" data-reveal></div>

<?=$alert_output?>
	<form accept-charset="UTF-8" action="ad.slot-edit.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="grlx_xss_token" value="<?=$_SESSION['admin']?>"/>
<?=$content_output ?>

<?php if ( $ad_list ) : ?>
		<p><button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button></p>
<?php endif; ?>
		<input type="hidden" id="slot-id" name="slot_id" value="<?=$slot_id ?>"/>
	</form>

<?php

/*
$js_call = <<<EOL
	$(function() {
		$('#label').blur( function() {
			var new_label = $('#label').val();
			var id = $('#slot-id').val();

			$.ajax({
				type: "GET",
				url: "slot.label-set.ajax.php",
				data: "label=" + new_label + "&id=" + id,
				dataType: "html",
			});

			var y = '<?=get_ad(\'' + new_label + '\') ?>';
			$('#template-code').val(y);
		});
	});

EOL;
*/

print($view->close_view()); ?>