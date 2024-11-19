<?php

/*

$fileops = new GrlxFileOps;

$fileops-> up_set_destination_folder('../comics/my-comic');
$success = $fileops-> up_process('file_change');

// Create the image DB record.
$new_image_id = $comic_image-> createImageRecord ( $web_path.'/'.$this_file );

// Create the page DB record.
$new_page_id = $comic_image-> createPageRecord ( $title, $sort_order, $book_id );

// Assign the image to the page.
if ( $new_image_id && $new_page_id ) {
	$new_assignment_id = $comic_image-> assignImageToPage($new_image_id,$new_page_id);
}


*/
class GrlxComicImage {
	protected $db;
	
	function __construct(){
		global $db;
		$this-> db = $db;
	}
	function createImageRecord($filename,$description=''){
		// Fallback in case we have no alt value (description, whatever).
		if ( !$description ) {
			$description = ''; //$filename;
		}
		$data = array (
			'url' => $filename,
			'description' => $description,
			'date_created' => $this-> db-> NOW()
		);
		$image_id = $this-> db-> insert('image_reference', $data);
		return $image_id;
	}

	function createPageRecord($title,$sort_order,$book_id,$marker_id=null,$date_publish=null){
		$data = array (
			'title' => $title,
			'sort_order' => $sort_order,
			'book_id' => $book_id,
			'date_created' => $this-> db-> NOW(),
			'date_publish' => ($date_publish)? $date_publish : $this-> db-> NOW()
		);
		if ( $marker_id ) {
			$data['marker_id'] = $marker_id;
		}
		$page_id = $this-> db-> insert('book_page', $data);
		return $page_id;
	}

	function assignImageToPage($image_id,$page_id){
		$data = array (
			'image_reference_id' => $image_id,
			'rel_id' => $page_id,
			'rel_type' => 'page',
			'date_created' => $this-> db-> NOW()
		);
		$id = $this-> db-> insert('image_match', $data);
		return $id;
	}

}
