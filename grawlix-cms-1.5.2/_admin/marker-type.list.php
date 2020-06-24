<?php

/*****
 * Setup
 */

require_once('panl.init.php');

$view = new GrlxView;
$message = new GrlxAlert;
$link = new GrlxLinkStyle;
$list = new GrlxList;
$form = new GrlxForm;
$marker_type = new GrlxMarkerType;


$var_list = array(
	'marker_type_id','new_marker_name','delete_id'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

// Folder in which we keep ad images.
// $image_path = $milieu_list['directory']['value'].'/assets/images';






/*****
 * Updates
 */

if ( $_GET && $delete_id ) {
	$success = $marker_type-> deleteMarkerType($delete_id);
	$marker_type-> resetMarkerTypes();
}
if ( $success && $delete_id ) {
	$alert_output = $message-> alert_dialog('Marker type deleted.');
}




/*****
 * Display
 */

$marker_type_list = $marker_type-> getMarkerTypeList();



if ( $marker_type_list ) {

	$heading_list[] = array(
		'value' => 'Level',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Title',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Total markers',
		'class' => null
	);
	$heading_list[] = array(
		'value' => 'Actions',
		'class' => null
	);

	$edit_link = new GrlxLinkStyle;
	$edit_link-> url('marker-type.edit.php');
	$edit_link-> title('Edit marker type meta.');
	$edit_link-> reveal(false);
	$edit_link-> action('edit');

	$delete_link = new GrlxLinkStyle;
	$delete_link-> url('marker-type.list.php');
	$delete_link-> title('Delete this marker type.');
	$delete_link-> reveal(false);
	$delete_link-> action('delete');
	
	foreach ( $marker_type_list as $key => $val ) {

		$edit_link->query("marker_type_id=$val[id]");
		$delete_link->query("delete_id=$val[id]");

		$action_output  = $delete_link->icon_link();
		$action_output .= $edit_link->icon_link();

		$list_items[$key] = array(
			'select'=> $val['rank'],
			'title'=> $val['title'],
			'tally'=> $val['tally'],
			'action'=> $action_output
		);
	}

	$list-> draggable(false);
	$list-> row_class('chapter');
	$list-> headings($heading_list);
	$list-> content($list_items);

	$list_output  = $list->format_headings();
	$list_output .= $list->format_content();
}
else {
	$list_output .= 'I didn’t find any types of marker.';
}


$link-> url('marker-type.create.php');
$link-> tap('Create');
$create_output = $link-> button_primary('new');


$view->group_h2('Organization');
$view->group_instruction('Markers denote the beginnings of sections, like chapters, scenes and supplemental pages. Tap to edit each type.');
$view->group_contents($list_output);
$content_output .= $view->format_group().'<hr />';

$view->group_h2('Create');
$view->group_instruction('Make a new marker type.');
$view->group_contents($create_output);
$content_output .= $view->format_group();


$view->page_title('Marker types: '.$book-> info['title']);
$view->tooltype('chapter');
$view->headline('Marker types');


$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $content_output;

print($output);

print( $view->close_view() );
