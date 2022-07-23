<?php

require_once('panl.init.php');

$allowed_sttc_file_types = array ('gif','png','jpg','jpeg','svg');

$variable_list = array(
	'page_id',
	'block_id',
	'title',
	'content',
	'pattern',
	'url',
	'remove_image',
	'msg'
);
if ( $variable_list ) {
	foreach ( $variable_list as $val ) {
		$$val = register_variable($val);
	}
}

// Hold it — no ID, no entrance.
if (!$block_id && !$page_id) {
	header('location:sttc.page-list.php');
	die();
}

// Allow HTML in content blocks, but prevent scripts.
$content = $_POST['content'];
if ($content)
{
	$content = str_replace('<script', '', $content);
}


$view = new GrlxView;
$link = new GrlxLinkStyle;
$message = new GrlxAlert;
$form = new GrlxForm;
$fileops = new GrlxFileOps;
$sl = new GrlxSelectList;


// Make sure the image folder exists and is accessible.
$alert_output .= $fileops->check_or_make_dir('../'.DIR_STATIC_IMG);

$view-> yah = 6;


if ( $msg == 'created' ) {
	$alert_output .= $message->success_dialog('New page built. Add your content below.');
}


// ! Update

if ( $remove_image && $remove_image == 1 && $block_id )
{
	$data = array(image => '');
	$db->where('id',$block_id);
	$success = $db->update('static_content',$data);
	if ( $success )
	{
		$alert_output .= $message->success_dialog('Image removed.');
	}
}


if ( $_POST && is_numeric($block_id) )
{
	$data = array(
		'title'=>$title,
		'content'=>$content,
		'pattern'=>$pattern,
		'url'=>$url
	);
	$db->where('id',$block_id);
	$success = $db->update('static_content',$data);
	if ( $success )
	{

		// What is this block’s page ID?
		$db->where('id',$block_id);
		$block_info = $db->getOne('static_content', null, array('page_id'));

		$alert_output .= $message->success_dialog('Changes saved. <a href="sttc.page-edit.php?page_id='.$block_info['page_id'].'">Return to this block’s static page</a>.');
	}
	else
	{
		$alert_output .= $message->warning_dialog('Changes failed to save.');
	}
}


// ! Create
if ( $_POST && $page_id && !$block_id )
{

	// What’s the last block sort_order in this page?
	$db->where('page_id',$page_id);
	$db->orderBy('sort_order', 'DESC');
	$sort_info = $db->getOne('static_content','sort_order');

	$data = array(
		'sort_order'=>$sort_info['sort_order'] + 1,
		'page_id'=>$page_id,
		'title'=>$title,
		'pattern'=>$pattern,
		'content'=>$content,
		'url'=>$url,
		'created_on' => $db->NOW()
	);
	$block_id = $db->insert('static_content',$data);
	if ( $block_id )
	{
		$alert_output .= $message->success_dialog('Content block created.');
	}
	else
	{
		$alert_output .= $message->alert_dialog('Content block not created.');
	}
}






// ! Upload images
// If the static images’ folder exists (that’s $folder_check),
// then loop through the artist-submitted files (if any).

if ( $_FILES && $_FILES['image']['name'] && $block_id && is_numeric($block_id) ) {
	foreach ( $_FILES['image']['name'] as $key => $image_file_name ) {

		$can_continue = FALSE;

		$tmp_name = $_FILES['image']['tmp_name'][$key];

		$type = $_FILES['image']['type'][$key];

		if ( $allowed_image_types && $type && is_image_type($type,$allowed_image_types))
		{
			$can_continue = TRUE;
		}
		elseif ( $type )
		{
			$alert_output .= $message->alert_dialog('I couldn’t upload the image. It doesn’t look like a PNG, GIF, JPG, JPEG or SVG.');
		}

		switch ( $_FILES['image']['error'][$key])
		{
			case 1:
				$alert_output .= $message->alert_dialog('I couldn’t upload an image that exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit. <a href="http://getgrawlix.com/docs/'.DOCS_VERSION.'/image-optimization">Learn about image optimization</a>.');
				break;
			case 3:
				$alert_output .= $message->alert_dialog('Something interrupted the file upload. Please try again.');
				break;
			case 6:
				$alert_output .= $message->alert_dialog('The server’s “tmp” folder is missing. Please contact your web host.');
				break;
		}

		// “Upload_file” is where we put it relative to the folder.
		// “Web file path” is its offical, absolute location for the public-facing website.
		if ( $tmp_name && $can_continue === TRUE ) {
			$uploadfile = '../'.DIR_STATIC_IMG . $image_file_name;
			$web_file_path = '/'.DIR_STATIC_IMG . $image_file_name;

			// Put the file in its new home.
			if (move_uploaded_file($tmp_name, $uploadfile)) {
				$data = array(); // reset
				$data['image'] = $web_file_path;

				$db -> where('id', $block_id);
				$success = $db -> update('static_content', $data);

			} else {
				$alert_output .= $message->alert_dialog('I couldn’t upload an image.');
			}
		}
	}
}



// ! Display logic

// Get all relevant info about this block.
if ( $block_id && is_numeric($block_id) ) {

	$cols = array(
		'page_id',
		'sort_order',
		'title',
		'url',
		'image',
		'content',
		'pattern'
	);
	$db->where('id',$block_id);
	$block_info = $db->getOne('static_content', null, $cols);

}



// ! Pattern select

// Which theme directory does this site use?
$theme_directory = get_current_theme_directory($db);

if (!$theme_directory || $theme_directory === FALSE)
{
	$alert_output .= $message->alert_dialog('I couldn’t determine the <a href="./site.theme-manager.php">site’s theme</a>, and so can’t find any theme pattern files.');
}


// Scan the current theme for pattern files in case the user
// renamed some or created new ones.
if ( $theme_directory )
{
	$pattern_order_list = array('(None)');
	$file_list = scandir('../themes/'.$theme_directory);
	if($file_list)
	{
		foreach($file_list as $filename)
		{
			if ( substr($filename,0,8) == 'pattern.')
			{
				$filename = explode('.',$filename);
				$pattern_order_list[$filename[1]] = str_replace('-',' ',$filename[1]);
			}
		}
	}
}

// Build a list to let the artist chooses a pattern for this static page.
if ( $pattern_order_list )
{
	$pattern_select = build_select_simple('pattern',$pattern_order_list, $block_info['pattern'],'width:200px');
}
else {
	if ( !$theme_directory )
	{
		$theme_directory = '(unknown)';
	}
	$order_output = 'I couldn’t find any <a href="http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/static-patterns">pattern files</a> in the /themes/'.$folder_name.' folder.';
}





if ( $block_info || $page_id )
{

	if ( $page_info['title'] ) {
		$page_title = $page_info['title'];
	}
	else {
		$page_title = 'Untitled';
	}

	$title_output  = '<label for="title">Title</label>'."\n";
	$title_output .= '<input type="text" name="title" id="title" maxlength="64" value="'.$block_info['title'].'" style="max-width:20rem">'."\n";

	if ( strtolower($block_info['title']) != 'freeform' )
	{
		$url_output  = '<label for="title">External URL</label>'."\n";
		$url_output .= '<input type="text" name="url" id="url" value="'.$block_info['url'].'">'."\n";

		$pattern_output  = '<label for="title">Pattern</label>'."\n";
		$pattern_output .= $pattern_select."\n";

		$content_output  = '<label for="title">Body</label>'."\n";
		$content_output .= '<textarea name="content" id="content" rows="15">'.$block_info['content'].'</textarea>'."\n";

		if ( $block_info['image'] && $block_info['image'] != '' )
		{
			$image_output .= '<img src="'.$milieu_list['directory']['value'].$block_info['image'].'" alt="image" /><br/>'."\n";
			$image_output .= '&nbsp;<p><a href="?remove_image=1&amp;block_id='.$block_id.'" class="btn secondary delete" name="submit" type="submit" value="save"/><i></i>Remove image</a></p>'."\n";
		}

		$max = ini_get( 'upload_max_filesize' ).'B maximum';

		if ($block_info['image'] && $block_info['image'] != '')
		{
			$image_output .= '<p><br/><label for="title">Upload a different image ('.$max.')</label>'."\n";
		}
		else
		{
			$image_output .= '<p><br/><label for="title">Upload an image ('.$max.')</label>'."\n";
		}
		$image_output .= ' <input type="file" id="image" name="image[]"></p>';

	}
	else
	{
		$content_output  = '<label for="title">Body</label>'."\n";
		$content_output .= '<textarea name="content" id="content" rows="40">'.$block_info['content'].'</textarea>'."\n";
	}


}



if ( $block_id )
{
	$view->page_title('Edit content block: '.$block_info['title']);
	$view->tooltype('sttc');
	$view->headline('Content block <span>'.$block_info['title'].'</span>');

	$link->url('sttc.page-edit.php?page_id='.$block_info['page_id']);
	$link->tap('Back to page');
	$action_output = $link->text_link('back');
}
elseif ( $page_id )
{
	$view->page_title('New content block');
	$view->tooltype('sttc');
	$view->headline('New content block');

	$link->url('sttc.page-edit.php?page_id='.$page_id);
	$link->tap('Back to page');
	$action_output = $link->text_link('back');
}


$view->action($action_output);


$form->multipart(true);
$form->send_to('sttc.block-edit.php');

$view->group_css('Layout');
$view->group_h2('Content');
$view->group_instruction('Information that people came to see. The body field supports <a href="https://daringfireball.net/projects/markdown/">Markdown</a> and most HTML.');
$view->group_contents($title_output . $content_output);
$final_meta_output  = $view->format_group().'<hr />';


if ( $url_output )
{
	$view->group_css('Layout');
	$view->group_h2('Link');
	$view->group_instruction('URL people will go to when they click a certain bit of text or image, depending on how the pattern uses links.');
	$view->group_contents($url_output);
	$final_content_output = $view->format_group().'<hr />';
}


if ( $pattern_output )
{
	$view->group_css('Layout');
	$view->group_h2('Pattern');
	$view->group_instruction('Choose a HTML arrangement for this particular block, overriding the page’s default pattern.');
	$view->group_contents($pattern_output);
	$final_pattern_output = $view->format_group().'<hr />';
}


if ( $image_output )
{
	$view->group_css('Layout');
	$view->group_h2('Image');
	$view->group_instruction('Graphic associated with this content block.');
	$view->group_contents($image_output);
	$final_image_output = $view->format_group().'<hr/>';
}


$hidden_fields  = '<input type="hidden" name="block_id" value="'.$block_id.'">'."\n";
$hidden_fields .= '<input type="hidden" name="page_id" value="'.$page_id.'">'."\n";



// ! Display


$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $form->open_form();
$output .= $hidden_fields;
$output .= $final_meta_output;
$output .= $final_url_output;
$output .= $final_content_output;
$output .= $final_image_output;
$output .= $final_pattern_output;

print($output);
print('<button class="btn primary save right" name="submit" type="submit" value="save"/><i></i>Save</button>');


$output  = $form->close_form();
$output .= $view->close_view();
print($output);
