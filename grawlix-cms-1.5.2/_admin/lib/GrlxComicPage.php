<?php

class GrlxComicPage {
	function __construct($pageID=null){
		$this-> pageID = $pageID;
		global $db;
		$this-> db = $db;
		if ( $this-> pageID ) {
			$this-> getPageInfo();
			$this-> getPageImages();
		}
	}
	function createPage($data){
		$page_id = $db -> insert('book_page', $data);
		return $page_id;
	}

	function deletePage($doomed_id=null){
		if ( !$doomed_id ) {
			$doomed_id = $this-> pageID;
		}

		// Delete the images and their directories.
		if ( $this-> imageList ) {
			foreach ( $this-> imageList as $key => $val ) {
				if ( $val['url'] && substr($val['url'],0,4) == 'http') { //probably the original test page with an external link image
					//No file to delete here, it's off-site!
					
					// Zap all references in the database.
					$this-> db-> where('id',$val['image_reference_id']);
					$success = $this-> db-> delete('image_reference');

					$this-> db-> where('image_reference_id',$val['image_reference_id']);
					$success = $this-> db-> delete('image_match');
				} else if ( $val['url'] && is_file('..'.$val['url'])) {
					$url_parts = explode('/',$val['url']);
					array_pop($url_parts);
					$url_parts = implode('/', $url_parts);
					unlink('..'.$val['url']);
					@rmdir('..'.$url_parts);

					// Oh, and zap all references in the database.
					$this-> db-> where('id',$val['image_reference_id']);
					$success = $this-> db-> delete('image_reference');

					$this-> db-> where('image_reference_id',$val['image_reference_id']);
					$success = $this-> db-> delete('image_match');
				}
			}
		}

		// Delete the page itself.
		$this-> db-> where('id',$doomed_id);
		$success = $this-> db-> delete('book_page');

		return $success;
	}

	function getPageInfo(){
		$this-> db-> where ('id', $this-> pageID);
		$info = $this-> db-> get ('book_page',1,'book_id, title, sort_order, options, blog_title, blog_post, transcript, date_publish');

		if ( $info ) {
			$this-> pageInfo = $info[0];
		}
	}


function getPageImages(){
	$cols = array(
		'url',
		'description',
		'ir.id AS image_reference_id',
		'im.sort_order',
		'im.id'
	);
	$result = $this->db
		->join('image_reference ir','image_reference_id = ir.id')
		->where('rel_id',$this-> pageID)
		->where('im.rel_type', 'page')
		->orderBy('im.sort_order','ASC')
		->orderBy('im.id','ASC')
		->get('image_match im',null,$cols);

	if ( $result )
	{
		$result = rekey_array($result,'id');
		$this-> imageList = $result;
	}
	}
}
