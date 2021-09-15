<?php

class GrlxMarker {

	protected $db;
	public $markerID;
	public $markerInfo;
	public $pageList;
	public $startPage;
	public $endPage;

	function __construct($markerID=null,$quick=false){
		global $db;
		$this-> db = $db;
		if ( $markerID && $quick ) {
			$this-> setID($markerID);
			$this-> getMarkerInfo();
			$this-> getMarkerThumbnail();
		}
		elseif ( $markerID ) {
			$this-> setID($markerID);
			$this-> setup();
		}
	}

	public function setup(){
		if ( $this-> markerID ) {
			$this-> getMarkerInfo();
			$this-> getPageRange();
			$this-> getPageIDs();
			$this-> getBookID();
			$this-> getMarkerThumbnail();
		}
	}

	public function setID($id){
		$this-> markerID = $id;
	}

	public function createMarker($title,$type_id,$page_id){

		$data = array (
			'title' => $title,
			'marker_type_id' => $type_id
		);
		if ( $new_desc ) {
			$data['description'] = $new_desc;
		}
		$id = $this-> db-> insert('marker', $data);

		return $id;
	}

	public function saveMarker($marker_id,$new_title,$type='',$new_desc=''){
		$data = array (
			'title' => $new_title,
		);
		if ( $type ) {
			$data['marker_type_id'] = $type;
		}
		if ( $new_desc ) {
			$data['description'] = $new_desc;
		}
		else{
			$data['description'] = null;			
		}

		$this-> db-> where('id',$marker_id);
		$success = $this-> db-> update('marker', $data);
		return $success;
	}

	// delete based on page ID, not primary ID
	public function deleteMarker($marker_id,$pages_too=false){

		$this-> db-> where('id', $marker_id);
		$success = $this-> db->delete('marker');

		$data = array (
			'marker_id' => null
		);
		$this-> db->where('marker_id',$marker_id);
		$success = $this-> db->update('book_page', $data);

		if ( $pages_too === true ) {
			$start = $this-> startPage;
			$end = $this-> endPage;

			if ( $start && $end ) {
				$this->db->where('sort_order', $start, '>=');
				$this->db->where('sort_order', $end, '<=');
				$result2 = $this->db->delete('book_page');

				$success = reset_page_order(1,$this-> db);
			}
		}
		return $result1[0];
	}

	function getMarkerInfo()
	{
		if ( $this-> markerID ) {
			$this-> db-> join('book_page bp', 'marker_id = m.id', 'LEFT');
			$this-> db-> where('m.id', $this-> markerID);
			$info = $this-> db-> get ('marker m', null, 'm.title,m.description,m.marker_type_id,bp.sort_order');
			$this-> markerInfo = $info[0];
		}
	}

	function getMarkerThumbnail(){
		if ( $this-> markerID ) {
			$this-> db-> join('image_match im', 'im.image_reference_id = ir.id', 'LEFT');
			$this-> db-> where('im.rel_id', $this-> markerID);
			$this-> db-> where('im.rel_type', 'marker');
			$this-> db-> orderBy('im.date_created', 'DESC');
			$info = $this-> db-> get ('image_reference ir', null, 'ir.id AS id, url, description');

			$this-> thumbInfo = $info[0];
		}
	}

	function getPageRange($sort_order=null){

		if ( $this->markerInfo ) {
			$sort_order ? $sort_order : $sort_order = $this->markerInfo['sort_order'];
			if ( $sort_order ) {
				$this-> db-> where ('sort_order', $sort_order, '>=');
				$this-> db-> where ('book_id', $this->markerInfo['book_id'] );
				$this-> db-> orderBy ('sort_order','ASC');
				$result = $this-> db-> get ('book_page',null,'id,sort_order');

				if ( $result ) {
					$result = reset($result);
					$this-> startPage = $result['sort_order'];
				}
				else {
					$this-> startPage = $sort_order;
				}
				$this-> db-> where ('sort_order', $sort_order, '>');
				$this-> db-> where ('marker_id', '0', '>');
				$this-> db-> orderBy ('sort_order','ASC');
				$result = $this-> db-> get ('book_page',1,'id,sort_order');
				if ( $result ) {
					$this-> endPage = $result[0]['sort_order'] - 1;
				}
				else {
					$result = $this-> db-> get ('book_page',1,'MAX(sort_order) AS endpage');
					if ( $result ) {
						$this-> endPage = $result[0]['endpage'];
					}
				}
			}
		}
	}

	function getBookID() {
		if ( $this-> pageList ) {
			$page_info = reset($this-> pageList);
			$this-> markerInfo['book_id'] = $page_info['book_id'];
		}
	}

	function getPageIDs() {

		if ( !$this->startPage || !$this->endPage ) {
			$this-> getPageRange();
		}
		if ( $this->startPage && $this->endPage ) {
			$this-> db-> where ('book_id',$this->markerInfo['book_id']);
			$this-> db-> where ("sort_order >= $this->startPage");
			$this-> db-> where ("sort_order <= $this->endPage");
			$this-> db-> orderBy ('sort_order','ASC');
			$page_list = $this-> db-> get ('book_page',null,'id,book_id,title,tone_id,date_publish,sort_order');

			$page_list = rekey_array($page_list,'id');

			$this-> pageList = $page_list;
		}
	}


	function shiftPages($qty='',$order=''){
		if ( $qty && $order ) {

			$data = array (
				'sort_order' => 'sort_order + '.$qty
			);
			$this-> db->where('sort_order','>='.$order);
			$success = $this-> db-> update('book_page', $data);

		}
		return $result[0];
	}
	function movePage($id='',$sort_order=''){
		if ( $id && $sort_order ) {

			$data = array (
				'sort_order' => $sort_order
			);
			$this-> db->where('id',$id);
			$id = $this-> db->update('book_page', $data);

		}
		return $result[0];
	}
}
