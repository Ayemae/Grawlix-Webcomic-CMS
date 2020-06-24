<?php

/* ! ------ Setup */

include('panl.init.php');

$view = new GrlxView;
$form = new GrlxForm;
$message = new GrlxAlert;
$select_list = new GrlxSelectList;
$list = new GrlxList;

// $view-> yah = 2;

$upload_xml_path = '../assets/data';
$xml_source_filename = '/wp-import.xml';
$upload_image_path = '../import/uploads';
$got_xml = FALSE;
$user_confirmed = FALSE;
$completed = FALSE;

$var_list = array('book_id','user_confirmed','sort_order_list','new_book_name','reset');
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val);
	}
}

// ! Prelim checks

if ( !is_dir($upload_image_path))
{
	$alert_output .= $message->alert_dialog('I can’t find the /import/uploads folder. Copy the “uploads” folder from your WordPress site (look in wp-content) into the “import” folder of your Grawlix CMS site, and try again.');
}

if ( !is_dir($upload_xml_path))
{
	$alert_output .= $message->alert_dialog('I can’t find the /assets/data folder. Please create it and make sure it has write permissions.');
}
elseif ( !is_writable($upload_xml_path))
{
	$alert_output .= $message->alert_dialog('I can’t write to the /assets/data folder. Please check its permissions and try again.');
}

if ( !is_writable('../'.DIR_COMICS_IMG) ) {
	$alert_output .= $message-> alert_dialog('I can’t write to the '.DIR_COMICS_IMG.' directory. Looks like a permissions problem.');
}



// ! Delete the existing XML file
if ( $reset )
{
	if (is_file($upload_xml_path.$xml_source_filename))
	{
		unlink($upload_xml_path.$xml_source_filename);
		$alert_output .= $message->success_dialog('XML file deleted.');
	}
}

// ! Upload the XML file

if ($_FILES)
{
	if ($_FILES['file']['type'][0] != 'text/xml')
	{
		$alert_output .= $message->alert_dialog('Please upload an exported XML file from WordPress.');
	}
	else
	{
		$success1 = move_uploaded_file($_FILES['file']['tmp_name'][0], $upload_xml_path.$xml_source_filename);
	}
}

if ($success1)
{
	$alert_output .= $message->success_dialog('XML uploaded successfully.');
	$got_xml = TRUE;
}

// ! ------ Interpret the XML

if (is_file($upload_xml_path.$xml_source_filename))
{
	$source_data = file_get_contents($upload_xml_path.$xml_source_filename);
	$source_data = str_replace('wp:', '', $source_data);
	$source_data = str_replace(':encoded', '', $source_data);
	$xml = simplexml_load_string($source_data);
}

// ! Loop through the XML items

if ( $xml && $xml->channel->item )
{
	foreach ( $xml->channel->item as $i => $obj )
	{
		$post_type = (string)$obj->post_type;
		$post_id = (string)$obj->post_id;
		$category = trim((string)$obj->category);

		if ($category == '' || strlen($category) == 0)
		{
			$category = '<em>(unknown)</em>';
		}

		// ! Handle comic pages
		if ($post_type == 'comic')
		{

			$comic_page_list[$post_id]['post_id'] = (string)$obj->post_id;
			$comic_page_list[$post_id]['title'] = (string)$obj->title;
			$comic_page_list[$post_id]['content'] = (string)$obj->content;
			$comic_page_list[$post_id]['post_date'] = (string)$obj->post_date;
			$comic_page_list[$post_id]['chapter'] = $category;

			foreach ($obj->children() as $key => $val)
			{
				if ($key == 'postmeta')
				{
					$metakey = (string)$val->meta_key;
					if ((string)$val->meta_key == '_thumbnail_id')
					{
						$thumbnail_id = (string)$val->meta_value;
						$comic_page_list[$post_id]['thumbnail_id'] = $thumbnail_id;
					}
				}
			}
		}

		// ! Handle attachments

		if ($post_type == 'attachment')
		{
			if ( $obj->postmeta )
			{
				foreach ( $obj->postmeta as $key => $val )
				{
					if ((string)$val->meta_key == '_wp_attached_file')
					{
						$image_list[$post_id]['image'] = (string)$val->meta_value;
					}
				}
			}
		}
	}
}


// ! Put the images and pages together.

if ( $comic_page_list && $image_list )
{
	foreach ( $comic_page_list as $key => $val )
	{
		$thumbnail_id = $val['thumbnail_id'];
		$image_info = $image_list[$thumbnail_id];
		$comic_page_list[$key]['image'] = $image_info['image'];
	}
}

// ! Organize pages into chapters

if ( $comic_page_list )
{
	foreach ( $comic_page_list as $key => $val )
	{
		$chapter = $val['chapter'];
		$chapter_list[$chapter][$key] = $val;
	}
}


/* ! ------ Actually import it */

// ! Create new markers

if ( $chapter_list && $user_confirmed == 1 )
{

	// ! Make a new book, if necessary
	if ($new_book_name && $new_book_name != '')
	{
		// Grab the last book’s settings
		$db->orderBy('title,sort_order','DESC');
		$book_info = $db->getOne('book', 'options,sort_order');

		// Create the new book
		$data = array (
			'title' => $new_book_name,
			'options' => $book_info['options'],
			'publish_frequency' => 'occasionally',
			'sort_order' => $book_info['sort_order']+1,
			'date_created' => $db->now()
		);
		$book_id = $db->insert('book',$data);

		// Add it to the paths table
		$data = array (
			'title' => $new_book_name,
			'url' => '/comic'.$book_id,
			'rel_id' => $book_id,
			'rel_type' => 'book',
			'in_menu' => 1,
			'edit_path' => 1
		);
		$book_path_id = $db->insert('path',$data);

		// Give it an archive page
		$data = array (
			'title' => $new_book_name,
			'url' => '/comic'.$book_id.'/archive',
			'rel_id' => $book_id,
			'rel_type' => 'archive',
			'in_menu' => 1,
			'edit_path' => 1
		);
		$archive_path_id = $db->insert('path',$data);
	}

	// Make sure we have a book selected
	if (!$book_id || $book_id == '')
	{
		$db->orderBy('id','ASC');
		$book_id = $db->getOne('book','id');
		$book_id = $book_id['id'];
	}

	// Clear out the old book
	if ($book_id && !$new_book_name)
	{
		$db->where('book_id',$book_id);
		$dead_list = $db->get('book_page',NULL,'id,marker_id');
		if ( $dead_list )
		{
			foreach ( $dead_list as $key => $val )
			{

				// Delete its markers
				$db->where('id',$val['marker_id']);
				$db->delete('marker');

				// Delete its pages’ image matches
				$db->where('rel_id',$val['id']);
				$db->where('rel_type','page');
				$db->delete('image_match');

				// Delete its markers’ image matches
				$db->where('rel_id',$val['marker_id']);
				$db->where('rel_type','marker');
				$db->delete('image_match');
			}
		}

		// Delete its pages
		$db->where('book_id',$book_id);
		$db->delete('book_page');

	}

	// ! Create pages per marker

	foreach ( $chapter_list as $chapter_name => $page_list )
	{
		$data = array(
			'title' => $chapter_name,
			'marker_type_id' => '1' // HARDCODED for testing
		);
		$new_marker_id = $db->insert('marker',$data);

		if ( $page_list )
		{
			$first_page = TRUE; // Reset (track if this is the first page for adding markers)
			foreach ( $page_list as $page_id => $page_info )
			{
				$current_sort_order = $sort_order_list[$page_id];

				$data = array (
					'title' => $page_info['title'],
					'book_id' => $book_id,
					'sort_order' => $current_sort_order,
					'date_created' => $db->now(),
					'date_publish' => $page_info['post_date'],
					'blog_title' => $page_info['title'],
					'blog_post' => $page_info['content']
				);
				if ($first_page === TRUE)
				{
					$data['marker_id'] = $new_marker_id;
					$first_page = FALSE;
				}

				$new_page_id = $db->insert('book_page',$data);

				// ! Copy the image to /assets
				if ($new_page_id)
				{
					if (is_file($upload_image_path.'/'.$page_info['image']))
					{
						$filename = basename($page_info['image']);
						$serial = date('YmdHis').substr(microtime(),2,6);
						$path = '/'.DIR_COMICS_IMG.$serial;
						mkdir('..'.$path);
						copy($upload_image_path.'/'.$page_info['image'],'..'.$path.'/'.$filename);

						$data = array (
							'url' => $path.'/'.$filename,
							'description' => $page_info['title'],
							'date_created' => $db->now()
						);
						$ir_id = $db->insert('image_reference',$data);
					}
				}
				if ($ir_id && $new_page_id)
				{
					$data = array (
						'image_reference_id' => $ir_id,
						'rel_id' => $new_page_id,
						'rel_type' => 'page',
						'sort_order' => 1,
						'date_created' => $db->now()
					);
					$match_id = $db->insert('image_match',$data);
				}
			}
		}
	}
	$alert_output .= $message->success_dialog('Book imported. <a href="book.view.php?book_id='.$book_id.'">Check it out</a>.');
	$completed = TRUE;
}


//echo '<pre>$chapter_list|';print_r($chapter_list);echo '|</pre>';



// ! ------ Display confirmation

if ($comic_page_list && $completed === FALSE)
{
	$tally_output  = '<h2>'.count($comic_page_list).' comic pages found</h2>';
	$tally_output .= '<p>Based on <a href="'.$upload_xml_path.$xml_source_filename.'">the provided XML</a>, here’s what we have:</p>';
}

if (!$comic_page_list && $completed === FALSE)
{
	$tally_output  = '<h2>No comic pages found</h2>';
	$tally_output .= '<p><a href="'.$upload_xml_path.$xml_source_filename.'">The provided XML</a> didn’t have any comic pages. Is it from ComicEasel?</p>';
}



// ! Get a list of books, if they have multibook
if (is_file('book.list.php') && $chapter_list)
{
	$db->orderBy('sort_order,title', 'ASC');
	$book_list = $db->get('book',NULL,'id,title');

	$select_list->list = $book_list;
	$select_list->name = 'book_id';
	$select_list->valueID = 'id';
	$select_list->valueTitle = 'title';
	$select_list->setStyle('width:300px');
	$book_select = $select_list->buildSelect();
	$which_book  = '<label for="book_id">Import into:</label> '.$book_select;
	$which_book .= '<p><strong>Warning:</strong> Importing into a book will delete its contents.</p>'."\n";
	$which_book .= '<label for="new_book_name">Or create a new book named:</label>'."\n";
	$which_book .= '<input type="text" name="new_book_name" id="new_book_name" style="max-width:10rem" value=""/>'."\n";
}
else
{
	$which_book = '<p><strong>Warning:</strong> Importing will overwrite your book’s existing pages.</p>'."\n";
}


// ! Show page lists organized by chapter
if ( $chapter_list && $completed === FALSE)
{
	$chapter_ct = 1; // for markers
	$sort_order = 1; // for pages
	$chapter_output .= '<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";
	foreach ( $chapter_list as $chapter_name => $page_list )
	{
		foreach ($page_list as $page_id => $page_info)
		{
			if (is_file($upload_image_path.'/'.$page_info['image']))
			{
				$found = 'OK';
			}
			else
			{
				$found = '/import/uploads/'.$page_info['image'].' <strong>missing</strong>';
			}
			$sort_order_input = '<input type="text" name="sort_order_list['.$page_id.']" style="max-width:4rem" value="'.$sort_order.'"/>';

			// Assemble the list item.
			$list_items[] = array(
				'title'=> $page_info['title'],
				'sort_order'=> $sort_order_input,
				'marker'=> $chapter_name,
				'image' => $found
			);
			$sort_order++;
		}
		$chapter_ct++;
	}
	$chapter_output .= '<br/><h2>Import</h2>'."\n";
	$chapter_output .= $which_book;
	$chapter_output .= '	<br/><button class="btn primary upload" name="user_confirmed" type="submit" value="1"><i></i>Import</button>'."\n";

}

// 

if ($list_items)
{
	$heading_list[] = array(
		'value' => 'Title'
	);
	$heading_list[] = array(
		'value' => 'Order'
	);
	$heading_list[] = array(
		'value' => 'Marker'
	);
	$heading_list[] = array(
		'value' => 'Image'
	);
	$list-> headings($heading_list);
	$list-> draggable(false);
	$list-> row_class('bookshelf');

	// Mix it all together.
	$list->content($list_items);
	$confirm_list_output  = $list->format_headings();
	$confirm_list_output .= $list->format_content();
}

// ! Let the user upload a new file

if ( is_file($upload_xml_path.$xml_source_filename) )
{
	$reset_output  = '<hr/><h2>Start over</h2>'."\n";
	$reset_output .= '<p>Upload a different WordPress XML file?</p>'."\n";
	$reset_output .= '<a class="btn secondary delete" href="?reset=1"><i></i>Yes, reset</a>'."\n";
}




// ! ------ Initial display

// ! Build the upload form
if ( !is_file($upload_xml_path.$xml_source_filename) )
{
$initial_upload_form = <<<EOL
	<input type="hidden" name="grlx_xss_token" value="{$_SESSION[admin]}"/>
	<h2>Upload a WordPress XML file</h2>
	<p>
		<label for="file[]">Select XML file</label>
		<input name="file[]" id="file[]" type="file" />
	</p>
	<button class="btn primary upload" name="submit" type="submit" value="submit"><i></i>Upload</button>

EOL;
}





$view->page_title('ComicEasel import');
$view->tooltype('chap');
$view->headline('ComicEasel import');


print ( $view->open_view() );
print ( $view->view_header() );
print ( $alert_output );
print ( '<form action="book.import-wp.php" method="post" enctype="multipart/form-data">' );
print ( $initial_upload_form );
print ( $tally_output );
print ( $confirm_list_output );
print ( $chapter_output );
print ( $reset_output );
print ( '</form>' );
print($view->close_view());

