<?php

/*****
 * Setup
 */

require_once('panl.init.php');

$view = new GrlxView;
$link = new GrlxLinkStyle;
$fileops = new GrlxFileOps;

$view-> yah = 14;

// Permissions to check
$folder_list = array (
	'../'.DIR_COMICS_IMG,
	'../assets',
	'../assets/data',
	'../assets/images',
	'../assets/images/ads',
	'../assets/images/icons',
	'../assets/images/static'
);

$image_status_list = array (
	array('#404d98','toomuch'),
	array('#9f9','excellent'),
	array('#ff6','mediocre'),
	array('#fa0','bad'),
	array('#f40','abysmal')
);





// CSV of files that the admin panel should have.
$panel_files = array(
	'book.pages-create.php',
	'ad.adsense-edit.php',
	'ad.list.php',
	'ad.promo-create.php',
	'ad.promo-edit.php',
	'ad.slot-edit.php',
	'ajax.alert-dismiss.php',
	'ajax.book-delete.php',
	'ajax.book-edit.php',
	'ajax.sort.php',
	'ajax.tone-set.php',
	'ajax.visibility-toggle.php',
	'book.archive.php',
	'book.import-wp.php',
	'book.bulk-upload.php',
	'book.edit.php',
	'book.import.php',
	'book.page-create.php',
	'book.page-edit.php',
	'book.view.php',
	'css',
	'diag.health-check.php',
	'img',
	'inc',
	'index.php',
	'js',
	'lib',
	'marker-type.list.php',
	'marker.create.php',
	'marker.edit.php',
	'marker.list.php',
	'marker.view.php',
	'marker-type.create.php',
	'marker-type.edit.php',
	'panl.init.php',
	'panl.login.php',
	'panl.logout.php',
	'panl.password-forgot.php',
	'panl.password-reset.php',
	'site.config.php',
	'site.link-delete.ajax.php',
	'site.link-edit.ajax.php',
	'site.link-list.php',
	'site.nav-delete.ajax.php',
	'site.nav-edit.ajax.php',
	'site.nav.php',
	'site.nav-alt.php',
	'site.theme-dupetone.ajax.php',
	'site.theme-manager.php',
	'site.theme-options.php',
	'sttc.block-edit.php',
	'sttc.page-delete.ajax.php',
	'sttc.page-edit.php',
	'sttc.page-list.php',
	'sttc.page-new.php',
	'sttc.xml-edit.php',
	'sttc.xml-new.php',
	'uploadtome.php',
	'user.config.php',
	'xtra.comments.ajax.php',
	'xtra.edit-info.ajax.php',
	'xtra.social.php',
	'xtra.toggle-active.ajax.php',
	'xtra.toggle-wonderful-active.ajax.php'
);

$optional_files = array(
	'book.book-create.php',
	'book.list.php'
);


/*****
 * Functions
 */


// Does the .htaccess file exist?
function check_htaccess($htaccess_string) {
	$x = check_path('../.htaccess');

	if ( $x['exists'] === false ){
		$message = '<p>Missing .htaccess file</p>'."\n";
		$status = 'error';
	}
	else {
		$message = '<p>Found the .htaccess file</p>'."\n";
		$status = 'aok';
	}

	return array('message'=>$message,'status'=>$status);
}


function check_path($item) {
	// Does the file/folder exist?
	if ( !is_dir($item) && !is_file($item)) {
		$writable['exists'] = false;
	}
	else {
		$writable['exists'] = true;
	}


	// Is the file/folder writable?
	if ( $writable['exists'] == true ) {
		if ( is_writable($item) === true ){
			$writable['writable'] = true;
		}
		else {
			$writable['writable'] = false;
		}
	}

	return $writable;
}

// Check individual folders in /comics
function fetch_comic_folders() {
	if ($dir = opendir('../'.DIR_COMICS_IMG)) {

		while (false !== ($entry = readdir($dir))) {
			if(substr($entry,0,1) != '.') {
				$result[] = '../'.DIR_COMICS_IMG.$entry;
			}
		}
		closedir($dir);
	}
	return $result;
}

function display_permissions($name,$folder) {
	if ( $folder['exists'] == true ) {
		$output = 'Found '.$name.'<br/>'."\n";
	}
	else {
		$output = '<strong>Missing '.$name.'</strong><br/>'."\n";
	}
	return $output;
}

function check_comic_seo($db) {
	$errors = false; // Be optimistic

	$link = new GrlxLink;

	$total_comic_pages = $db->get ('book_page',null,'COUNT(id) AS total');
	$total_comic_pages = $total_comic_pages[0];

	$db->orderBy ('title','ASC');
	$page_list = $db->get ('book_page',null,'id,title,sort_order');
	$page_list = rekey_array($page_list,'id');

	if ( !empty($page_list) ) {
		foreach ( $page_list as $key => $val ) {
			if ( trim($val['title']) == '' ) {
				$untitled_list[$key] = $val;
				$errors = true;
			}
			else {
				if( isset($title_count[$val['title']]) )
					$title_count[$val['title']]++;
				else
					$title_count[$val['title']] = 1;
			}
			if ( $val['title'] && strlen($val['title']) < 8 ) {
				$short_name_list[$key] = $val;
				$errors = true;
			}
			if ( $val['title'] && strlen($val['title']) > 60 ) {
				$long_name_list[$key] = $val;
				$errors = true;
			}
		}
	}

	$duplicate_title_error = false;
	if ( isset($title_count) ) {
		foreach ( $title_count as $key => $val ) {
			if ( $val > 1 ) {
				$duplicate_title_error = true;
			}
		}
	}

	$output = '';
	if ( isset($short_name_list) ) {
		$output .= '<p>'.count($short_name_list).' comic pages have short titles.</p><ul>'."\n";
		$link-> rel = null;
		foreach ( $short_name_list as $key => $val ) {

			$link-> tap = $val['title'];
			$link-> url = 'book.page-edit.php?page_id='.$val['id'];

			$output .= '<li>'.$link-> paint().'</li>'."\n";
		}
		$output .= '</ul>'."\n";
	}

	if ( isset($long_name_list) ) {
		$link-> rel = null;
		$output .= '<p>'.count($long_name_list).' comic pages have overlong titles. '.$link->paint().' titles no longer than about 60 characters.</p><ul>'."\n";
		foreach ( $long_name_list as $key => $val ) {
			$link-> tap = $val['title'];
			$link-> url = 'book.chapter-edit.php?chapter_id='.$val['chapter_id'];
			$output .= '<li>'.$link-> paint().'</li>'."\n";
		}
		$output .= '</ul>'."\n";
	}

	if ( isset($untitled_list) ) {
		$link-> rel = null;
		$output .= '<p>'.count($untitled_list).' comic pages have no titles.</p><ul>'."\n";
		foreach ( $untitled_list as $key => $val ) {
			$link-> tap = 'Untitled (ID '.$key.')';
			$link-> url = 'book.chapter-edit.php?chapter_id='.$val['chapter_id'];
			$output .= '<li>'.$link-> paint().'</li>'."\n";
		}
		$output .= '</ul>'."\n";
	}
	else {
		$output .= '<p>Good news: Every page has a unique title.</p>'."\n";
	}

	if ( $duplicate_title_error === true ) {
		$output .= '<p>These titles are used more than once:</p><ul>'."\n";
		foreach ( $title_count as $key => $val ) {
			if ( $val > 1 ) {
				$output .= '<li>“'.$key.'”</li>'."\n";
			}
		}
		$output .= '</ul>';
	}

	return array($errors,$output);
}

/*function check_menu($path_items=array()) {
	$dupe_check = array_count_values($path_items);
	foreach ( $dupe_check as $key => $val ) {
		if ( $val > 1 ) {
			$dupes[] = $key;
		}
	}
}*/


function display_module($module=array(),$link) {
//echo '<pre>$module|';print_r($module);echo '|</pre>';
	if ( $module['status'] == 0 ) {
		$status = 'verified';
	}
	else {
		$status = 'problem';
	}

	$output = '<div>';
	$output .= $module['report'];
	$output .= '</div>';
	return array($output,'<span class="health-section"><a><i class="'.$status.'"></i></a></span>');
}



function image_weight_test($root_folder,$root_list,$image_status_list) {

	$link = new GrlxLinkStyle;
	$fileops = new GrlxFileOps;
	$allowed_extension = array (
		'gif',
		'png',
		'jpg',
		'svg'
	);
	$overall_status = 0;
	if ( $root_list ) {
		foreach ( $root_list as $key => $val ) {
			$folder_list[$root_folder.'/'.$val] = $fileops->get_dir_list($root_folder.'/'.$val);
		}
	}

	if ( $folder_list ) {
		foreach ( $folder_list as $folder => $set ) {
			if ( $set ) {
				foreach ( $set as $filename ) {
					$ext = substr($filename,-3,3);
					if ( in_array($ext, $allowed_extension)){

						$image_info = NULL; // reset
						$image_bytes = NULL; // reset

						if ( is_file($folder.'/'.$filename) ) {
							$image_info = getimagesize($folder.'/'.$filename);
							$image_bytes = filesize($folder.'/'.$filename);
						}
						else {
							die();
						}

						if ( $image_bytes && $image_info ) {
							$weight = figure_pixel_weight($image_info[0],$image_info[1],$image_bytes);
							$short = round($weight,3);
							$status = interpret_image_weight($weight);
							$sorted_set["$short"][] = array(
								'short' => $short,
								'weight' => $weight,
								'status' => $status,
								'filename' => $filename,
								'bytes' => $image_bytes,
								'path' => $folder.'/'.$filename,
								'image_info' => $image_info
							);
							if ( $status > $overall_status ) {
								$overall_status = $status;
							}
						}
					}
				}
				if ( isset($sorted_set) ) {
					krsort($sorted_set);
				}
			}
		}
	}
	$output = '';
	if ( isset($sorted_set) ) {
		foreach ( $sorted_set as $rank ) {
			foreach ( $rank as $image ) {
				$pad = $image['short'] * 300;
				$status = $image['status'];
				$image_bytes = number_format($image['bytes']);
				if ( strlen($image['filename']) > 20 ) {
					$abbr = substr($image['filename'],0,15).'…';
				}
				else {
					$abbr = $image['filename'];
				}

				$link-> url = str_replace('//', '/', $image['path']);
				$link-> tap = $abbr;
				$this_link = $link-> paint();

				$background = $image_status_list[$status][0];
				$short = $image['short'];
				$image_info = $image['image_info'];
				$output .= <<<EOL
<p>
	<span style="background:$background;padding-right:{$pad}px;">&nbsp;</span><br/><strong>$short bytes / pixel</strong> <small>($image_bytes b / $image_info[0] &times; $image_info[1] px)</small> $this_link
</p>

EOL;
			}
		}
	}
	return array($output,$overall_status);
}



/////////// Image weight check
$root_list = $fileops->get_dir_list('../'.DIR_COMICS_IMG);
$weight_report = image_weight_test('../'.DIR_COMICS_IMG,$root_list,$image_status_list);

$image_weight_module['anchor'] = 'image_weight';
$image_weight_module['heading'] = 'Image weight check';
$image_weight_module['status'] = $weight_report[1] ?? null;
//$image_weight_module['report']  = '<p>“Image weight” is a ratio of bytes / dimensions. Images with a better ratio load faster than others with similar pixel width &amp; height.</p>';
$image_weight_module['report'] = $weight_report[0] ?? null;

if ( !isset($image_weight_module) || !isset($image_weight_module['report']) ) {
	$image_weight_module['report'] = 'I didn’t find any images.';
}




/////////// Admin panel files check

// Assume it’s all good.
$panel_file_module['status'] = 0;


// Make sure every required file exists.
if ( $panel_files ) {
	foreach ( $panel_files as $key => $val ) {
		$check_this = check_path($val);
		if ( $check_this['exists'] !== true ) {
			$missing_panel[] = $val;
		}
	}
}
if ( isset($missing_panel) ) {
	$panel_file_module['anchor'] = 'files';
	$panel_file_module['heading'] = 'Admin panel';
	$panel_file_module['status'] = 20;
	$panel_file_module['report'] .= '<h3>Missing admin panel files</h3>'."\n";
	$panel_file_module['report'] .= '<p>These files are missing from the admin panel folder.'."\n";
	$panel_file_module['report'] .= '<ul>'."\n";
	foreach ( $missing_panel as $key => $val ) {
		$panel_file_module['report'] .= '<li>'.$val.'</li>'."\n";
	}
	$panel_file_module['report'] .= '</ul>'."\n";
}

// Look for extra files in the _admin folder.
$admin_existing_list = $fileops->get_dir_list('.');
if ( $admin_existing_list ) {
	foreach ( $admin_existing_list as $key => $val ) {
		if ( !in_array($val, $panel_files) && !in_array($val, $optional_files)) {
			$extra_panel_files[] = $val;
		}
	}
}
if ( isset($extra_panel_files) ) {
	$panel_file_module['anchor'] = 'files';
	$panel_file_module['heading'] = 'Admin panel';
	$panel_file_module['status'] = 20;
	$panel_file_module['report'] .= '<h3>Unknown files discovered</h3>'."\n";
	$panel_file_module['report'] .= '<p>These files should <em>not</em> go in the admin panel folder.'."\n";
	$panel_file_module['report'] .= '<ul>'."\n";
	foreach ( $extra_panel_files as $key => $val ) {
		$panel_file_module['report'] .= '<li>'.$val.'</li>'."\n";
	}
	$panel_file_module['report'] .= '</ul>'."\n";
}

if ( $panel_file_module['status'] == 0 ) {
	$panel_file_module['anchor'] = 'files';
	$panel_file_module['heading'] = 'Admin panel';
	$panel_file_module['report'] = '<p>No extra files found; no files missing.</p>'."\n";
}




/////////// Lost images check

// Get all of the comic image folders.
$comic_dir_list = $fileops->get_dir_list(DIR_COMICS_IMG);

// Get the contents of each comic image folder.
if ( $comic_dir_list ) {
	foreach ( $comic_dir_list as $subdirectory ) {
		$subdirectory = DIR_COMICS_IMG . '/' . $subdirectory;
		$this_dir = $fileops->get_dir_list($subdirectory);

		// Build full paths with the comic folder’s name, individual comic folders’ names, and filename.
		if ( $this_dir ) {
			foreach ( $this_dir as $filename ) {
				$filename = $milieu_list['directory']['value'] . substr($subdirectory,2) . '/' . $filename;
				$filename_list[$filename] = $filename;
			}
		}
	}
}

// Get a list of all comic images in the database.
$reference_list = get_image_reference($db);

// Compare the two.
if ( !empty($filename_list) ) {
	// Look for images in FTP but not in MySQL.
	foreach ( $filename_list as $key => $val ) {
		if ( !isset($reference_list[$key]) ) {
			$ftp_not_mysql[$key] = $val;
		}
	}

	// Look for images in MySQL but not in FTP.
	if ( $reference_list ) {
		foreach ( $reference_list as $key => $val ) {
			if ( !$filename_list[$key] && substr($key,0,4) != 'http' ) {
				$mysql_not_ftp[$key] = $val;
			}
		}
	}
}


$orphan_image_module['status'] = 0;
if ( isset($ftp_not_mysql) ) {
	$total = count($ftp_not_mysql);
	if ( $total > 30 ) {
		$limit = 25;
		$remaining = $total - $limit;
	}
	$orphan_image_module['anchor'] = 'orphan_image';
	$orphan_image_module['heading'] = 'Image check';
	$orphan_image_module['status'] = 10;
	$orphan_image_module['report'] .= '<h3>Unregistered images</h3>'."\n";
	$orphan_image_module['report'] .= '<ul>'."\n";
	foreach ( $ftp_not_mysql as $key => $val ) {
		$link-> url = $val;
		$link-> tap = $val;
		$orphan_image_module['report'] .= '<li>'.$link-> paint().'</li>'."\n";
	}

	$link-> tap = 'Assign these to a chapter';
	$link-> url = 'book.chapter-assign.php';
	$orphan_image_module['report'] .= '</ul>'."\n";
	$orphan_image_module['report'] .= '<p>'.$link-> paint().'</p><br/>'."\n";
	$orphan_image_module['report'] .= '<ul>'."\n";
	$i = 0;
	foreach ( $ftp_not_mysql as $key => $val ) {
		if ( $limit && $i < $limit ) {
			$orphan_image_module['report'] .= '<li>'.$val.'</li>'."\n";
			$i++;
		}
	}
	$orphan_image_module['report'] .= '</ul>'."\n";
	if ( $remaining ) {
		$orphan_image_module['report'] .= '<p>…and '.$remaining.' more.</p>';
	}
}
if ( !isset($orphan_image_module) || !isset($orphan_image_module['report']) ) {
	$orphan_image_module['report'] = 'I didn’t find any extra images.';
}

if ( isset($mysql_not_ftp) ) {
	$orphan_image_module['anchor'] = 'orphan_image';
	$orphan_image_module['heading'] = 'Orphan image check';
	$orphan_image_module['status'] = 10;
	$orphan_image_module['report'] .= '<h3>Missing registered images</h3>'."\n";
	$orphan_image_module['report'] .= '<p>These files listed in the database, but I can’t find their files in FTP.'."\n";
	$orphan_image_module['report'] .= '<ul>'."\n";
	foreach ( $mysql_not_ftp as $key => $val ) {
		$orphan_image_module['report'] .= '<li>'.$val['url'].'</li>'."\n";
	}
	$orphan_image_module['report'] .= '</ul>'."\n";
}






/////////// SEO check

$comic_seo_check = check_comic_seo($db);
if ( $comic_seo_check[0] == true ) {
	$seo_module['anchor'] = 'seo';
	$seo_module['heading'] = 'Comic page SEO';
	$seo_module['status'] = 1;
	$seo_module['report'] = $comic_seo_check[1];
}
else {
	$seo_module['anchor'] = 'seo';
	$seo_module['heading'] = 'Comic page SEO';
	$seo_module['status'] = 0;
	$seo_module['report'] = $comic_seo_check[1];
}




/////////// Permissions check

$comic_folders = fetch_comic_folders(DIR_COMICS_IMG);
if ( isset($folder_list) && isset($comic_folders) ) {
	$folder_list = array_merge($folder_list,$comic_folders);
}
elseif ( !isset($folder_list) && isset($comic_folders) ) {
	$folder_list = $comic_folders;
}

if ( isset($folder_list) ) {
	$permissions_module['status'] = 0;
	$error_count = 0;
//	$permissions_module['report'] .= '<p>This checks the ability to add or edit files on your behalf.</p>'."\n";
	foreach ( $folder_list as $folder ) {
		$folder_info = check_path($folder);
		if ( $folder_info['exists'] === false ) {
			$permissions_module['status'] = 10;
			$error_count++;
			$permissions_module['report'] .= '<strong>Couldn’t find '.$folder.'</strong><br/>'."\n";
		}
		elseif ( $folder_info['writable'] === false ) {
			$permissions_module['status'] = 10;
			$error_count++;
			$permissions_module['report'] .= '<strong>Couldn’t write to '.$folder.'</strong><br/>'."\n";
		}
		else {
//			$permissions_module['report'] .= $folder.' <strong>OK</strong><br/>'."\n";
		}
	}
	if ( !isset($permissions_module['report']) ) {
		$permissions_module['report'] = 'No issues found.';
	}
}
if ( $error_count == count($folder_list)) {
	$permissions_module['status'] = 20;
}
if ( $permissions_module['status'] == 0 ) {
	$permissions_module['anchor'] = 'access';
	$permissions_module['heading'] = 'Access';
}
elseif ( $permissions_module['status'] == 10 ) {
	$permissions_module['anchor'] = 'access';
	$permissions_module['heading'] = 'Access';
}
else {
	$permissions_module['anchor'] = 'access';
	$permissions_module['heading'] = 'Access';
}



/////////// Permissions check

$htaccess_status = check_htaccess($htaccess_string ?? null);
if ( $htaccess_status['status'] == 'aok' ) {
	$htaccess_module['status'] = 0;
	$htaccess_module['anchor'] = 'htaccess';
	$htaccess_module['heading'] = 'Routes';
	$htaccess_module['report'] = '<p>.htaccess file found.</p>';
}
else {
	$htaccess_module['status'] = 1;
	$htaccess_module['anchor'] = 'htaccess';
	$htaccess_module['heading'] = 'Routes';
	$htaccess_module['report'] = '<p>.htaccess file not found.</p>';
}


/////////// Display


// Group
$report = display_module($permissions_module,$link);
$view->group_h2($report[1].' Permissions');
$view->group_instruction('This checks the ability to add or edit files on your behalf.');
$view->group_contents( $report[0] );
$content_output = $view->format_group().'<hr />';

// Group
$report = display_module($panel_file_module,$link);
$view->group_h2($report[1].' Panel files');
$view->group_instruction('The panel should only have Grawlix files. Extras could indicate a hack attempt.');
$view->group_contents( $report[0] );
$content_output .= $view->format_group().'<hr />';

// Group
$report = display_module($htaccess_module,$link);
$view->group_h2($report[1].' Access');
$view->group_instruction('Grawlix needs permission to add images to certain folders.');
$view->group_contents( $report[0] );
$content_output .= $view->format_group().'<hr />';

// Group
$link-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/seo');
$link-> tap('search engine optimization');

$report = display_module($seo_module,$link);
$view->group_h2($report[1].' SEO');
$view->group_instruction('This checks '.$link-> external_link().', beginning with unique page titles.');
$view->group_contents( $report[0] );
$content_output .= $view->format_group().'<hr />';

// Group
$report = display_module($orphan_image_module,$link);
$view->group_h2($report[1].' Orphaned images');
$view->group_instruction('The database tracks every comic image. Unknown or missing JPGs, PNGs, etc are reported here.');
$view->group_contents( $report[0] );
$content_output .= $view->format_group().'<hr />';

// Group

$link-> url('http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/image-optimization');
$link-> tap('ratio of bytes / pixel');

$report = display_module($image_weight_module,$link);
$view->group_h2($report[1].' Image weight');
$view->group_instruction('“Image weight” is a '.$link-> external_link().'. Images with a better ratio load faster than others with similar pixel width &amp; height. Bars indicate bytes per pixel. <strong>Images with shorter bars load faster. Faster images make for happier readers.</strong>
<ul>
	<li>Green: Pretty good</li>
	<li>Blue: Suspiciously <em>under</em>weight</li>
	<li>Yellow: Kinda <em>over</em>weight</li>
	<li>Red: Dangerously heavy</li>
	<li>Blood red: You’re kidding, right?</li>
</ul>
');
$view->group_contents( $report[0] );
$content_output .= $view->format_group();

// Display
/*
$output  = $view->open_view();
$output .= $view->view_header();
$output .= $content_output;
$output .= $view->close_view();
*/






/*****
 * Display
 */

$view->page_title('Site health check');
$view->tooltype('health');
$view->headline('Site health check');

$output  = $view->open_view();
$output .= $view->view_header();
print($output);
?>
<p>OK, I’ve scanned your site for technical problems. Here’s the report:</p>
<?=$content_output ?>
<?php

$output = $view->close_view();
print($output);
