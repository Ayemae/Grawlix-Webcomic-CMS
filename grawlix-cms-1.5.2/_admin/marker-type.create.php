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
$marker_type = new GrlxMarkerType();


$var_list = array(
	'next_rank','new_title'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}





/*****
 * Updates
 */

if ( $_POST && $new_title ) {
	$next_rank ? $next_rank : $next_rank = 1;
	$new_id = $marker_type-> createMarkerType($new_title,$next_rank);
	if ( $new_id ) {
		header('location:marker-type.list.php');
		die();
	}
	else {
		$alert_output = $message-> alert_dialog('I couldn’t create the new marker.');
	}
}
elseif ( $_POST && !$new_title ) {
	$alert_output = $message-> alert_dialog('Hmm, I didn’t see a title. Did you give this new marker type a name?');
}




/*****
 * Display logic
 */

$next_rank = $db-> get ('marker_type',null,'MAX(rank)+1 AS next_rank');
$next_rank = $next_rank[0]['next_rank'];


$view->page_title('Create marker type');
$view->tooltype('chap');
$view->headline('Create marker type');


$content_output .= '<form accept-charset="UTF-8" action="marker-type.create.php" method="post">'."\n";
$content_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
$content_output .= '	<input type="hidden" name="marker_type_id" value="'.$marker_type_id.'"/>'."\n";
$content_output .= '	<label for="marker_type_id">New type title:</label>'."\n";
$content_output .= '	<input type="text" name="new_title" size="12" style="width:12rem" value="'.$marker_type-> markerTypeInfo['title'].'"/>'."\n";
$content_output .= '	<button class="btn primary save" name="submit" type="submit" value="save"><i></i>Save</button>'."\n";
$content_output .= '	<input type="hidden" name="next_rank" value="'.$next_rank.'"/>'."\n";
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
