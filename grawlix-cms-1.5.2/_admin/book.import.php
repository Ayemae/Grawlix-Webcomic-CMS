<?php

class bulkImport{
	var $import_top_list;

	function makeSerial(){
		$serial = date('YmdHis').substr(microtime(),2,6);
		return $serial;
	}
	function moveImage($source_path,$serial){
		mkdir('../'.DIR_COMICS_IMG.'/'.$serial);
		$success = rename ( $source_path, '../'.DIR_COMICS_IMG.'/'.$serial.'/'.$this_file );
		return $success;
	}
	function importFolders(){
		if ( is_array($this-> fileList) ) {
			foreach ( $this-> fileList as $item ) {
				
			}
		}
	}
}

$bimport = new bulkImport;


/*****
 * Setup
 */

require_once('panl.init.php');

$view = new GrlxView;
$fileops = new GrlxFileOps;
$message = new GrlxAlert;
$book = new GrlxComicBook(1);
$comic_image = new GrlxComicImage;
$marker = new GrlxMarker;
$link = new GrlxLinkStyle;
$link1 = new GrlxLinkStyle;
$list = new GrlxList;
$sl = new GrlxSelectList;

$import_path = '../import';

if ( $book ) {
	$book-> getPages();
}

if ( $book-> pageList ) {
	$last_page = end($book-> pageList);
	$last_page = $last_page['sort_order'];
}
else {
	$last_page = 0;
}

$book_id = $book-> bookID;

$marker_type_list = $db-> get ('marker_type',null,'id,title');
$marker_type_list = rekey_array($marker_type_list,'id');







/*****
 * Actions
 */


if ( $_POST ) {

	// What’s in the import folder?
	$import_top_list = $fileops-> get_dir_list($import_path);

	if ( $import_top_list ) {
		foreach ( $import_top_list as $key => $val ) {

			if (is_dir($import_path.'/'.$val)) {
				$folder_list[$val] = $import_path.'/'.$val;
			}
			else {
				$file_list[$val] = $import_path.'/'.$val;
			}
		}
	}

	// Build a list of each folder and its contents.
	if ( $folder_list ) {
		foreach ( $folder_list as $key => $val ) {
			$file_list = $fileops-> get_dir_list($val);
			if ( $file_list && count($file_list) > 0 ) {
				$master_folder_list[$key] = $file_list = $fileops-> get_dir_list($val);
			}
		}
	}

	if ( $master_folder_list ) {
		//Get the starting schedule date and increment:
		$var_list = array(
			array('pub_day','int'),
			array('pub_month','int'),
			array('pub_year','int'),
			array('pub_time','string'),
			array('pub_frequency', 'int'),
			array('pub_frequency_unit', 'int'), //seconds to multiply the frequency by
		);

		if ( $var_list ) {
			foreach ( $var_list as $key => $val ) {
				${$val[0]} = register_variable($val[0],$val[1]);
			}
		}
		
		if ( $pub_year && $pub_month && $pub_day ) {
			$publish_start = strtotime($pub_year.'-'.$pub_month.'-'.$pub_day.' '.$pub_time);
		}
		else {
			$publish_start = null;
		}
		//TODO: Offer an option for a frequency or specific days. If days are selected, set $pub_frequency to false
		$pub_frequency *= $pub_frequency_unit;
		
		$pages_processed = 0;

		// Assume everything works unless proven otherwise. 
		// I feel optimistic. Thanks, @BarefootCoffee!
		$total_success = true;

		$i = $last_page + 1; // Sort_order count
		$first_page_id = null; // Triggers when to create a marker.

		foreach ( $master_folder_list as $folder => $file_list ) {

				// Create the marker. We use the folder’s name as the marker’s title.
				// TO DO: Make the marker type dynamic.
				$new_marker_id = $marker->createMarker($folder,1,$first_page_id);

				// We’re on the first page of this set.
				$first_page = true;

				// Make sure they’re in order.
				natsort($file_list); //Is sort better? PHP natsort ignores leading zeroes entirely, which means numbers that consist only of 0 behave oddly.

				foreach ( $file_list as $this_file ) {

					$serial = $bimport-> makeSerial();
					$permissions = fileperms($import_path.'/'.$folder);

					if ( $permissions == '16895' ) {
						$new_path = '../'.DIR_COMICS_IMG.'/'.$serial;
						mkdir($new_path);
						$success = rename ( $import_path.'/'.$folder.'/'.$this_file, $new_path.'/'.$this_file );
					}
					else {
						$success = false;
						$total_success = false;
						$alert_output = $message-> alert_dialog('I couldn’t import all of the new images. Looks like a permissions error. Please temporarily set the folders in /import to 777.');
					}

					if ( $success ) {

						// Create the image DB record.
						$new_image_id = $comic_image-> createImageRecord ( '/'.DIR_COMICS_IMG.$serial.'/'.$this_file );

						// Create the page DB record.
						$title = explode('.',$this_file);
						array_pop($title); //clear the file extension (the last element)
						$title = implode('.',$title); //glue the filename back together, including any .s
						//TODO: Instead of explode and implode, use strrpos to find the last ., and if one is found, substr up to it. Should be faster.
						if($publish_start) { //we're doing scheduling!
							//TODO: Check if $pub_frequency is false. If it is, do the more complex day of the week logic.
							$date_publish = date('Y-m-d H:i:s', $publish_start + $pub_frequency * $pages_processed);
						} else $date_publish = null;
						
						if ( $first_page === true ) {
							$new_page_id = $comic_image-> createPageRecord($title,$last_page + $i,$book_id,$new_marker_id,$date_publish);
							$first_page = false;
						}
						else {
							$new_page_id = $comic_image-> createPageRecord($title,$last_page + $i,$book_id,null,$date_publish);
						}

						// Assign the image to the page.
						if ( $new_image_id && $new_page_id ) {
							$new_assignment_id = $comic_image-> assignImageToPage($new_image_id,$new_page_id);
						}
						$i += 0.0001;
					}
					elseif ( $success !== false ) {
						$total_success = false;
						$alert_output .= $message-> alert_dialog('I couldn’t import images from '.$folder.'.');
					}
					$pages_processed++;
				}
			}
		}
	}

	reset_page_order($book_id,$db);
	if ( $total_success === TRUE ) {
		$link->url('book.view.php');
		$link->tap('Check ’em out');
		$alert_output .= $message-> success_dialog('Hooray! Images imported. '.$link-> paint().'.');
	}








/*****
 * Display
 */


// Reset in case of form submission.
$folder_list = array();
$file_list = array();

// What’s in the import folder?
$import_top_list = $fileops-> get_dir_list($import_path);

if ( $import_top_list ) {
	foreach ( $import_top_list as $key => $val ) {
		if (is_dir($import_path.'/'.$val)) {
			$folder_list[$val] = $import_path.'/'.$val;
		}
		elseif ( is_image($import_path.'/'.$val) ) {
			$file_list[$val] = $import_path.'/'.$val;
		}
	}
}

if ( !$folder_list ) {
	$alert_output .= $message-> info_dialog('No folders found in /import on your web server.');
}
if ( !$folder_list && !$file_list )
{
	$instructions_output = '<p>Upload many images to the “import” folder via FTP to add them to your comic here. Each folder inside of “import” becomes its own '.$marker_type_list[1]['title'].' — for example, images in “/import/something” will become a '.$marker_type_list[1]['title'].' called “something”.</p>';
}



if ( $folder_list ) {

	$permissions_error_found = false;
	$total_count = 0;

	foreach ( $folder_list as $key => $val ) {
		$count = $fileops-> get_dir_list($val);
		if($count)
			$count = count($count);
		else
			$count = 0;
		$total_count += $count;

		$permissions = fileperms($val);
		if ( $permissions && $permissions != '16895' ) {
			$permissions_error = '<strong>Access error</strong>';
			$permissions_error_found = true;
		}
		else {
			$permissions_error = 'Looks good';
		}

		$list_items[] = array(
			$key,
			'&nbsp;',
			$count,
			$permissions_error
		);
	}

	if ( $total_count && $total_count > 0 ) {
		if ( $permissions_error_found === false ) {
			$submit_output .= '<input type="submit" class="btn primary new" name="submit" value="Import to new '.$marker_type_list[1]['title'].'s"/>'."\n";
		}
		else {
			$submit_output .= '<input type="submit" class="btn primary new" name="submit" value="Try to import anyway"/>'."\n";
		}
	}
	elseif ( !$file_list ) {
		$alert_output .= $message-> info_dialog('I found folders, but no image files, in /import.');
	}
}


if ( $permissions_error_found === true && !$alert_output ) {
	$alert_output .= $message-> alert_dialog('I may not be able to work with these files. Try setting the folders inside of “import” to 777.');
}





/*****
 * Display
 */

if ( $list && $list_items && count($list_items) > 0 ) {
	$heading_list = array ('Title','&nbsp;','Images','Info');

	$list-> headings($heading_list);
	$list-> draggable(false);
	$list-> row_class('chapter');

	$list->headings($heading_list);
	$list->content($list_items);
	$folder_output  = $list->format_headings();
	$folder_output .= $list->format_content();

}


// Group

if ( $folder_output ) {
	
	// Build calendar options (month list, day list, year list)
	for ( $i=1; $i<32; $i++ ) {
		$i < 10 ? $i = '0'.$i : null;
		$day_list[$i] = array(
			'title' => $i,
			'id' => $i
		);
	}
	for ( $i=1; $i<13; $i++ ) {
		$i < 10 ? $i = '0'.$i : null;
		$month_list[$i] = array(
			'title' => date("F", mktime(0, 0, 0, $i, 1, 2015)),
			'id' => $i
		);
	}
	for ( $i=date('Y')-20; $i<date('Y')+2; $i++ ) {
		$year_list[$i] = array(
			'title' => $i,
			'id' => $i
		);
	}
	
	// Build select elements for each date part.
	$sl-> setName('pub_year');
	$sl-> setCurrent(date('Y'));
	$sl-> setList($year_list);
	$sl-> setValueID('id');
	$sl-> setValueTitle('title');
	$sl-> setStyle('width:6rem');
	$year_select_output = $sl-> buildSelect();

	$sl-> setName('pub_month');
	$sl-> setCurrent(date('m'));
	$sl-> setList($month_list);
	$sl-> setValueID('id');
	$sl-> setValueTitle('title');
	$sl-> setStyle('width:9rem');
	$month_select_output = $sl-> buildSelect();

	$sl-> setName('pub_day');
	$sl-> setCurrent(date('d'));
	$sl-> setList($day_list);
	$sl-> setValueID('id');
	$sl-> setValueTitle('title');
	$sl-> setStyle('width:5rem');
	$day_select_output = $sl-> buildSelect();
	
	//Create the units list:
	$frequency_units = array(
		0 => ['id' => '60',
			'title' => 'minutes'
			],
		1 => ['id' => '3600',
			'title' => 'hours'
			],
		2 => ['id' => '86400',
			'title' => 'days'
			]
	);
	
	$sl-> setName('pub_frequency_unit');
	$sl-> setCurrent('86400');
	$sl-> setList($frequency_units);
	$sl-> setValueID('id');
	$sl-> setValueTitle('title');
	$sl-> setStyle('width:9rem');
	$frequency_unit_select_output = $sl-> buildSelect();
	
	
	
	$link-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/ftp');
	$link-> tap('via FTP');
	
	$link1-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/importing');
	$link1-> tap('Learn more.');
	
	$view->group_h2('Folders');
	$view->group_instruction('This tool lets you copy images from the “/import” folder ('.$link-> external_link().') into '.$marker_type_list[1]['title'].'s. '.$link1->external_link());
	$view->group_contents( $folder_output );
	$content_output .= $view->format_group()."\n";
	
	
	//Scheduling tools:
	$schedule_output = '<label>Publication start date</label>'."\n";
	$schedule_output .= $day_select_output;
	$schedule_output .= $month_select_output;
	$schedule_output .= $year_select_output;
	$schedule_output .= '&nbsp;Time: <input type="text" name="pub_time" style="width:6rem;display:inline" value="'.date('H:i:s').'"/>'."\n";
	
	$schedule_output .= '<label>Publication frequency</label>'."\n";
	$schedule_output .= '<input type="number" name="pub_frequency" id="pub_frequency" value="0" style="width:5rem;display:inline"/>&nbsp;';
	$schedule_output .= $frequency_unit_select_output;
	
	$view->group_h2('Scheduling');
	$view->group_instruction('When should the first page go public, and how often should subsequent pages go up?');
	$view->group_contents( $schedule_output."<br>\n".$submit_output );
	$content_output .= $view->format_group()."\n";
}




$view->page_title('Create pages from FTP');
$view->tooltype('Chapter');
$view->headline('Create pages from FTP');

$link->url('./book.page-create.php');
$link->tap('Create one comic page');
$link->reveal(false);
$action_output = $link->button_secondary('new');

$view->action($action_output);

$output  = $view->open_view();
$output .= $view->view_header();
print($output);
?>

<form accept-charset="UTF-8" action="book.import.php" method="post">
<?=$alert_output ?>
<?=$instructions_output ?>
<?=$content_output ?>
</form>
<?php

$output = $view->close_view();
print($output);
?>