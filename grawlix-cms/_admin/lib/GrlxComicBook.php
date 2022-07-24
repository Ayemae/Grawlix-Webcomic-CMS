<?php

class GrlxComicBook {

	protected $db;
	public $bookID;
	public $info;
	public $pageList;
	public $markerList;

	function __construct($bookID=null){
		global $db;
		$this-> db = $db;

		if ( $bookID ) {
			$this-> bookID = $bookID;
		}
		else {
			$this-> getFirstBook();
		}
		$this-> getInfo();
/*
		$this-> getPages();
		$this-> getMarkers();
		$this-> getLastPage();
		$this-> figureMarkerEnds();
*/
	}

	function getPages($start=0,$limit=99999){

		$this-> db-> where ('book_id', $this-> bookID);
		$this-> db-> orderBy ('sort_order','ASC');
		$list = $this-> db-> get ('book_page',array($start,$limit),'id,title,sort_order,marker_id');
		$list = rekey_array($list,'sort_order');
		$this-> pageList = $list;
	}

	/**
	 * Get markers in order for the selected book only
	 */
	public function getMarkerList() {
		$cols = array(
			'bp.id AS page_id',
			'bp.tone_id',
			'bp.marker_id',
			'm.title',
			'mt.title AS type'
		);
		$result = $this->db
			->join('marker m','bp.marker_id = m.id','INNER')
			->join('marker_type mt','m.marker_type_id = mt.id','INNER')
			->where('bp.book_id',$this->bookID)
			->orderBy('bp.sort_order','ASC')
			->get('book_page bp',null,$cols);
		$result = rekey_array($result,'marker_id');
		$this->markerList = $result;
	}

	function getMarkers(){
		$cols = array(
			'm.id',
			'm.title',
			'm.marker_type_id'
		);
		$result = $this->db
			->join('marker m','marker_id = m.id')
			->orderBy('bp.sort_order','ASC')
			->where('bp.book_id',$this->bookID)
			->get('book_page bp',null,$cols);
		$this-> markerList = rekey_array($result,'id');
	}

	function figureMarkerEnds(){
		if ( $this-> markerList ) {
			foreach ( $this-> markerList as $key => $val ) {
				$this_sort_order = $val['first_page'];
				if ( isset($last_key) ) {
					$this-> markerList[$last_key]['last_page'] = $this_sort_order - 1;
				}
				$last_key = $key;
			}
			$this-> markerList[$key]['last_page'] = $this-> lastPage['sort_order'];
		}
	}

	function getLastPage(){
		$this-> db-> where ('book_id', $this-> bookID);
		$this-> db-> orderBy ('sort_order','DESC');
		$result = $this-> db-> get ('book_page',null,'id,title,sort_order');
		$result = rekey_array($result,'sort_order');
		$this-> lastPage = reset($result);
	}

	function getFirstBook(){
		$this-> db-> orderBy ('sort_order','ASC');
		$result = $this-> db-> getOne ('book','title,id');
		if ( $result ) {
			$this-> bookID = $result['id'];
		}
		else {
			die('No books in the database.');
		}
	}

	public function getInfo() {
		$cols = array(
			'b.id',
			'b.title',
			'b.description',
			'b.tone_id',
			'b.sort_order',
			'b.publish_frequency',
			'b.options',
			'p.url'
		);
		$this->db->join('path p','b.id = p.rel_id','INNER');
		$this->db->where('p.rel_type','book');
		$this->db->where('b.id',$this->bookID);
		$this->db->where('p.url','/','<>');
		$this->info = $this->db->getOne('book b',$cols);
	}
}
