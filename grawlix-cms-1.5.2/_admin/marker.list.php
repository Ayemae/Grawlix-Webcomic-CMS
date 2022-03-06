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
$marker = new GrlxMarker;


$var_list = array(
	'delete_id'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}




/*****
 * Updates
 */
 
if ( $delete_id ) {
	$doomed_marker = new GrlxMarker($delete_id);
	$success = $doomed_marker-> deleteMarker($delete_id, false);
	
	if ( $success ) {
		$alert_output = $message-> alert_dialog('Marker deleted.');
	}
}




/*****
 * Display
 */

function compareMarkers($a, $b) {
	if($a['book_id'] == $b['book_id']) { //compare pages
		return $a['startPage'] - $b['startPage'];
	} else {
		return $a['book_id'] - $b['book_id'];
	}
}

$marker_list = $marker_type-> getMarkers();
$markersFound = 0;
if( $marker_list && $marker_list['markers'] && count($marker_list['markers']) > 0) {
	//Fetch some extra data about the markers to help sort and display them
	foreach($marker_list['markers'] as $key => $val) {
		$marker->setID($val['id']);
		$marker->setup();
		if($marker->markerInfo) {
			$marker_list['markers'][$key]['book_id'] = $marker->markerInfo['book_id'];
			$marker_list['markers'][$key]['startPage'] = $marker->startPage;
			$marker_list['markers'][$key]['title'] = $marker->markerInfo['title'];
			$markersFound++;
		}
	}
	if($markersFound > 0) {
		usort($marker_list['markers'], 'compareMarkers');
		
		$heading_list[] = array(
			'value' => 'Level',
			'class' => null
		);
		$heading_list[] = array(
			'value' => 'Type',
			'class' => null
		);
		$heading_list[] = array(
			'value' => 'Title',
			'class' => null
		);
		$heading_list[] = array(
			'value' => 'Actions',
			'class' => null
		);
		
		$list-> headings($heading_list);
		$list-> draggable(false);
		$list-> row_class('chapter');
		
		$edit_link = new GrlxLinkStyle;
		$edit_link-> url('marker.edit.php');
		$edit_link-> title('Edit marker type meta.');
		$edit_link-> reveal(false);
		$edit_link-> action('edit');

		$delete_link = new GrlxLinkStyle;
		$delete_link-> url('marker.list.php');
		$delete_link-> title('Delete this marker.');
		$delete_link-> reveal(false);
		$delete_link-> action('delete');
		
		//Build the list of markers
		$list_items;
		$prev_book_id = -1;
		foreach($marker_list['markers'] as $key => $val) {
			if($val['book_id'] != $prev_book_id) {
				if($list_items && sizeof($list_items) > 0) {
					$list-> content($list_items);
					$marker_output = $list->format_headings();
					$marker_output .= $list->format_content();

					$view->group_contents($marker_output);
					$content_output .= $view->format_group();
				}
				if($val['book_id']) {
					$book = new GrlxComicBook($val['book_id']);
					$view->group_h2('<span>'.$book-> info['title'].'</span>');
					$view->group_instruction('');
				} else {
					$view->group_h2('Orphaned markers');
					$view->group_instruction('These markers have no associated pages, and should be deleted.');
				}
				$prev_book_id = $val['book_id'];
				$list_items = [];
			}
			$edit_link->query('marker_id='.$val['id']);
			$delete_link->query('delete_id='.$val['id']);
			$action_output = $edit_link->icon_link();
			$action_output .= $delete_link->icon_link();
			
			$title = $val['title'];
			// Emphasize top-tier markers with a <strong> element.
			if ( $val['marker_type_id'] == 1 ) {
				$title = '<strong>'.$title.'</strong>';
			}
			
			$link-> url('book.view.php?view_marker='.$val['id']);
			$link-> title('See the pages with this marker');
			$link-> tap($title);
			
			$list_items[] = array(
				'rank'=> $marker_list[$val['marker_type_id']]['rank'],
				'type'=> $marker_list[$val['marker_type_id']]['title'],
				'title'=> $link-> paint(),
				'action'=> $action_output
			);
		}
		if($list_items && sizeof($list_items) > 0) {
			$list-> content($list_items);
			$marker_output = $list->format_headings();
			$marker_output .= $list->format_content();
			
			$view->group_contents($marker_output);
			$content_output .= $view->format_group();
		}
	}
}
if($markersFound == 0) {
	$list_output .= 'I didn’t find any markers.';
}

$view->page_title('Marker list');
$view->tooltype('chapter');
$view->headline('Marker list');


$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $content_output;

print($output);

print( $view->close_view() );
