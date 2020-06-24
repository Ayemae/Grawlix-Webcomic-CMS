<?php

/* ! Setup * * * * * * * */

require_once('panl.init.php');

$view = new GrlxView;
$view-> yah = 4;

$form = new GrlxForm;
$form->send_to($_SERVER['SCRIPT_NAME']);
$form->row_class = 'row arcv';

// Load up the settings/info for building the archive
$args['infoXML'] = 1;
$infoXML = new GrlxXML_Book($args);
unset($args);

$book_id = $_POST['book_id'];
$book_id ? $book_id : $book_id = $_GET['book_id'];
$book_id ? $book_id : $book_id = $_SESSION['book_id'];

// A book ID is required. If you don’t have one at this point, then get the “first” book in the database.
if ( !$book_id ) {
	$db->orderBy ('sort_order,id','ASC');
	$result = $db->getOne ('book','id');
	if ( $result ) {
		$book_id = $result['id'];
	}
	else {
		die('No books in the database.');
	}
}


if ( $_POST['submit'] ) {
	$args['archiveNew'] = array(
		'behavior' => clean_text($_POST['behavior']),
		'structure' => clean_text($_POST['structure']),
		'chapter'  => $_POST['chapter'],
		'page'     => $_POST['page']
	);

	// Sanitized for your protection.
	if ( $args['chapter']['option'] )
	{
		foreach ( $args['chapter']['option'] as $key => $val )
		{
			$args['chapter']['option'][$key] = clean_text($val);
		}
	}
	
	if ( $args['page']['option'] )
	{
		foreach ( $args['page']['option'] as $key => $val )
		{
			$args['page']['option'][$key] = clean_text($val);
		}
	}
	

	// These can’t be empty
	if ( !array_key_exists('option',$args['archiveNew']['chapter']) ) {
		$args['archiveNew']['chapter']['option'] = 'number';
	}
	if ( !array_key_exists('option',$args['archiveNew']['page']) ) {
		$args['archiveNew']['page']['option'] = 'number';
	}
}

$args['bookID'] = $book_id;
$xml = new GrlxXML_Book($args);


/* ! Build * * * * * * * */


// Get some basic information about this book.
if ($book_id && is_numeric($book_id))
{
	$db->where('id',$book_id);
	$book_info = $db->getOne('book','title');
}


// ! Behavior
if ( $infoXML->archive['behavior'] ) {
	foreach ( $infoXML->archive['behavior'] as $info ) {
		$name = $info['name'];
		$xml->behavior == $name ? $check = ' checked="checked"' : $check = null;
		$behavior_output .= '<div>';
		$behavior_output .= '<h5>'.$info['title'].'</h5>';
		$behavior_output .= '<label class="option"><img src="'.$info['image'].'" alt="'.$name.'" />';
		$behavior_output .= '<p><input type="radio"'.$check.' name="behavior" value="'.$name.'"/>';
		$behavior_output .= $info['description'].'</p></label>';
		$behavior_output .= '</div>';
	}
	$behavior_output = $form->row_wrap($behavior_output);
}

// ! Structure

// HARDCODED so we don’t have to ask artists to update some obscure XML.
$infoXML->archive['structure'] = array (
	array (
		'name' => 'v1.3',
		'title' => 'Classic',
		'description' => 'Use HTML made for themes predating the Grawlix CMS v1.4.',
	),
	array (
		'name' => 'v1.4',
		'title' => 'Hierarchal',
		'description' => 'Use HTML made for Grawlix CMS v1.4 themes and later.',
	),
);

foreach ( $infoXML->archive['structure'] as $info ) {
	$name = $info['name'];
	$xml->structure == $name ? $check = ' checked="checked"' : $check = null;
	$structure_output .= '<div>';
	$structure_output .= '<h5>'.$info['title'].'</h5>';
	$structure_output .= '<label class="option">';
	$structure_output .= '<p><input type="radio"'.$check.' name="structure" value="'.$name.'"/>';
	$structure_output .= $info['description'].'</p></label>';
	$structure_output .= '</div>';
}
$structure_output = $form->row_wrap($structure_output);


// ! Layout

if ( $xml->layout && $infoXML->archive['chapter']['layout'] && $infoXML->archive['page']['layout'] ) {
	$layout_output  = '<div>';
	$layout_output .= '<h5>Markers</h5>';
	foreach ( $infoXML->archive['chapter']['layout'] as $info ) {
		$name = $info['name'];
		$title = ucfirst($name);
		$xml->layout['chapter'] == $name ? $check = ' checked="checked"' : $check = null;
		$layout_output .= '<label class="option"><img src="'.$info['image'].'" alt="'.$name.'" />';
		$layout_output .= '<p><input type="radio"'.$check.' name="chapter[layout]" value="'.$name.'"/>';
		$layout_output .= $title.'</p></label>';
	}
	$layout_output .= '</div>';
	$layout_output .= '<div>';
	$layout_output .= '<h5>Pages</h5>';
	foreach ( $infoXML->archive['page']['layout'] as $info ) {
		$name = $info['name'];
		$title = ucfirst($name);
		$xml->layout['page'] == $name ? $check = ' checked="checked"' : $check = null;
		$layout_output .= '<label class="option"><img src="'.$info['image'].'" alt="'.$name.'" />';
		$layout_output .= '<p><input type="radio"'.$check.' name="page[layout]" value="'.$name.'"/>';
		$layout_output .= $title.'</p></label>';
	}
	$layout_output .= '</div>';
	$layout_output = $form->row_wrap($layout_output);
}

if ( $xml->meta && $infoXML->archive['chapter']['option'] && $infoXML->archive['page']['option'] ) {

	// Yeeeah, let’s just sneak this one in there. Ahem.
	$infoXML->archive['page']['option'][] = 'image';

	$meta_output  = '<div>';
	$meta_output .= '<h5>Markers</h5>';

	// Yeeeah, let’s just sneak this one in there. Ahem.
	$infoXML->archive['chapter']['option'][] = 'image';

	foreach ( $infoXML->archive['chapter']['option'] as $key=>$info ) {
		$title = ucfirst($info);
		if ( $info && is_array($xml->meta['chapter']) && in_array($info,$xml->meta['chapter']))
		{
			$check = ' checked="checked"';
		}
		else
		{
			$check = null;
		}
		$meta_output .= '<label><input type="checkbox" name="chapter[option][]"'.$check.' value="'.$info.'"/>&emsp;'.$title.'</label>';
	}
	$meta_output .= '</div>';
	$meta_output .= '<div>';
	$meta_output .= '<h5>Pages</h5>';
	foreach ( $infoXML->archive['page']['option'] as $info ) {
		$title = ucfirst($info);
		in_array($info,$xml->meta['page']) ? $check = ' checked="checked"' : $check = null;
		$meta_output .= '<label><input type="checkbox" name="page[option][]"'.$check.' value="'.$info.'"/>&emsp;'.$title.'</label>';
	}
	$meta_output .= '</div>';
	$meta_output = $form->row_wrap($meta_output);
}

if ( $xml->saveResult == 'success' ) {
	$message = new GrlxAlert;
	$alert_output = $message->success_dialog('Changes saved.');
}

if ( $xml->saveResult == 'error' ) {
	$message = new GrlxAlert;
	$alert_output = $message->alert_dialog('Changes failed to save.');
}


/* ! Display * * * * * * * */

$view->page_title('Archives');
$view->tooltype('arcv');
if (is_file('book.list.php'))
{
	$view->headline('Archive settings <span>'.$book_info['title'].'</span>');
}
else
{
	$view->headline('Archive settings');
}
$form->input_hidden('book_id');
$form->value($book_id);
$hidden_book_info = $form->paint();

$view->group_css('arcv');
$view->group_h2('Behavior');
$view->group_instruction('Select how you want readers to navigate through your archives.');
$view->group_contents($behavior_output);
$content_output .= $view->format_group().'<hr/>';

$view->group_css('arcv');
$view->group_h2('Structure');
$view->group_instruction('Archives after v1.3 use a more streamlined HTML that may conflict with older themes. Choose which structure works with your theme’s CSS.');
$view->group_contents($structure_output);
$content_output .= $view->format_group().'<hr/>';

$view->group_h2('Layout');
$view->group_instruction('Select how you want to arrange information.');
$view->group_contents($layout_output);
$content_output .= $view->format_group().'<hr/>';

$view->group_h2('Metadata');
$view->group_instruction('Select the types of information to display.');
$view->group_contents($meta_output);
$content_output .= $view->format_group().'<hr/>';
$content_output .= $form->form_buttons();

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $form->open_form();
$output .= $hidden_book_info;
$output .= $content_output;
$output .= $form->close_form();
$output .= $view->close_view();
print($output);
