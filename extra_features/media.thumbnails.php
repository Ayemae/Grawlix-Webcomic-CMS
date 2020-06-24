<?php

// Media library v1.0, July 2017

// ! ------ Setup

require_once('panl.init.php');

$view = new GrlxView;
$message = new GrlxAlert;
$gd_enabled = FALSE; // Until proven otherwise

$view->yah = 15;

$var_list = array(
	'action'
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		$$val = register_variable($val,$var_type_list[$key]);
	}
}

$gd_info = gd_info();
if ($gd_info && $gd_info['GD Version'] != '')
{
	$gd_enabled = TRUE;
}

// ! How big should thumbnails be?
$db->where('label','thumb_max');
$result = $db->getOne('milieu','value');

if ($result)
{
	$thumb_max = $result['value'];
}
else
{
	$thumb_max = 200;
}


function scan_folder($path)
{
	$dir_obj = dir($path);
	while (false !== ($entry = $dir_obj->read())) {
		if ($entry != '.' && $entry != '..')
		{
			$result[] = $path.$entry;
		}
	}
	$dir_obj->close();
	return $result;
}


// ! ------ Build thumbnails


// ! What are the folders?

if ($action)
{
	$dir_list = scan_folder('../'.DIR_COMICS_IMG);
}


// ! Fill in missing thumbnails.

if ( $dir_list && $action == 'fill-thumbs' && $gd_enabled === TRUE )
{
	// Track how many thumbnails we’re about to make.
	$created = 0;
	$error_files = array();

	foreach ( $dir_list as $key => $folder )
	{
		// What files are in this folder?
		$in_this_folder = scan_folder($folder.'/');
		if ( $in_this_folder )
		{
			$has_thumb = FALSE;
			foreach ( $in_this_folder as $key2 => $val2 )
			{
				// Got a thumb?
				$filename = basename($val2);
				if (substr($filename,0,5) == 'thumb')
				{
					$has_thumb = TRUE;
				}
			}

			// No thumbnail, eh? Then make a thumb of the LAST file scanned.
			if ($has_thumb === FALSE)
			{
				$extension = explode('.',$filename);
				$extension = array_pop($extension);
				$thumb_filename = $folder.'/thumb.'.$extension;

				if (
					$extension == 'png'
					|| $extension == 'jpg'
					|| $extension == 'jpeg'
					|| $extension == 'gif'
				)
				{
					$success = create_thumbnail( $val2, $thumb_filename, $thumb_max);
					if($success === TRUE)
					{
						$created++;
					}
					else
					{
						$error_files[] = $folder.'/'.$filename;
					}
				}

				if ($extension == 'svg')
				{
					copy($val2, $thumb_filename);
					$created++;
				}
			}
		}
	}
}

// ! Create & overwrite thumbs.
if ( $dir_list && $action == 'replace-thumbs' && $gd_enabled === TRUE )
{
	// Track how many thumbnails we’re about to make.
	$created = 0;
	$error_files = array();

	foreach ( $dir_list as $key => $folder )
	{
		// What files are in this folder?
		$in_this_folder = scan_folder($folder.'/');

		if ( $in_this_folder )
		{
			$filename = basename($in_this_folder[0]);
			$extension = explode('.',$filename);
			$extension = array_pop($extension);
			$thumb_filename = $folder.'/thumb.'.$extension;

			if (
				$extension == 'png'
				|| $extension == 'jpg'
				|| $extension == 'jpeg'
				|| $extension == 'gif'
			)
			{
				$success = create_thumbnail($in_this_folder[0], $thumb_filename, $thumb_max);
				if($success === TRUE)
				{
					$created++;
				}
				else
				{
					$error_files[] = $folder.'/'.$filename;
				}
			}

			if ($extension == 'svg')
			{
				copy($in_this_folder[0], $thumb_filename);
				$created++;
			}
		}
	}
}

// ! Report

if ($gd_enabled === FALSE)
{
	$alert_output .= $message->alert_dialog('I can’t make thumbnails because your web host can not process images.');
}

if ($_GET && $created == 0)
{
	$alert_output .= $message->alert_dialog('No new thumbnails created.');
}
if ($_GET && $created == 1)
{
	$alert_output .= $message->success_dialog('One new thumbnail created.');
}
if ($_GET && $created > 1)
{
	$alert_output .= $message->success_dialog($created.' new thumbnails created.');
}


// ! Find where “broken” images are used.
if ( $error_files )
{
	foreach ( $error_files as $path )
	{
		$ir_id = NULL; // reset
		$result2 = NULL; // reset
		$path = substr($path, 2);
		$filename = basename($path);
		$db->where('url',$path);
		$result1 = $db->getOne('image_reference', 'id');
		if ($result1)
		{
			$ir_id = $result1['id'];
		}
		if ($ir_id && is_numeric($ir_id))
		{
			$db->where('image_reference_id',$ir_id);
			$db->join('book_page bp', 'rel_id = bp.id');
			$result2 = $db->getOne('image_match', 'bp.id,bp.title');
		}
		if ($result2)
		{
			$error_list[] = '<a href="'.$path.'">'.$filename.'</a> on <a href="book.page-edit.php?page_id='.$result2['id'].'"><strong>'.$result2['title'].'</strong></a>';
		}
		else
		{
			$error_list[] = '<a href="'.$path.'">'.$filename.'</a> on <strong>(unknown page)</strong>';
		}
	}
}




// ! ------ Build the display

// ! What can they do? Show ’em

if ($gd_enabled === TRUE)
{
	$actions_output  = '<h3>Auto-generate thumbnails</h3>'."\n";
	$actions_output .= '<p>Create thumbnails for all images, even if they already have one. Use this if you want to <em>completely replace</em> all existing thumbnails.</p>'."\n";
	$actions_output .= '<p><a class="btn secondary" href="?action=replace-thumbs">Create all thumbnails</a></p>'."\n";

	$actions_output .= '<hr/><h3>Create missing thumbnails</h3>'."\n";
	$actions_output .= '<p>Create thumbnails for images that don’t have one.</p>'."\n";
	$actions_output .= '<p><a  class="btn secondary" href="?action=replace-thumbs">Fill out thumbnails</a></p>'."\n";

}

// ! Display errors

if ($error_list && count($error_list) > 0)
{
	$error_output  = '<p>I couldn’t work with these files:</p>'."\n";
	$error_output .= '<ul>'."\n";
	foreach ( $error_list as $filename )
	{
		$error_output .= '<li>'.$filename.'</li>'."\n";
	}
	$error_output .= '<ul>'."\n";
}






// ! ------ Output the display

$view->page_title("Thumbnail generator");
$view->tooltype('chap');
$view->headline('Thumbnail generator');
$view->action('<a class="lnk back" id="browse-media" href="media.list.php"><i></i>Media library</a>');

if ($error_output)
{
	$view->group_h2('Report');
	$view->group_contents($error_output);
	$feed_options_output  = $view->format_group().'<hr/>';
}

if ($gd_enabled === TRUE)
{
	$view->group_h2('Actions');
	$view->group_instruction('Thumbnails are small versions of full-size comic images. Use these tools to create them on the fly.');
	$view->group_contents($actions_output);
	$final_actions_output = $view->format_group();
}

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $feed_options_output;
$output .= $final_actions_output;
print($output);

print( $view->close_view() );
