<?php

/*****
 * Setup
 */

require_once('panl.init.php');

$fileops = new GrlxFileOps;
$link = new GrlxLinkStyle;
$view = new GrlxView;
//$image_obj = new GrlxImage;

$view-> yah = 10;

$var_list = array('destination', 'small_image_url', 'medium_image_url', 'large_image_url', 'code');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}


// Folder in which we keep ad images.
$image_path = $milieu_list['directory']['value'].'/assets/images/ads';



/*****
 * Updates
 */

$check_these = array(
	'small' => 'small_image_url',
	'medium' => 'medium_image_url',
	'large' => 'large_image_url'
);


if ( $check_these ) {
	foreach ( $check_these as $key => $val ) {

		// Got a new file upload? Then upload it.
		$upload_status[$key] = upload_specific_file($val,$image_path);
		$upload_sizes[$key] = getimagesize('..'.$image_path.'/' . basename($_FILES[$val]['name']));
	}
}



// Prepare to update the ad’s database record.
if ( $_POST ) {
	$data = array (
		'code' => $code,
		'tap_url' => $destination
	);

	if ( $check_these ) {
		foreach ( $check_these as $key => $val ) {
			if ( is_array($upload_sizes[$key]) ) {
				$data[$key.'_width'] = $upload_sizes[$key][0];
				$data[$key.'_height'] = $upload_sizes[$key][1];
			}
		}
	}
	$data['source_id'] = 1;


	// Have we uploaded an image? Then add it to the list.
	// I made this optional so we don’t override a previously-
	// uploaded file’s record in the database.
	if ( $check_these ) {
		foreach ( $check_these as $key => $val ) {
			if ( $upload_status[$key][0] == 'success' ) {
				$data[$key.'_image_url'] = $image_path.'/' . basename($_FILES[$key.'_image_url']['name']);
			}
		}
	}

	// Updates, ho!
	$success = $db->insert('ad_reference', $data);

	if ( $success > 0 ) {
		header('location:ad.promo-edit.php?ad_id='.$success.'&msg=created');
	}

//	$link = new GrlxLink('grlx ad list');

/*
	if ( $success == 1) {
		$alert = new GrlxAlert;
		$alert_output = $alert-> success_dialog('Ad created. '.$link-> paint());
	}
*/
}



/*****
 * Display logic
 */

if ( $ad_id ) {
	$promo_info = get_ad_info($ad_id,$db);
}

// Got an image for this ad? Then get its size. We’ll use this
// to override anything the database claims the image is.
/*
if ( $check_these ) {
	foreach ( $check_these as $key => $val ) {
		if ( $promo_info[$key.'_image_url'] && is_file('..'.$promo_info[$key.'_image_url'])) {
			$real_size[$key] = getimagesize('..'.$promo_info[$key.'_image_url']);
		}
	}
}
*/

$slot_list = get_slots(null,'ad',$db);
if ( $slot_list ) {
	foreach ( $slot_list as $key => $val ) {
		$options_list[$key] = $val['min_width'].'&times'.$val['max_width'];
	}
}


$alert_output .= $fileops->check_or_make_dir('..'.$image_path);

$mobile_form = <<<EOL
		<label for="small_image_url">Mobile image</label>
		<input type="file" id="small_image_url" name="small_image_url" value=""/>

EOL;

$mobile_form = <<<EOL
		<label for="small_image_url">Mobile image</label>
		<input type="file" id="small_image_url" name="small_image_url" value=""/>

EOL;

$tablet_form = <<<EOL
		<label for="medium_image_url">Tablet image</label>
		<input type="file" id="medium_image_url" name="medium_image_url" value=""/>

EOL;

$widescreen_form = <<<EOL
		<label for="large_image_url">Widescreen image</label>
		<input type="file" id="large_image_url" name="large_image_url" value=""/>

EOL;

$destination_form = '<input type="text" id="destination" size="24" style="width:24rem" name="destination" value="http://"/>';

$code_form = '<textarea name="code" id="code" height="8" width="24" style="height:8rem;width:24rem"></textarea>';

$view->group_h2('Destination');
$view->group_instruction('Where this ad will take people when they tap its graphics.');
$view->group_contents($destination_form);
$content_output .= $view->format_group().'<hr />';

/*
$view->group_h2('Mobile-optimized');
$view->group_instruction('Graphic for this ad on smartphones.');
$view->group_contents($mobile_form);
$content_output .= $view->format_group().'<hr />';

$view->group_h2('Tablet-sized');
$view->group_instruction('Graphic for this ad on small screens and tablets.');
$view->group_contents($tablet_form);
$content_output .= $view->format_group().'<hr />';
*/

$view->group_h2('Image');
$view->group_instruction('Graphic that readers will tap.');
$view->group_contents($widescreen_form);
$content_output .= $view->format_group().'<hr />';

$view->group_h2('Custom code');
$view->group_instruction('Optional: If you have special HTML or JavaScript instead of graphics and a destination for this ad, paste it here.');
$view->group_contents($code_form);
$content_output .= $view->format_group();





/*****
 * Display
 */

$view->page_title('Create new promo');
$view->tooltype('ad');
$view->headline("Create new promo");

print( $view->open_view() );
print( $view->view_header() );

?>
<p>Promos are custom ads you create and upload yourself. All sizes below are optional — but you should upload at least a mobile-optimized graphic. <strong>Grawlix’s ad slots are based on mobile graphics first.</strong></p><hr/>

<?=$alert_output?>
<form accept-charset="UTF-8" action="ad.promo-create.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="grlx_xss_token" value="<?=$_SESSION['admin']?>"/>
<?=$content_output ?>
	<button class="btn primary new" name="submit" type="submit" value="create"><i></i>Create</button></p><br/>
</form>

<?php print($view->close_view()); ?>