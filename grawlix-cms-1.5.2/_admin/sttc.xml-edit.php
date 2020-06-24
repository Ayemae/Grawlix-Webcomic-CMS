<?php

require_once('panl.init.php');
require_once('lib/htmLawed.php');

// Be optimistic and assume the page’s content uses structured data.
$mode = 'xml';

// Number of new items we let the artist create at once.
$limit = 1;

// Restrict the XML to only certain types of elements within <item>s.
$allowed_container_types = array (
	'h' => 'heading',
	'i' => 'image',
	'l' => 'link',
	't' => 'text',
	'f' => 'free'
);

// Labels for the form.
$label_list = array (
	'heading' => 'Heading',
	'image' => 'Image',
	'link' => 'Destination URL',
	'text' => 'Text block',
	'free' => 'Freeform'
);

// What it sounds like.
$allowed_sttc_file_types = array ('gif','png','jpg','jpeg','svg');

$variable_list = array('page_id','new_title','new_description','old_title','xml_format','function','msg','pattern_id','url','layout_id');
if ( $variable_list ) {
	foreach ( $variable_list as $val ) {
		$$val = register_variable($val);
	}
}

// Hold it — no ID, no entrance.
if ( !$page_id ) {
	header('location:sttc.page-list.php');
	die();
}
if ( $page_id && !is_numeric($page_id))
{
	header('location:sttc.page-list.php');
	die();
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


/*****
 * ! Updates
 */

// This comes from sttc.xml-new.php.
if ( $msg == 'created' ) {
	$link1 = new GrlxLinkStyle;
	$link1-> url('sttc.xml-new.php');
	$link1-> tap('Create another new page');

	$link-> url('site.nav.php');
	$link-> tap('Add this page to the site’s menu');
	$alert_output .= $message->success_dialog('Empty page ready to go. Add your content below.</li>');
}


// ! Upload images
// If the static images’ folder exists (that’s $folder_check),
// then loop through the artist-submitted files (if any).

if ( !$alert_output && $_FILES && $_FILES['item']['name'] ) {
	foreach ( $_FILES['item']['name'] as $key => $image_file_name ) {

		// Assume this upload is invalid until proven otherwise.
		$can_continue = FALSE;

		// Get the image’s official filename.
		$image_file_name = $image_file_name['image'];

		// Get the image’s temporary serialized name.
		$tmp_name = $_FILES['item']['tmp_name'][$key]['image'];

		// What size and kind of image is this?
		$type = $_FILES['item']['type'][$key]['image']; // Really oughta check against valid image types.

		if ( $allowed_image_types && $type && is_image_type($type,$allowed_image_types))
		{
			$can_continue = TRUE;
		}
		elseif ( $type )
		{
			$alert_output .= $message->alert_dialog('I couldn’t upload the image. It doesn’t look like a PNG, GIF, JPG, JPEG or SVG.');
		}

		// Also check for other errors, like the server’s file size limit.
		// You’d be surprised what I’ve seen people try to upload.
		switch ( $_FILES[item][error][$key]['image'])
		{
			case 1:
				$alert_output .= $message->alert_dialog('I couldn’t upload an image that exceeded the server’s '.(ini_get( 'upload_max_filesize' )).'B file size limit. <a href="http://getgrawlix.com/docs/'.DOCS_VERSION.'/image-optimization">Learn about image optimization</a>.');
				break;
			case 3:
				// Handled in v2
				break;
			case 4:
				// Handled in v2
				break;
		}

// Display the file size limit
// echo ini_get( 'upload_max_filesize' );

		// “Upload_file” is where we put it relative to the folder.
		// “Web file path” is its offical, absolute location for the public-facing website.
		if ( $tmp_name && $can_continue === TRUE ) {
			$uploadfile = '../'.DIR_STATIC_IMG . $image_file_name;
			$web_file_path = DIR_STATIC_IMG . $image_file_name;

			// Put the file in its new home.
			if (move_uploaded_file($tmp_name, $uploadfile)) {
			} else {
				$alert_output .= $message->alert_dialog('I couldn’t upload an image.');
			}
		}
	}
}

// ! Write items’ XML
if ( $_POST['item'] && $page_id ) {

	// quick and dirty check to see how many items
	$check = count($_POST['item']) - 1;
	if ( $check <= 1 ) {
		// If there's only one item then no point in using a grid
		$layout_id = 'list';
	}

	// “default” matches the pattern filenames, e.g. /assets/patterns/hilt.default.php
	$pattern_id = register_variable('pattern_id');
	if ( !$pattern_id || $pattern_id == NULL || $pattern_id == '' )
	{
		$pattern_id = 'default';
	}

	// Rebuild the XML file from scratch.
	$save_xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$save_xml .= '<page version="1.1">'."\n";

	if ( $xml_format ) {
		$expected_field_set = str_split($xml_format);
	}
	$save_xml .= "\t<options>\n";
	$save_xml .= "\t\t<pattern>$pattern_id</pattern>\n";
	$save_xml .= "\t\t<layout>$layout_id</layout>\n";
	$save_xml .= "\t\t<function>$function</function>\n";
	if ( $expected_field_set ) {
		foreach ( $expected_field_set as $val ) {
			$save_xml .= "\t\t<option>$allowed_container_types[$val]</option>\n";
		}
	}
	$save_xml .= "\t</options>\n\t<content>\n";



	// Keep track of how many items we’re saving.
	$item_saved_count = 0;

//	$save_xml .= "\t".'<content>'."\n";

	foreach ( $_POST['item'] as $item_id => $item_contents ) {

		// A dash of extra sanitization.
//		$item_contents['heading'] = htmlspecialchars($item_contents['heading']);
//		$item_contents['text'] = htmlspecialchars($item_contents['text']);
		$item_contents['heading'] = $item_contents['heading'];
		$item_contents['text'] = $item_contents['text'];
		$item_contents['heading'] = str_replace('javascript', '', $item_contents['heading']);
		$item_contents['text'] = str_replace('javascript', '', $item_contents['text']);
		// Make sure this page’s items have only what they need, e.g. headings or images.
		$valid_type = FALSE; // Guilty until proven innocent.
		if ( $allowed_container_types ) {
			foreach ( $allowed_container_types as $allowed_type ) {
				if ( $item_contents[$allowed_type] ) {
					$valid_type = TRUE;
				}
			}
		}

		if ( $valid_type == TRUE ) {

			// Keep track of how many items we’re saving.
			$item_saved_count++;

			// If the artist uploaded a pic for this item, then add it to the mix.
			if ( $_FILES['item']['name'][$item_id]['image'] ) {
				$item_contents['image'] = '/'.DIR_STATIC_IMG . $_FILES['item']['name'][$item_id]['image'];
			}

			// If the artist *didn’t* add one, but there was a hidden “original” value, then use that instead.
			elseif ( in_array('i', $expected_field_set) ) {
				$item_contents['image'] = $item_contents['original_image']; // Uh-huh, that’s what I thought.
			}

			// Delete the original value to keep from adding <original_image> to <item> elements.
			unset($item_contents['original_image']);

			// Begin to build this XML item (child of the root element).
			$save_xml .= "\t\t".'<item>'."\n\t\t\t".'<pattern>'.'</pattern>'."\n";

			foreach ( $item_contents as $this_container_type => $this_container ) {

				$this_container = trim ( $this_container );
				$save_xml .= "\t\t\t".'<'.$this_container_type.'><![CDATA['.$this_container.']]></'.$this_container_type.'>'."\n";

			}
			$save_xml .= "\t\t".'</item>'."\n";
		}
	}

	// Finish off the XML.
//	$save_xml .= "\t".'</content>'."\n";
	$save_xml .= '</content></page>';


	// ! Update the database

	// Prep the database update statement.
	$data = array (
		'title' => $new_title,
		'description' => $new_description,
		'options' => $save_xml,
		'date_modified' => $db->now()
	);


	$db->where('id',$page_id);
	$success_item = $db->update('static_page', $data);

	$link-> url('sttc.page-list.php');
	$link-> tap('Return to the page list');

	$static = new GrlxStaticPage($page_id);
	$static-> getInfo();

	$alert_output .= $message->success_dialog('Items saved. <ul><li>'.$link-> paint().'</li><li><a href="'.$static-> info['url'].'">View this page live</a></li></ul>');
}

// If it doesn’t look like XML, then treat the page’s content as
// “raw”, unstructured, unruly text. But go ahead and save it.
elseif ( $_POST['raw_content'] && $page_id ) {

//	$xml_content = htmlspecialchars($_POST['raw_content']);
	$xml_content = $_POST['raw_content'];
	$xml_content = str_replace('javascript', '', $xml_content);

	$data = array (
		'title' => $new_title,
		'description' => $new_description,
		'options' => $xml_content,
		'date_modified' => $db->now()
	);

	$db->where('id',$page_id);
	$success_item = $db->update('static_page', $data);
	$alert_output .= $message->success_dialog('Freeform content saved. '.$link-> paint('grlx page list'));
}


/*****
 * ! Display logic
 */

// Get all relevant info about this page.
if ( $page_id && is_numeric($page_id) ) {
	$static = new GrlxStaticPage($page_id);
	$static-> getInfo();
}

if ( $static && $static-> info ) {

	// Reality check: Does this look like XML?
	// No? Must be freeform content.
	if ( substr($static-> info['options'],0,5) != '<?xml' ) {
		$mode = 'plaintext';
	}
	else {
		// ! Read current XML
		$args['stringXML'] = $static->info['options'];
		$static_xml = new GrlxXML_Static($args);

		// What does this page contain?
		$item_objects = $static_xml-> getItemSets('/content');

		// What is this page *allowed* to contain?
		$manifest = $static_xml-> getClones('/options','option');

		// ! Interpret the manifest of allowed elements
		if ( $manifest ) {
			$xml_format_list = array();
			foreach ( $manifest as $key => $val ) {
				$xml_format_list[] = substr($val,0,1);
			}
			asort($xml_format_list);
			$xml_format = implode('',$xml_format_list);
		}

		// Loop through each item …
		if ( $item_objects && $manifest ) {
			foreach ( $item_objects as $number => $item ) {

				// …building input elements for each allowed datum type.
				foreach ( $manifest as $type ) {
					$value = (string)$item[$type];
					$f = 'build_'.$type.'_field';

					// Build the damn element.
					if ( function_exists($f)) {
						$form_output .= build_quick_label($number,$label_list[$type],$type);
						$form_output .= $f($number,$value);
					}
				}
				$form_output .= '<hr class="sub"/>'."\n";
			}
		}
	}
}

// ! Build sets of “new” fields
if ( $manifest ) {

	for ( $i=$number+2; $i<=$number+$limit+1; $i++) {
		foreach ( $manifest as $type ) {
			$f = 'build_'.$type.'_field';

			if ( function_exists($f)) {
				$new_output .= build_quick_label($number,'New '.$label_list[$type],$type);
				$new_output .= $f($i);
			}
		}
	}
}

// Next we want to find all patterns that seem to match this page’s XML format.
// Heck, just get ’em all to start.
$pattern_option_list = $fileops-> get_dir_list('../'.DIR_PATTERNS);

// Now go through them and remove those that do *not* match the page’s XML format.
if ( $pattern_option_list && $xml_format ) {
	foreach ( $pattern_option_list as $id => $filename ) {

		// The first part of a pattern file’s name is its XML format (a.k.a. “HILT” combo). Compare that
		// to the page’s XML format.
		$this_pattern = explode('.', $filename);
		if ( $this_pattern[0] != $xml_format ) {
			unset($pattern_option_list[$id]);
		}

	}
}

$form->row_class('widelabel');

// If anything’s left over, loop through and make radio buttons
// to let the artist choose among the appropriate pattern files.
if ( $pattern_option_list && $static_xml ) {
	$i = 0;
	$page_pattern = $static_xml->getValue('/options/pattern');
	$page_layout = $static_xml->getValue('/options/layout');
	$function = $static_xml->getValue('/options/function');

	$pattern_select_output = '<ul class="block-grid-small-12">';

	foreach ( $pattern_option_list as $this_pattern ) {
		$i++;
		$this_pattern = explode('.',$this_pattern);
		$parts = $this_pattern[0];
		$pattern = $this_pattern[1];
		$ext = $this_pattern[2];

		// Ignore the images. Look to the PHP files.
		// Sample file name: hilt.default.php, not hilt.default.svg
		if ( !in_array($ext, $allowed_sttc_file_types) && !in_array('xml', $this_pattern) ) {

			// Set up the thumbnail file.
			$thumbnail = '../'.DIR_PATTERNS.$parts.'.'.$pattern.'.svg';

			// Does this page use the pattern?
			if ( $pattern == $page_pattern ) {

				$pattern_select_output .= '
<li>
	<label for="pattern-'.$i.'">'.$this_label.'<br/>
		<img src="'.$thumbnail.'" alt="'.$pattern.'" style="width:100px"/>
	</label>
	<input type="radio" name="pattern_id" id="pattern-'.$i.'" value="'.$pattern.'" checked="checked"/>
</li>'."\n";
			}
			else {
				$pattern_select_output .= '
<li>
	<label for="pattern-'.$i.'" style="opacity: 0.5"><br/>
		<img src="'.$thumbnail.'" alt="'.$pattern.'" style="width:100px"/>
	</label>
	<input type="radio" name="pattern_id" value="'.$pattern.'" id="pattern-'.$i.'"/>
</li>'."\n";
			}
		}
	}
	$pattern_select_output .= '</ul>'."\n";
}
else {
	$pattern_select_output = 'No patterns available for this file type.';
}
$pattern_select_output = '
<div class="'.$form->row_class.'">
	<div>
		<label>Items’ pattern</label>
	</div>
<div>'."\n".$pattern_select_output; //.'</div></div>';


$layout_options[] = array (
	'id' => 'list',
	'title' => 'List'
);
$layout_options[] = array (
	'id' => 'grid',
	'title' => 'Grid'
);

$sl-> setName('layout_id');
$sl-> setCurrent($page_layout);
$sl-> setList($layout_options);
$sl-> setStyle('width:6rem');
$sl-> setValueID('id');
$sl-> setValueTitle('title');

$layout_select_output = '
<div class="'.$form->row_class.'">
	<div>
		<label>Overall page layout</label>
	</div>
	<div>'.$sl-> buildSelect().'</div></div>';

if ( $static-> info['title'] ) {
	$page_title = $static-> info['title'];
}
else {
	$page_title = 'Untitled';
	$static-> info['title'] = 'Untitled';
}



$link-> url('site.nav.php');
$link-> tap($static-> info['url']);
$path_link = $link-> paint();

$path_link_output = <<<EOL
<div class="row form widelabel">
	<div>
		<label>URL</label>
	</div>
	<div class="plaintext">$path_link</div></div>
EOL;


$view->page_title('Edit static page: '.$page_title);
$view->tooltype('sttc');
$view->headline('Static page <span>'.$page_title.'</span>');

$link->url('sttc.page-list.php');
$link->tap('Back to list');
$action_output = $link->text_link('back');

$link->url('..'.$static-> info['url']);
$link->tap('View live page');
$action_output .= $link->button_secondary('view');

$view->action($action_output);

$form->multipart(true);
$form->send_to('sttc.xml-edit.php');

$form->input_hidden('page_id');
$form->value($page_id);
$hidden_fields = $form->paint();

$form->input_hidden('function');
$form->value($function);
$hidden_fields .= $form->paint();

$form->input_hidden('xml_format');
$form->value($xml_format);
$hidden_fields .= $form->paint();

$form->input_title('new_title');
$form-> size('10');
if ( $static->info['title'] == 'Home' ) {
	$form->readonly(true);
}
$form->value($static-> info['title']);
$settings_form = $form->paint();

$form->input_description('new_description');
$form->value($static-> info['description']);
$settings_form .= $form->paint();

$settings_form .= $path_link_output;

$layout_form  = $layout_select_output;

$layout_form .= $pattern_select_output;

$view->group_css('sttc');
$view->group_h2('Settings');
$view->group_instruction('General information for this static page.');
$view->group_contents($settings_form);
$settings_output = $view->format_group().$form->form_buttons().'<hr />';

if ( $mode != 'plaintext' ) {
  $view->group_css('sttc');
  $view->group_h2('Layout');
  $view->group_instruction('How items on this static page are arranged.');
  $view->group_contents($layout_form);
  $settings_output .= $view->format_group().$form->form_buttons().'<hr />';
}

if ( $mode == 'plaintext' ) {
	$form_output .= '<p>Freeform content:</p>';
	$form_output .= '<textarea name="raw_content" rows="10" style="height:12rem">'.$static-> info['options'].'</textarea>';
//	$form_output .= '<p><a href="#to-do" target="_blank">Get sample XML</a></p>';
}
$form_output .= $form->form_buttons();

$view->group_h2('Items');
$view->group_contents($form_output);
$view->group_instruction('Stuff the readers see.');
$content_output = $view->format_group();

if ( $new_output ) {
	$view->group_h3('Add more');
	$new_output .= $form->form_buttons();
	$view->group_contents($new_output);
	$new_output = '<hr />'.$view->format_group();
}


/*****
 * Display
 */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $form->open_form();
$output .= $hidden_fields;
$output .= $settings_output;
$output .= $content_output;
$output .= $new_output;

print($output);

$output  = $form->close_form();
$output .= $view->close_view();
print($output);
