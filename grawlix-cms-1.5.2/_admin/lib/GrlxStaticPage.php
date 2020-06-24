<?php

/**
 * Operations for static pages.
 *
 * Instantiate:
 * $static = new GrlxStaticPage;
 */

class GrlxStaticPage {

	protected $db;
	public    $pageList;

	/**
	 * Setup
	 */
	public function __construct($id=null) {
		global $db;
		$this->db = $db;
		$this->getArgs(func_get_args());
		if ( $id ) {
			$this-> pageID = $id;
		}
	}

	/**
	 * Pass in any arguments
	 *
	 * @param array $list - arguments from main script
	 */
	protected function getArgs($list=null) {
		$list = $list[0];
		if ( isset($list) && is_array($list) ) {
			foreach ( $list as $key=>$val ) {
				if ( property_exists($this, $key) ) {
					$this->{$key} = $val;
				}
			}
		}
	}

	/**
	 * Get one static page by ID
	 */
	public function getInfo($page_id=null) {
		if ( !$page_id ) {
			$page_id = $this->pageID;
		}
		if ( $page_id && $this->db ) {
			$cols = array(
				'sp.id',
				'sp.title',
				'sp.description',
				'sp.options',
				'sp.tone_id',
				'p.title AS menu_title',
				'p.url',
				'p.edit_path'
			);
			$result = $this->db
				->join('path p','sp.id = p.rel_id','INNER')
				->where('sp.id',$page_id)
				->where('rel_type','static')
				->getOne('static_page sp',$cols);
			$this->info = $result;
		}
	}

	/**
	 * Get list of all static pages
	 */
	public function getPageList() {
		$cols = array(
			'sp.id',
			'sp.title',
			'sp.description',
			'sp.tone_id',
			'sp.options',
			'p.edit_path'
		);
		$result = $this->db
			->join('path p','sp.id = p.rel_id','INNER')
			->where('rel_type','static')
			->orderBy('p.title','ASC')
			->get('static_page sp',null,$cols);
		$result = rekey_array($result,'id');
		$this->pageList = $result;
		return $this->pageList;
	}
}