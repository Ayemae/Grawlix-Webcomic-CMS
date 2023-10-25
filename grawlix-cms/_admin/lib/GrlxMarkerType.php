<?php

class GrlxMarkerType {
	protected $db;
	public    $markerTypeID;
	public    $markerTypeInfo;

	function __construct($markerTypeID=null){
		global $db;
		$this-> db = $db;

		if ( !empty($markerTypeID) ) {
			$this-> markerTypeID = $markerTypeID;
			$this-> getMarkerTypeInfo();
		}
	}

	function createMarkerType($title,$rank){
		$data = array (
			'title' => $title,
			'rank' => $rank
		);
		$id = $this-> db-> insert('marker_type', $data);
		return $id;
	}

	function saveMarkerType ( $marker_type_id, $new_title ){
		$data = array (
			'title' => $new_title,
		);

		$this-> db-> where('id',$marker_type_id);
		$success = $this-> db-> update('marker_type', $data);
		return $success;
	}

	function deleteMarkerType($marker_type_id){
		// Get every marker that uses this type

		$this-> db-> where ('marker_type_id', $marker_type_id);
		$marker_id_list = $this-> db-> get ('marker',null,'id');

		// Remove doomed markers from the comic book pages.
		if ( !empty($marker_id_list) ) {
			$doomed_marker = new GrlxMarker();
			foreach ( $marker_id_list as $key => $val ) {
				$doomed_marker-> deleteMarker($val['id'],false);
			}
		}

		$this-> db-> where('id',$marker_type_id);
		$success1 = $this-> db-> delete('marker_type');

		return $success1;
	}

	function getMarkerTypeInfo(){
		if ( !empty($this-> markerTypeID) ) {
			$this-> db-> where ('`id`', $this-> markerTypeID);
			$info = $this-> db-> get ('marker_type',null,'`id`,`title`');
			$this-> markerTypeInfo = $info[0];
		}
	}

	function getMarkerTypeList(){
		$this-> db-> where ('`id`', $this-> markerTypeID);
		$this-> db-> orderBy('`rank`','ASC');
		$result = $this-> db-> get ('marker_type',null,'`id`,`title`,`rank`');
		$result = rekey_array($result,'id');
		if ( !empty($result) ) {
			foreach ( $result as $key => $val ) {
				$this-> db-> where ('marker_type_id', $val['id']);
				$tally = $this-> db-> getOne('marker', 'COUNT(`id`) AS `tally`');
				$result[$key]['tally'] = $tally['tally'];
			}
		}
		
		return $result;
	}
	
	function getMarkers(){
		$this-> db-> where ('`id`', $this-> markerTypeID);
		$this-> db-> orderBy('`rank`','ASC');
		$result = $this-> db-> get ('marker_type',null,'`id`,`title`,`rank`');
		$result = rekey_array($result,'id');
		if ( !empty($result) ) {
			foreach ( $result as $key => $val ) {
				$this-> db-> where ('marker_type_id', $val['id']);
				$markers = $this-> db-> get('marker', null, '`id`,marker_type_id');
				if( !empty($result['markers']) ) {
					$result['markers'] = array_merge($result['markers'], $markers);
				} else {
					$result['markers'] = $markers;
				}
			}
		}
		
		return $result;
	}

	function resetMarkerTypes(){
		$this-> db-> orderBy ('`rank`','ASC');
		$this-> db-> orderBy ('`title`','ASC');
		$result = $this-> db-> get ('marker_type',null,'`rank`,`id`');
		$i = 1;
		if ( !empty($result) ) {
			foreach ( $result as $key => $val ) {
				$data = array (
					'rank' => $i
				);
				$this-> db->where('`id`',$val['id']);
				$id = $this-> db->update('marker_type', $data);
				$i++;
			}
		}
	}
}

