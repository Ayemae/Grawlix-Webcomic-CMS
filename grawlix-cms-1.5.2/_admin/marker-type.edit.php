<?php

/*****
 * Setup
 */

require_once('panl.init.php');

$view = new GrlxView;
$modal = new GrlxForm_Modal;
$message = new GrlxAlert;
$link = new GrlxLinkStyle;
$list = new GrlxList;


$var_list = array(
	'marker_type_id','new_title'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}


if ( $marker_type_id ) {
	$marker_type = new GrlxMarkerType($marker_type_id);
}
else {
	header('location:marker-type.list.php');
	die();
}


/*****
 * Updates
 */

if ( $marker_type_id && $new_title ) {
	$success = $marker_type-> saveMarkerType ( $marker_type_id, $new_title );
	$marker_type = new GrlxMarkerType($marker_type_id);
}

if ( $success ) {
	$link-> url('marker-type.list.php');
	$link-> tap('Return to the type list');
	$alert_output = $message-> success_dialog('Marker type saved. '.$link-> paint().'.');
}


/*****
 * Display logic
 */

$view->page_title('Marker type: '.$marker_type-> markerTypeInfo['title']);
$view->tooltype('chap');
$view->headline('Marker type <span>'.$marker_type-> markerTypeInfo['title'].'</span>');


$content_output .= '<form accept-charset="UTF-8" action="marker-type.edit.php" method="post">'."\n";
$content_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
$content_output .= '	<input type="hidden" name="marker_type_id" value="'.$marker_type_id.'"/>'."\n";
$content_output .= '	<label for="marker_type_id">This marker type is called:</label>'."\n";
$content_output .= '	<input type="text" name="new_title" size="12" style="width:12rem" value="'.$marker_type-> markerTypeInfo['title'].'"/>'."\n";
$content_output .= '	<button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button>'."\n";
$content_output .= '	<input type="hidden" name="marker_type_id" value="'.$marker_type_id.'"/>'."\n";
$content_output .= '</form>'."\n";



/*****
 * Display
 */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $content_output;
print($output);




print( $view->close_view() );
