<?php

/* Artists use this script to configure a theme tone.
 */

/*****
 * Setup
 */
require_once('panl.init.php');
$view = new GrlxView;
$modal = new GrlxForm_Modal;
$link = new GrlxLinkStyle;
$message = new GrlxAlert;
$form = new GrlxForm;
//$theme = new GrlxXML_Theme;
//$img = new GrlxImage;
//$img-> db_new = $db;
$theme_id = numfunc_register_var('theme_id');
$tone_id = numfunc_register_var('tone_id');
$view-> yah = 9;



/*****
 * Updates
 */

// Save changes to the theme.
if ( $_POST && $theme_id )
{
	$variable_list = array('title','description','author','url','version');
	if ( $variable_list )
	{
		foreach ( $variable_list as $val )
		{
			$data[$val] = register_variable($val);
		}
	}

	$result = $db
		-> where('id', $theme_id)
		-> update('theme_list', $data);

	if ( $db-> count <= 0 ) {
		$alert_output .= $message->alert_dialog('Theme changes failed to save.');
	}
	else {
		$alert_output .= $message->success_dialog('Theme changes saved.');
	}
}







/*****
 * Display logic
 */

if ( $theme_id ) {
	$theme_info = $db
		->where('id', $theme_id)
		->getOne('theme_list',NULL);
}
// Make sure the URL, if any, has a prefix.
if ( ($theme_info['url'] !== null) && ($theme_info['url'] != 'None listed.') ) {
	if ( $theme_info['url'] && substr($theme_info['url'], 0, 7) != 'http://' ) {
		$theme_info['url'] = 'http://'.$theme_info['url'];
	}
}

// Scan for tone files.
if ($handle = opendir('../themes/'.$theme_info['directory'])) {
	while (false !== ($entry = readdir($handle)))

	{
		if (substr($entry,0,4) == 'tone' )
		{
			// Is this tone in the database?
			$check_tone = $db
				->where('options', $entry)
				->where('theme_id', $theme_id)
				->getOne('theme_tone',NULL);

			// If it’s not in the database, then add it.
			if ( !$check_tone )
			{
				$title = substr($entry,5,-4);
				$title = str_replace('_', ' ', $title);
				$data = array (
					'theme_id' => $theme_id,
					'title' => $title,
					'user_made' => 1,
					'options' => $entry,
					'date_created' => $db->now()
				);
				$db->insert('theme_tone', $data);
				$new_tone_list[] = $entry;
			}
		}
	}

	closedir($handle);
}

// Build a list of all tones for this theme
$cols = array('id', 'title', 'options');
$tone_list = $db
	-> where('theme_id', $theme_id)
	-> orderBy('title', 'ASC')
	-> get('theme_tone', NULL, $cols);

// Does each DB entry have a corresponding CSS file?
if ( $tone_list )
{
	foreach ( $tone_list as $key => $val )
	{
		if ( !is_file('../themes/'.$theme_info['directory'].'/'.$val['options']))
		{
			// No? Then remove it from the database.
			$db->where('id',$val['id']);
			$db->delete('theme_tone', 1);
			unset($tone_list[$key]);
		}
	}
}


if ( $tone_list )
{
	if ( count($tone_list) == 1 )
	{
		$tone_list_output = '<h3>One lonely tone found</h3>';
	}
	else
	{
		$tone_list_output = '<h3>'.count($tone_list).' tones found</h3>';
	}
	foreach ( $tone_list as $key => $val )
	{
		if ( $new_tone_list && in_array($val['options'], $new_tone_list) ) 
		{
			$tone_list_output .= '<p><strong>'.$val['title'].'</strong> ('.$val['options'].') — <strong>now installed!</strong></p>';
		}
		else
		{
			$tone_list_output .= '<p><strong>'.$val['title'].'</strong> ('.$val['options'].')</p>';
		}
	}
}

$meta_output = <<<EOL
<form action="site.theme-options.php" method="post">
	<div class="row">
		<div class="medium-4 columns">
			<label for="title">Title</label>
			<input type="text" name="title" id="title" value="$theme_info[title]"/>
		</div>
		<div class="medium-8 columns">
			<label for="description">Description</label>
			<input type="text" name="description" id="description" value="$theme_info[description]"/>
		</div>
	</div>
	<div class="row">
		<div class="medium-4 columns">
			<label for="author">Author</label>
			<input type="text" name="author" id="author" value="$theme_info[author]"/>
		</div>
		<div class="medium-4 columns">
			<label for="url">URL</label>
			<input type="text" name="url" id="url" value="$theme_info[url]"/>
		</div>
		<div class="medium-2 columns">
			<label for="version">Version</label>
			<input type="text" name="version" id="version" value="$theme_info[version]"/>
		</div>
		<div class="medium-2 columns">&nbsp;</div>
	</div>
	<div class="row">
		<div class="columns">
			<input type="submit" name="submit" value="save" class="btn secondary submit"/>
		</div>
	</div>
	<input type="hidden" name="theme_id" value="$theme_id"/>
	<input type="hidden" name="grlx_xss_token" value="$_SESSION[admin]"/>
</form>

EOL;



$view->page_title("Theme: $theme_info[title]");
$view->tooltype('tone');
$view->prepend_stylesheet('spectrum.css');
$view->headline("Theme <span>$theme_info[title]</span>");

$view->group_css('theme');
$view->group_h2('Metadata');
$view->group_instruction('Information about this theme.');
$view->group_contents($meta_output);
$meta_output = $view->format_group().'<hr/>';

$view->group_css('theme');
$view->group_h2('Tones');
$view->group_instruction('CSS files that modify this theme. To make a new tone, add a tone CSS file to the '.$theme_info['directory'].' directory. For example, tone.something.css would create the tone “something.”');
$view->group_contents($tone_list_output);
$tone_output = $view->format_group().'<hr/>';


$link->url('site.theme-manager.php');
$link->tap('Back to list');
$action_output = $link->text_link('back');
$view->action($action_output);






/*****
 * Display
 */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $modal->modal_container();
$output .= $meta_output;
$output .= $tone_output;
print($output);

print ( $view->close_view() );
