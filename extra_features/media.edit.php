<?php

// ! ------ Setup

require_once('panl.init.php');
require_once('lib/htmLawed.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;
$fileops = new GrlxFileOps;
$gd_enabled = FALSE; // Until proven otherwise

$max_file_size = ini_get( 'upload_max_filesize' ).'B maximum';

$view->yah = 15;

$var_list = array(
	array('image_id','int'),
	array('description','string'),
	array('action','string')
);
if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		${$val[0]} = register_variable($val[0],$val[1]);
	}
}


if ( !$image_id )
{
	header('location:media.list.php');
	die();
}
if ( $image_id && !is_numeric($image_id))
{
	header('location:media.list.php');
	die();
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


// ! ------ Updates

// ! Update caption etc.

if ($_POST && $description)
{
	$data = array('description'=>$description);
	$db->where('id',$image_id);
	$success = $db->update('image_reference',$data);
	if ($success)
	{
		$alert_output .= $message->success_dialog('Description saved.');
	}
	else
	{
		$alert_output .= $message->alert_dialog('I couldn’t save the description.');
	}
}

if ( $_FILES ) {

	// ! Get info about the former main image

	$db->where('id',$image_id);
	$image_info = $db->getOne('image_reference','id,description,url,date_created,date_modified');
	if ($image_info)
	{
		$temp_path = explode('/',$image_info['url']);
		array_pop($temp_path);
		$image_info['directory'] = implode('/',$temp_path);
	}

	// ! Make a folder, if necessary.

	if (!is_dir('..'.$image_info['directory']))
	{
		$serial = date('YmdHis').substr(microtime(),2,6);
		$image_info['directory'] = '/'.DIR_COMICS_IMG.$serial;

		// Error handling
		$image_info['directory'] = str_replace('//', '/', $image_info['directory']);
		mkdir('..'.$image_info['directory']);
	}

	// ! Move the MAIN file to its new home.

	if ($_FILES['file_change'] && $_FILES['file_change']['name'])
	{
		$success1 = move_uploaded_file($_FILES['file_change']['tmp_name'], '..'.$image_info['directory'].'/'.$_FILES['file_change']['name']);

		if ( !$success1 ) {
			$result = report_image_error($image_info,$_FILES[$which]['error'][$key]);
			$alert_output .= $message->alert_dialog($result);
		}
		// Or add an image reference to the database.
		else {
			// Update the DB image reference to use the new file.
			$data = array(
				'url' => $image_info['directory'].'/'.$_FILES['file_change']['name'],
				'date_modified' => $db->now()
			);
	
			$db->where('id',$image_id);
			$success2 = $db->update('image_reference', $data);
	
			$alert_output .= $message->success_dialog('File uploaded.');
		}
	}

	// ! Move the THUMBNAIL file to its new home.
	if ($_FILES['thumbnail'] && $_FILES['thumbnail']['name'])
	{
		$main_extension = explode('.',$image_info['url']);
		$main_extension = array_pop($main_extension);
		$thumb_filename = explode('/',$_FILES['thumbnail']['name']);
		$thumb_extension = array_pop($thumb_filename);
		$thumb_extension = explode('.',$thumb_extension);
		$thumb_extension = array_pop($thumb_extension);
		if ($thumb_extension == $main_extension)
		{
			$thumb_filename = implode('/',$thumb_filename).'thumb.'.$thumb_extension;
			$success1 = move_uploaded_file($_FILES['thumbnail']['tmp_name'], '..'.$image_info['directory'].'/'.$thumb_filename);
			if ( !$success1 ) {
				$result = report_image_error($image_info,$_FILES[$which]['error'][$key]);
				$alert_output .= $message->alert_dialog($result);
			}
			// Or add an image reference to the database.
			else {
				$alert_output .= $message->success_dialog('File uploaded.');
			}
		}
		else
		{
			$alert_output .= $message->alert_dialog('The thumbanail must be the same type of graphic as the main file ('.$main_extension.').');
		}
	}
}

// ! Make a thumbnail.

if ($action && $action == 'gen-thumb')
{
	$db->where('id',$image_id);
	$image_info = $db->getOne('image_reference','id,description,url,date_created,date_modified');

	if ($image_info)
	{
		$folder = explode('/',$image_info['url']);
		array_pop($folder);
		$folder = implode('/',$folder);
		$image_info['directory'] = $folder;

		$extension = explode('.',$image_info['url']);
		$extension = array_pop($extension);
		$thumb_filename = '..'.$folder.'/thumb.'.$extension;

		if (
			$extension == 'png'
			|| $extension == 'jpg'
			|| $extension == 'jpeg'
			|| $extension == 'gif'
		)
		{
			$success = create_thumbnail( '..'.$image_info['url'], $thumb_filename, $thumb_max);
		}
		if ($success)
		{
			$alert_output .= $message->success_dialog('Thumbnail created.');
		}
		else
		{
			$alert_output .= $message->alert_dialog('Thumbnail not created.');
		}
	}
}




// ! ------ Display logic


// ! Get image info

$db->where('id',$image_id);
$image_info = $db->getOne('image_reference','id,description,url,date_created,date_modified');
$temp_path = explode('/',$image_info['url']);
array_pop($temp_path);
$image_info['directory'] = implode('/',$temp_path);


// ! Where is this image used?
$db->where('image_reference_id',$image_id);

$match_list = $db->get('image_match',NULL,'rel_id,rel_type,id,image_reference_id');

if ( $match_list && count($match_list) > 0 )
{
	$matches = 'This appears on:<br/><ul>'."\n"; // reset
	foreach ( $match_list as $key2 => $val2 )
	{
		switch($val2['rel_type'])
		{
			case 'page':
				$db->where('id',$val2['rel_id']);
				$info = $db->getOne('book_page','title');
				if ($info && $info['title'])
				{
					$title = '<li>'.$info['title'].'</li>'."\n";
				}
				else
				{
					$title = '<li>(Unknown comic page)</li>';
				}
				$matches .= $title."\n";
				break;

			case 'marker':
				$db->where('id',$val2['rel_id']);
				$info = $db->getOne('marker','title');
				if ($info && $info['title'])
				{
					$title = '<li>'.$info['title'].'</li>'."\n";
				}
				else
				{
					$title = '<li>(Unknown marker)</li>';
				}
				$matches .= $title."\n";
				break;

			case 'book':
				$db->where('id',$val2['rel_id']);
				$info = $db->getOne('book','title');
				if ($info && $info['title'])
				{
					$title = $info['title'];
				}
				else
				{
					$title = '<li>(Unknown book)</li>';
				}
				$matches .= $title."\n";
				break;
		}
	}
	$matches .= '</ul>'."\n";
	$matches .= '<a href="media.usage.php?image_id='.$image_id.'"><strong>Manage usage</strong></a>'."\n";
}
else
{
	$matches  = 'No pages or markers use this image. ';
	$matches .= '<a href="media.usage.php?image_id='.$image_id.'"><strong>Do something about that</strong></a>'."\n";
}






if ( $image_info['url'] && substr($image_info['url'],0,4) != 'http' && is_file('..'.$image_info['url'])) {
	$image_dimensions = getimagesize('..'.$image_info['url']);
	$image_bytes = filesize('..'.$image_info['url']);

	$image_type = basename('..'.$image_info['url']);
	$image_type = explode('.',$image_type);
	$image_type = array_reverse($image_type);
	$image_type = $image_type[0];
}

if ($image_dimensions && $image_bytes)
{
	$weight_output = figure_pixel_weight($image_dimensions[0],$image_dimensions[1],$image_bytes);
	$weight_output = round($weight_output,3).' bytes/pixel';
	$dimensions_output = $image_dimensions[0].' &times; '.$image_dimensions[1].' pixels';
	$filesize_output = round($image_bytes/1000,2).' KB';
}
else
{
	$weight_output = 'Unable to calcluate pixel weight';
}

if (!$image_dimensions || count($image_dimensions) == 0)
{
	$dimensions_output = 'I couldn’t read this file’s dimensions';
}
if (!$image_bytes)
{
	$filesize_output = 'I couldn’t get this file’s size';
}

switch($image_type)
{
	default:
		$image_type_output = 'Unknown file type';
		break;
	case 'png':
		$image_type_output = 'PNG file';
		break;
	case 'jpg':
	case 'jpeg':
		$image_type_output = 'JPG file';
		break;
	case 'gif':
		$image_type_output = 'GIF file';
		break;
	case 'svg':
		$image_type_output = 'SVG file';
		break;
}

if ($image_info['url'] && substr($image_info['url'],0,4) == 'http')
{
	$image_link = '<a href="'.$image_info['url'].'">'.$image_info['url'].'</a>';
}
elseif ($image_info['url'] && is_file('..'.$image_info['url']))
{
	$image_link = '<a href="..'.$image_info['url'].'">'.$image_info['url'].'</a>';
}
elseif ($image_info['url'])
{
	$image_link = $image_info['url'];
}
else
{
	$image_link = 'No file path, and probably no file, either';
}

$stats_output  = '<ul>'."\n";
$stats_output .= '<li><strong>Path:</strong> '.$image_link."</li>\n";
$stats_output .= '<li><strong>Dimensions:</strong> '.$dimensions_output."</li>\n";
$stats_output .= '<li><strong>Bytes:</strong> '.$filesize_output."</li>\n";
$stats_output .= '<li><strong>Pixel weight:</strong> '.$weight_output."</li>\n";
$stats_output .= '<li><strong>File type:</strong> '.$image_type_output."</li>\n";
$stats_output .= '</ul>'."\n";

$description_output  = '		<label for="description">Image description (alt text)</label>'."\n";
$description_output .= '		<input type="text" name="description" id="description" value="'.$image_info['description'].'" style="max-width:40rem"/>';





// ! ------ Display

// ! Image file

if ($image_info['url'] && substr($image_info['url'],0,4) == 'http')
{
	$img_element = '<img src="'.$image_info['url'].'" alt="pic" style="max-height:300px;border:1px solid #eee"/>';
}
elseif ($image_info['url'] && is_file('..'.$image_info['url']))
{
	$img_element = '<img src="..'.$image_info['url'].'" alt="pic" style="max-height:300px;border:1px solid #eee"/>';
	$file_dimensions = getimagesize('..'.$image_info['url']);
}
else
{
	$img_element = 'File not found';
	$file_dimensions = NULL;
}


// ! Thumbnail file

$replace_thumb = TRUE; // Until proven otherwise.

if (substr($image_info['url'],0,4) != 'http')
{
	$thumb_filename = $image_info['url'];
	$thumb_filename = explode('/',$thumb_filename);
	$extension = array_pop($thumb_filename);
	$extension = explode('.',$extension);
	$extension = array_pop($extension);
	$thumb_filename = implode('/',$thumb_filename).'/thumb.'.$extension;

	if ($thumb_filename && is_file('..'.$thumb_filename))
	{
		$thumb_element = '<img src="'.$thumb_filename.'?reset='.date('ymdhis').'" alt="pic" style="border:1px solid #eee"/>';
	}
}
elseif (substr($image_info['url'],0,4) == 'http')
{
	$thumb_element = 'Can’t create thumbnails for remote files';
	$replace_thumb = FALSE;
}

if (!$thumb_element)
{
	$thumb_element = 'File not found';
}




// ! Tall or wide layout?

// Wide

if ($file_dimensions && $file_dimensions[0] < $file_dimensions[1] + 300)
{
	$graphic_output = <<<EOL
<div class="row">
	<div class="column small-6">
		<h2>Main image</h2>
		<p>$img_element</p>
		<label for="file_change">Replace main file ($max_file_size)</label>
		<input type="file" name="file_change" value=""/><br/>
		<button class="btn primary upload" name="submit" type="submit" value="save"/><i></i>Upload</button>
	</div>
	<div class="column small-6">
		<h2>Thumbnail</h2>
		<p>$thumb_element</p>

EOL;

if ($replace_thumb === TRUE)
{
	$graphic_output .= <<<EOL
		<p>
			<label for="file_change">Replace thumbnail ($max_file_size)</label>
			<input type="file" name="thumbnail" value=""/>
			<button class="btn primary upload" name="submit" type="submit" value="save"/><i></i>Upload</button>
		</p>
EOL;

	if ($gd_enabled === TRUE)
	{
		$graphic_output .= <<<EOL
		<hr/><p><a class="btn secondary" href="media.edit.php?image_id=$image_id&amp;action=gen-thumb">Generate thumbnail</a></p>
		<p>Create a new thumbnail based on the main image.</p>

EOL;
	}
}
$graphic_output .= '</div></div>'."\n";

}

// Tall
else
{
	$graphic_output = <<<EOL
	<div>
		<h2>Main image</h2>
		<p>$img_element</p>
		<label for="file_change">Replace main file</label>
		<input type="file" name="file_change" value=""/><br/>
		<button class="btn primary upload" name="submit" type="submit" value="save"/><i></i>Upload</button>
	</div>
	<div>
		<br/><h2>Thumbnail</h2>
		<p>$thumb_element</p>

EOL;

	if ($replace_thumb == TRUE)
	{
		$graphic_output .= <<<EOL
		<p>
			<label for="file_change">Replace thumbnail</label>
			<input type="file" name="thumbnail" value=""/>
			<button class="btn primary upload" name="submit" type="submit" value="save"/><i></i>Upload</button>
		</p>

EOL;
		if ($gd_enabled === TRUE)
		{
			$graphic_output .= <<<EOL
		<hr/><p><a class="btn secondary" href="media.edit.php?image_id=$image_id&amp;action=gen-thumb">Generate thumbnail</a></p>
		<p>Create a new thumbnail based on the main image.</p>

EOL;
		}
		$graphic_output .= '</div>'."\n";
	}
}

// ! ------ Display output

$link->url('media.list.php');
$link->tap('Media library');
$link->id('browse-media');
$action_output = $link->text_link('back');


$view->group_css('page');
$view->group_h2('Stats');
$view->group_contents($stats_output);
$view->group_instruction('Information and metadata for this file.');
$stats_output = $view->format_group();

$view->group_css('page');
$view->group_h2('Options');
$view->group_contents('<br/>'.$description_output.'<button class="btn primary save" name="submit" type="submit" value="save"/><i></i>Save</button><br/>');
$view->group_instruction('Actions you can perform on this file.');
$options_output = $view->format_group();

$view->group_css('page');
$view->group_h3('Usage');
$view->group_contents('<br/>'.$matches);
$view->group_instruction('Where in your site this image appears.');
$usage_output = $view->format_group();

$view->page_title("Comic image");
$view->tooltype('chap');
$view->headline('Comic image <span>'.$image_info['description'].'</span>');
$view->action($action_output);

print ($view->open_view());
print ($view->view_header());
print ($alert_output);

?>

<form accept-charset="UTF-8" action="media.edit.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="grlx_xss_token" value="<?=$_SESSION['admin']?>"/>
	<input type="hidden" name="image_id" value="<?=$image_id?>"/>

<?=$graphic_output ?><hr/>
<?=$options_output ?>
<?=$usage_output ?><hr/>
<?=$stats_output ?>



</form>

<?php
print($view->close_view());
