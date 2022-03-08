<?php

/* ! Setup * * * * * * * */

require_once('panl.init.php');

$update = 'upgrade-to-1.3.php';

$alert_output = '';
if ( file_exists($update) ) {
	$message = new GrlxAlert;
	$alert_output = $message->info_dialog('Welcome to '.GRAWLIX_VERSION.'!<br /><br />You should <a href="'.$update.'">update your database</a> now.');
}


/* ! Build * * * * * * * */

$view = new GrlxView;
$fileops = new GrlxFileOps;
$link = new GrlxLink;
$fileops->db = $db;
$view-> yah = 99;

$view->page_title('Grawlix panel');
$view->tooltype('panl');
$view->headline('Grawlix panel');

$db-> where('date_publish >= NOW()');
$db-> orderBy('sort_order','ASC');
$id_info = $db-> getOne('book_page','id');

if ( !empty($id_info) ) {
	$comic_page = new GrlxComicPage($id_info['id']);
}
if ( isset($comic_page) ) {
	$image = reset($comic_page-> imageList);
	$link-> url('book.page-edit.php?page_id='.$comic_page-> pageID);
	$link-> tap($comic_page-> pageInfo['title']);
	$next_output  = '<p>'.$link-> paint().'</p>'."\n";
	$link-> tap('<img src="'.$image['url'].'" alt="" />');
	$next_output .= '<p>'.$link-> paint().'</p>'."\n";
}
else {
	$next_output = '<p><a href="book.view.php">Nothing’s coming up.</a></p>'."\n";
}


//TODO: New documentation!
$docs_link = 'http://www.getgrawlix.com/docs/'.DOCS_VERSION.'/';

$primary_output = <<<EOL
<div class="row">
<div class="medium-5 columns">
	<p><a href="book.page-create.php" class="btn primary" style="width:100%">Make a comic page</a></p>
	<p><a href="sttc.page-new.php" class="btn secondary" style="width:100%">Make a static page</a></p>
	<p><a href="site.config.php" class="btn secondary" style="width:100%">Set your name, copyright, etc</a></p>
	<p><a href="site.theme-manager.php" class="btn secondary" style="width:100%">Install or choose a theme</a></p>
</div>
</div>


EOL;

$tools_set_output = <<<EOL
<div class="row">
<div class="medium-4 columns">
<ul>
	<li><a href="book.archive.php">Archives</a></li>
	<li><a href="book.edit.php">Book info</a></li>
	<li><a href="book.view.php">Comic pages</a></li>
	<li><a href="site.config.php">Copyright</a></li>
	<li><a href="diag.health-check.php">Diagnostics</a></li>
	<li><a href="site.config.php">General settings</a></li>
	<li><a href="site.config.php">Home page</a></li>
	<li><a href="site.link-list.php">Link list</a></li>
	<li><a href="user.config.php">Login/password</a></li>
</ul>
</div>
<div class="medium-4 columns">
<ul>
	<li><a href="marker-type.list.php">Marker types</a></li>
	<li><a href="marker.list.php">Marker list</a></li>
	<li><a href="book.page-create.php">New comic page</a></li>
	<li><a href="sttc.page-new.php">New static page</a></li>
	<li><a href="xtra.social.php">Sharing</a></li>
	<li><a href="site.nav.php">Site-wide navigation menu</a></li>
	<li><a href="sttc.page-list.php">Static (non-comic) pages</a></li>
	<li><a href="site.theme-manager.php">Themes</a></li>
</ul>
</div>
<div class="medium-4 columns">&nbsp;<!-- more to come --></div>
</div>

EOL;

$ref_set_output = <<<EOL
<div class="row">
<div class="medium-12 columns">
<ul>
  <!--<li><a href="$docs_link">Read the documentation</a></li>-->
  <li><a href="https://github.com/Ayemae/Grawlix-Webcomic-CMS">Get help on GitHub</a></li>
</ul>
</div>
</div>

EOL;


// Group
$view->group_h2('Get going');
$view->group_instruction('Start making your webcomic.');
$view->group_contents($primary_output);
$content_output = $view->format_group().'<hr/>'."\n";

$view->group_h2('Tools');
$view->group_instruction('Choose from the sections to the left, or browse these topics.');
$view->group_contents($tools_set_output);
$content_output .= $view->format_group().'<hr/>'."\n";

$view->group_h2('Help');
$view->group_instruction('Learn about the Grawlix CMS or get assistance using the system.');
$view->group_contents($ref_set_output);
$content_output .= $view->format_group()."\n";

/*
$view->group_h2('Coming up');
$view->group_instruction('The next comic will be …');
$view->group_contents($next_output);
$content_output .= $view->format_group().'<hr/>'."\n";
*/


/* ! Display * * * * * * * */

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
$output .= $content_output;
$output .= $view->close_view();
print($output);


