<?php

/*****
 * Setup
 */

require_once('panl.init.php');

$fileops = new GrlxFileOps;
$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;
$list = new GrlxList;
$list->draggable(false);
$image_obj = new GrlxImage;

$view-> yah = 10;

// Default value
$page_title = 'Ad editor';

$var_list = array('title','ad_id', 'tap_url', 'small_image_url', 'medium_image_url', 'large_image_url','msg');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}
$code = $_POST['code'];


// No ad selected? Send ’em back to the list.
if ( !$ad_id || !is_numeric($ad_id) ) {
	header('location:ad.list.php');
}

// Folder in which we keep ad images.
$image_path = $milieu_list['directory']['value'].'/assets/images/ads';

// List of status levels.
/*
$priority_list = array(
	'1' => 'High', // 1 = priority
	'0' => 'Normal', // 0 = nothing special
	'-1' => 'Hidden' // below zero = out of the loop.
);
*/

// Official IAB sizes. Outdated ideas, but still used a lot.
/*
$size_list = array (
	'117,30' => 'Button 117',
	'468,60' => 'Full Banner',
	'728,90' => 'Leaderboard',
//	'336,280' => 'Square 336',
//	'300,250' => 'Square 300',
	'250,250' => 'Square',
	'160,600' => 'Skyscraper',
//	'120,600' => 'Skyscraper 120',
//	'120,240' => 'Small Skyscraper',
//	'240,400' => 'Fat Skyscraper',
	'234,60' => 'Half Banner',
//	'180,150' => 'Rectangle 180',
	'125,125' => 'Square Button',
	'120,90' => 'Button 90',
	'120,60' => 'Button 60',
	'88,31' => 'Button 88&times;31'
);
*/


/*****
 * Updates
 */

$check_these = array(
//	'small' => 'small_image_url',
//	'medium' => 'medium_image_url',
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
if ( $_POST && $ad_id ) {
	$data = array (
		'title' => $title,
		'code' => $code,
		'tap_url' => $tap_url
	);
	if ( $check_these ) {
		foreach ( $check_these as $key => $val ) {
			if ( is_array($upload_sizes[$key]) ) {
				$data[$key.'_width'] = $upload_sizes[$key][0];
				$data[$key.'_height'] = $upload_sizes[$key][1];
			}
		}
	}


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
	$db->where('id',$ad_id);
	$success = $db->update('ad_reference', $data);

	$link = new GrlxLink('grlx ad list');

	if ( $success == 1) {
		$alert = new GrlxAlert;
		$alert_output = $alert-> success_dialog('Ad info saved. Make changes below or '.$link-> paint().'.');
	}
}



/*****
 * Display logic
 */

if ( $ad_id ) {
	$promo_info = get_ad_info($ad_id,$db);
}
if ( $ad_id && $msg == 'created' ) {
	$success_message = <<<EOL
Ad created.
<ul>
	<li>Make changes below</li>
	<li><a href="ad.list.php">Add this ad to a slot in your site’s template</a></li>
	<li><a href="ad.promo-create.php">Make another new ad</a></li>
</ul>

EOL;
	$alert_output .= $message-> success_dialog($success_message);
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



if ( $promo_info ) {

	$image_obj-> src = $promo_info['small_image_url'];
	$image_obj-> alt = $promo_info['small_image_url'];
	$small_img = $image_obj-> paint();

	$image_obj-> src = $promo_info['medium_image_url'];
	$image_obj-> alt = $promo_info['medium_image_url'];
	$medium_img = $image_obj-> paint();

	$image_obj-> src = $promo_info['large_image_url'];
	$image_obj-> alt = $promo_info['large_image_url'];
	$large_img = $image_obj-> paint();

}

$alert_output .= $fileops->check_or_make_dir('..'.$image_path);

/*
$mobile_content = <<<EOL
	$small_img
	<label for="small_image_url">Replace image</label>
	<input type="file" id="small_image_url" name="small_image_url" value=""/>

EOL;

$tablet_content = <<<EOL
	$medium_img
	<label for="medium_image_url">Replace image</label>
	<input type="file" id="medium_image_url" name="medium_image_url" value=""/>

EOL;
*/

$title_content = <<<EOL
	<label for="title">Site title</label>
	<input type="text" id="title" name="title" value="$promo_info[title]" style="width:10rem"/><br/>

EOL;

$widescreen_content = <<<EOL
	$large_img

	<p><br/>
		<label for="large_image_url">Replace image</label>
		<input type="file" id="large_image_url" name="large_image_url" value=""/>
	</p>

EOL;

$destination_content = <<<EOL
	<label for="tap_url">Destination URL</label>
	<input type="text" id="tap_url" name="tap_url" value="$promo_info[tap_url]"/><br/>

EOL;

$code_content = <<<EOL
	<label for="code">Code</label>
	<textarea name="code" id="code" rows="10">$promo_info[code]</textarea>

EOL;

/*****
 * Display
 */

// Group
/*
$view->group_h2('Mobile image');
$view->group_instruction('Image that will appear on small screens, e.g. smartphones.');
$view->group_contents($mobile_content);
$content_output .= $view->format_group().'<hr />';

$view->group_h2('Tablet image');
$view->group_instruction('Image will appear on smallish screens.');
$view->group_contents($tablet_content);
$content_output .= $view->format_group().'<hr />';
*/

$view->group_h2('Title');
$view->group_instruction('Name of the website.');
$view->group_contents($title_content);
$content_output .= $view->format_group().'<hr />';

$view->group_h2('Image');
$view->group_instruction('Image that readers will see.');
$view->group_contents($widescreen_content);
$content_output .= $view->format_group().'<hr />';

$view->group_h2('Destination');
$view->group_instruction('Where the ad, when tapped, will take readers.');
$view->group_contents($destination_content);
$content_output .= $view->format_group().'<hr />';

$view->group_h2('Code');
$view->group_instruction('Optional JS or HTML to use in lieu of images and destination. <strong>Entering code will override the image and link above.</strong>');
$view->group_contents($code_content);
$content_output .= $view->format_group();


$view->page_title('Ad editor');
$view->tooltype('ad');
$view->headline('Promo <span>'.$promo_info['title'].'</span>');


print( $view->open_view() );
print( $view->view_header() );
?>

<div id="edit_modal" class="reveal-modal" data-reveal></div>

<?=$alert_output?>
			<form accept-charset="UTF-8" action="ad.promo-edit.php" method="post" enctype="multipart/form-data">
				<input type="hidden" name="grlx_xss_token" value="<?=$_SESSION['admin']?>"/>
<?=$content_output ?>

				<input type="hidden" name="ad_id" value="<?=$ad_id ?>"/>
				<p><button class="btn primary right" id="submit" name="edit-submit" type="submit" value="save"/><i></i>Save</button></p>
			</form>

<?php print($view->close_view()); ?>