<?php

include('panl.init.php');

// Setup the paths.
$temp_file = $_FILES['file']['tmp_name'];
$file_name = $_FILES['file']['name'];

// Get the file’s human-readable name.
$human_name = basename($file_name);
$human_name = str_replace(' ','-',$file_name);
$human_name = clean_text( $file_name );
$human_name = str_replace('_', ' ', $file_name);
$human_name_set = explode('.',$human_name);
array_pop($human_name_set);
$human_name = implode('.',$human_name_set);
$human_name = ucfirst($human_name);


// Upload the file.
if ( is_writable('../'.DIR_COMICS_IMG)) {
	$serial = date('YmdHis').substr(microtime(),2,6);
	$new_folder = DIR_COMICS_IMG.'/'.$serial;
	mkdir('..'.$new_folder);
	$success = move_uploaded_file($temp_file,'..'.$new_folder.'/'.$file_name);
	$web_file_path = $new_folder.'/'.$file_name;
}


// Add it to the database.
if ( $success && $web_file_path ) {
	$data = array (
		'url' => $web_file_path,
		'description' => $human_name,
		'date_created' => $db-> NOW()
	);
	$image_id = $db->insert('image_reference', $data);
}


// What’s the last page sort_order?
if ( $image_id ) {
//	$db_new-> where ('book_id', $book_id);
//	$db_new-> orderBy ('sort_order','DESC');
	$sort_order = $db-> get ('book_page',null,'MAX(sort_order) AS latest');
	if ( $sort_order ) {
//		$book_id = $sort_order[0]['book_id']; // TO DO: Make book ID dynamic … from what source?
		$book_id ? $book_id : $book_id = 1;
		$sort_order = $sort_order[0]['latest'];
	}
}



// Create a comic page.
if ( $sort_order ) {
	$data = array (
		'title' => $human_name,
		'sort_order' => $sort_order + 1,
		'book_id' => $book_id,
		'date_created' => $db-> NOW(),
		'date_publish' => $db-> NOW()
	);
	$page_id = $db->insert('book_page', $data);
}



// Put ’em together.
if ( $image_id && $page_id ) {
	$data = array (
		'image_reference_id' => $image_id,
		'rel_id' => $page_id,
		'rel_type' => 'page',
		'date_created' => $db-> NOW()
	);
	$id = $db->insert('image_match', $data);
}



