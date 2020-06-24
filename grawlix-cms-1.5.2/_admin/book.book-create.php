<?php

// ! Setup


require_once('panl.init.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;

$view-> yah = 2;

$var_list = array(
	array('title','string'),
	array('description','string'),
	array('url','string')
);

if ( $var_list ) {
	foreach ( $var_list as $key => $val ) {
		${$val[0]} = register_variable($val[0],$val[1]);
	}
}


$max = $db->getOne('book','MAX(sort_order) AS max');
if ($max && $max['max'])
{
	$sort_order = $max['max'] + 1;
}
else
{
	$sort_order = 1;
}



// ! Updates

// Don’t allow illegal characters.
if ($url)
{
	$url = str_replace(' ', '-', $url);
	$url = str_replace('?', '', $url);
	$url = str_replace('&', 'and', $url);
	$url = str_replace('#', '-', $url);
	$url = str_replace('/', '-', $url);
	$url = str_replace('@', '-', $url);
	$url = str_replace('%', '-', $url);
	$url = str_replace('^', '-', $url);
	$url = str_replace('*', '-', $url);
	$url = str_replace('(', '', $url);
	$url = str_replace(')', '', $url);
	$url = str_replace('\\', '-', $url);

	// Put back the initial slash. Hmm.
	$url = '/'.substr($url, 1);
}



if ($_POST && $title)
{
	$default_options = <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<book version="1.1"><rss><option>image</option><option>number</option><option>blog</option><option>transcript</option></rss><archive><behavior>single</behavior><structure>v1.4</structure><chapter><layout>grid</layout><option>title</option><option>image</option></chapter><page><layout>list</layout><option>title</option></page></archive></book>

EOL;

	// Without this, the desc results in “0”.
	$description ? $description : $description = '';

	// What number is the last item in the menu?
	$last_menu = $db->getOne('path','MAX(sort_order) AS highest');
	if ($last_menu)
	{
		$last_menu = $last_menu['highest'];
	}
	else
	{
		$last_menu = 0;
	}

	$data = array(
		'title' => $title,
		'description' => $description,
		'options' => $default_options,
		'sort_order' => $sort_order,
		'date_created' => $db->now()
	);
	$new_book_id = $db->insert('book', $data);

	$data = array(
		'title' => $title,
		'url' => $url,
		'rel_id' => $new_book_id,
		'rel_type' => 'book',
		'sort_order' => $last_menu + 1,
		'in_menu' => 1,
		'edit_path' => 1
	);

	$new_path_id = $db->insert('path', $data);

	$data = array(
		'title' => $title.' archive',
		'url' => $url.'/archive',
		'rel_id' => $new_book_id,
		'rel_type' => 'archive',
		'sort_order' => $last_menu + 2,
		'in_menu' => 1,
		'edit_path' => 1
	);

	$new_archive_id = $db->insert('path', $data);

	header('location:book.view.php?created=1&book_id='.$new_book_id);
	die();
}

if ($_POST && !$title)
{
	$alert_output = $message->alert_dialog('A title is required.');
}


// ! Display logic






$url ? $url : $url = '/comic'.$sort_order;


$title_output = <<<EOL
<label for="title">Title</label>
<input type="text" name="title" id="title" value="$title"/>

<label for="description">Synopsis</label>
<input type="text" name="description" id="description" value="$description"/>

<label for="url">URL</label>
<input type="text" name="url" id="url" value="$url"/>

<button class="btn primary save" name="submit" type="submit" value="create"><i></i>Create</button>

EOL;



//$view->action($action_output);



$content_output .= '<form accept-charset="UTF-8" action="book.book-create.php" method="post" enctype="multipart/form-data">'."\n";
$content_output .= '	<input type="hidden" name="grlx_xss_token" value="'.$_SESSION['admin'].'"/>'."\n";


$view->group_css('page');
$view->group_h3('Metadata');
$view->group_instruction('Information about this new book.');
$view->group_contents($title_output);
$content_output .= $view->format_group()."\n";






// ! Display


$view->page_title('Comic book creator');
$view->tooltype('page');
$view->headline('Create a new book');
$view->action($action_output);

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;

print($output);

?>


<?=$content_output ?>

<?php
$view->add_jquery_ui();
$view->add_inline_script($js_call);
print($view->close_view());
?>
