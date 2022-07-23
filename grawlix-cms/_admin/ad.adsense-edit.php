<?php

/*****
 * Setup
 */

include('panl.init.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;



$var_list = array('ad_id','title');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

$code = $_POST['code'];

// No ad selected? Send ’em back to the list.
if ( !$ad_id || !is_numeric($ad_id) ) {
//	header('location:ad.list.php');
}

// Folder in which we keep ad images.
$image_path = $milieu_list['directory']['value'].'/assets/images/ads';

// List of status levels. 
$priority_list = array(
	'1' => 'High', // 1 = priority
	'0' => 'Normal', // 0 = nothing special
	'-1' => 'Hidden' // below zero = out of the loop.
);




/*****
 * Updates
 */

// Prepare to update the ad’s database record.
if ( $_POST && $ad_id ) {
	$data = array (
		'code' => $code,
		'title' => $title
	);

	$db->where('id',$ad_id);
	$success = $db->update('ad_reference', $data);	


	if ( $success == 1) {
		$alert_output = $message->success_dialog('Add info saved. '.$link-> paint('grlx_ad_list'));
	}
}

if ( $_POST && !$ad_id ) {
	$data = array (
		'title' => $title,
		'code' => $code,
		'date_created' => $db-> NOW(),
		'source_id' => '3',
	);
	$ad_id = $db->insert('ad_reference', $data);	

	if ( $ad_id > 0) {
		$alert_output = $message->success_dialog('Add created. '.$link-> paint('grlx_ad_list'));
	}
}



/*****
 * Display logic
 */

$link-> preset('adsense_help');

if ( $ad_id ) {
	$ad_info = get_ad_info($ad_id,$db);
	$instruction_output = 'Edit your AdSense code, '.$link->paint().', below.';
}
else {
	$ad_info['title'] = 'New';
	$instruction_output = 'Edit your AdSense code, '.$link->paint().', below.';
}



if ( $ad_info ) {

	$ad_output = <<<EOL
<label for="title">Title (for your reference)</label>
<input type="text" style="width:10rem" name="title" id="title" value="$ad_info[title]"/>

<label for="code">AdSense Code</label>
<textarea name="code" id="code" rows="10">$ad_info[code]</textarea>

EOL;
}



/*****
 * Display
 */

$view->page_title('Adsense editor');
$view->headline('AdSense <span>'.$ad_info['title'].'</span>');
$view->tooltype('ad');
$view->group_css('ad');

print( $view->open_view() );
print( $view->view_header() );
?>

<div id="edit_modal" class="reveal-modal" data-reveal></div>

<?=$alert_output?>
<p><?=$instruction_output?></p>
		<form accept-charset="UTF-8" action="ad.adsense-edit.php" method="post" enctype="multipart/form-data">
<?php if ( $ad_output ) : ?>
<?=$ad_output?>
<?php endif; ?>
<?php if ( $ad_id ) : ?>
			<input type="hidden" name="ad_id" value="<?=$ad_id ?>"/>
			<p><button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button></p>
<?php else : ?>
			<p><button class="btn primary save" name="submit" type="submit" value="create"><i></i>Create</button></p>
<?php endif; ?>
		</form>

<?php print($view->close_view()); ?>