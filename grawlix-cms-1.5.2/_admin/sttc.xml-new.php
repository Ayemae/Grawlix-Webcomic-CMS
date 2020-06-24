<?php

/* Artists use this script to create and edit text pages.
 */

/*****
 * Setup
 */

require_once('panl.init.php');
require_once('lib/htmLawed.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;
$fileops = new GrlxFileOps;

$view-> yah = 5;


// Offer these to the artist.
$page_type_list[1] = array(
	'layout_type' => 'ht',
//	'content_type' => 'article',
	'label' => 'About the artist',
	'image' => '../'.DIR_PATTERNS.'ht.default.svg',
	'description' => 'Your bio, artist’s statement, or just who you want people to know you as.',
	'url' => '/about-artist'
);

$page_type_list[2] = array(
	'layout_type' => 'ht',
//	'content_type' => 'article',
	'function' => 'about-comic',
	'label' => 'About the comic',
	'image' => '../'.DIR_PATTERNS.'ht.default.svg',
	'description' => 'What you want new readers to know about your comic.',
	'url' => '/about-comic'
);
$page_type_list[3] = array(
	'layout_type' => 'hit',
//	'content_type' => 'list',
	'function' => 'cast',
	'label' => 'Cast',
	'image' => '../'.DIR_PATTERNS.'hit.default.svg',
	'description' => 'Name, quick bio and picture of each character.',
	'url' => '/comic-characters'
);
$page_type_list[4] = array(
	'layout_type' => 'hl',
//	'content_type' => 'list',
	'function' => 'links',
	'label' => 'Links',
	'image' => '../'.DIR_PATTERNS.'hl.default.svg',
	'description' => 'Simple title, optional descriptive text and URL.',
	'url' => '/favorite-links'
);
$page_type_list[5] = array(
	'layout_type' => 'ht',
//	'content_type' => 'list',
	'function' => 'faq',
	'label' => 'FAQ',
	'image' => '../'.DIR_PATTERNS.'ht.default.svg',
	'description' => 'Answers to questions you get asked often it’s worth a page.',
	'url' => '/frequently-asked-questions'
);
$page_type_list[6] = array(
	'layout_type' => 'free',
//	'content_type' => 'free',
	'function' => 'freeform',
	'label' => 'Freeform',
	'image' => '../'.DIR_PATTERNS.'free.svg',
	'description' => 'Add your own HTML. Anything goes.',
	'url' => '/new-page'
);
$page_type_list[7] = array(
	'layout_type' => 'ht',
//	'content_type' => 'article',
	'function' => 'welcome',
	'label' => 'Welcome new readers',
	'image' => '../'.DIR_PATTERNS.'ht.default.svg',
	'description' => 'Introduction to your comic.',
	'url' => '/new-readers-begin-here'
);
$page_type_list[8] = array(
	'layout_type' => 'hilt',
//	'content_type' => 'list',
	'function' => 'store',
	'label' => 'Store',
	'image' => '../'.DIR_PATTERNS.'hit.default.svg',
	'description' => 'Quick product teasers that you link to your ecommerce package.',
	'url' => '/store'
);


/*****
 * Updates
 */

if ( $_GET['id'] ) {
	$id = $_GET['id'];
	$layout_type_id = $page_type_list[$id]['layout_type'];
	$xml_source = '../'.DIR_PATTERNS.''.$layout_type_id.'.xml';

	if ( is_file($xml_source) ) {
		$starter = $fileops->read_file($xml_source);
	}
	else {
		$starter = null;
	}


	// Let’s add some custom default data.
	if ( $starter && $starter != null ) {
		switch ( $id ) {

			case 1:
				$starter = str_replace('{function}', $page_type_list[1]['function'], $starter);
				$starter = str_replace('{heading1}', 'All about the artist', $starter);
				$starter = str_replace('{text1}', '(Psst — tell us something about yourself here.)', $starter);
				$starter = str_replace('{heading2}', '', $starter);
				$starter = str_replace('{text2}', '', $starter);
				$starter = str_replace('{heading3}', '', $starter);
				$starter = str_replace('{text3}', '', $starter);
				break;

			case 2:
				$starter = str_replace('{function}', $page_type_list[2]['function'], $starter);
				$starter = str_replace('{heading1}', 'Welcome, new readers!', $starter);
				$starter = str_replace('{text1}', '(Psst — give us a quick story synopsis here.)', $starter);
				break;

			case 3:
				$starter = str_replace('{function}', $page_type_list[3]['function'], $starter);
				$starter = str_replace('{heading1}', 'First character name', $starter);
				$starter = str_replace('{heading2}', 'Second character name', $starter);
				$starter = str_replace('{heading3}', 'Third character name', $starter);
				$starter = str_replace('{text1}', 'A brief bio of character 1', $starter);
				$starter = str_replace('{text2}', 'A brief bio of character 2', $starter);
				$starter = str_replace('{text3}', 'A brief bio of character 3', $starter);
				break;

			case 4:
				$starter = str_replace('{function}', $page_type_list[4]['function'], $starter);
				$starter = str_replace('{heading1}', 'My most favorite comic', $starter);
				$starter = str_replace('{heading2}', 'Another cool comic', $starter);
				$starter = str_replace('{heading3}', 'And a third site I like', $starter);
				$starter = str_replace('{link1}', 'http://el-indon.com', $starter);
				$starter = str_replace('{link2}', 'http://evil-inc.com', $starter);
				$starter = str_replace('{link3}', 'http://www.getgrawlix.com', $starter);
				break;

			case 5:
				$starter = str_replace('{function}', $page_type_list[5]['function'], $starter);
				$starter = str_replace('{heading1}', 'A question?', $starter);
				$starter = str_replace('{text1}', 'And the answer.', $starter);
				$starter = str_replace('{heading2}', 'Another question?', $starter);
				$starter = str_replace('{text2}', 'Another answer.', $starter);
				$starter = str_replace('{heading3}', 'A third question?', $starter);
				$starter = str_replace('{text3}', 'Answers galore.', $starter);
				break;

			case 8:
				$starter = str_replace('{function}', $page_type_list[8]['function'], $starter);
				$starter = str_replace('{heading1}', 'Product 1', $starter);
				$starter = str_replace('{text1}', 'About this product.', $starter);
				$starter = str_replace('{link1}', 'www.your-store.com', $starter);
				$starter = str_replace('{heading2}', 'Product 2', $starter);
				$starter = str_replace('{text2}', 'About this product.', $starter);
				$starter = str_replace('{link2}', 'www.your-store.com', $starter);
				$starter = str_replace('{heading3}', 'Product 3', $starter);
				$starter = str_replace('{text3}', 'About this product.', $starter);
				$starter = str_replace('{link3}', 'www.your-store.com', $starter);
				break;
		}
	}

	$data = array (
		'title' => $page_type_list[$id]['label'],
	//	'content_type' => $page_type_list[$id]['content_type'],
		'options' => $starter,
//		'layout' => $page_type_list[$id]['repeat'].'-'.$layout_type_id.'-default',
//		'layout' => $layout_type_id.'-default',
		'date_created' => $db->now(),
		'date_modified' => $db->now()
	);
	$static_id = $db->insert('static_page', $data);
	if ( $static_id ) {
		if ( $static_id ) {
			$result = $db-> get ('path',null,'MAX(sort_order) AS max');
			$sort_order = $result[0]['max'] + 1;
			$sort_order ? $sort_order : $sort_order = 0;

      // Wait — does this URL exist in the database?
      $new_url = $page_type_list[$id]['url'];
      $db-> where ('url', $new_url);
      $maybe_existing = $db-> getOne('path',null,'url');

      if ( $maybe_existing && is_array($maybe_existing) ) {
        $new_url .= date('-h-i-s');
      }

			$data = array (
				'title' => $page_type_list[$id]['label'],
				'url' => $new_url,
				'rel_id' => $static_id,
				'rel_type' => 'static',
				'in_menu' => 1,
				'edit_path' => 1,
				'sort_order' => $sort_order
			);
			$db->insert('path', $data);
		}
		header('location:sttc.xml-edit.php?msg=created&page_id='.$static_id);
		die();
	}
}

function existing_url($db,$url_to_test){
  $db-> where ('url', $url_to_test);
  $result = $db-> getOne('path',null,'url');
  return $result;
}

function display_new_block($info,$id){
	$output = <<<EOL
						<div class="row">
							<div class="small-3 columns">
								<a href="sttc.xml-new.php?id=$id"><img src="{$info[image]}" alt="new"/></a>
							</div>
							<div class="small-9 columns">
								<a href="sttc.xml-new.php?id=$id"><strong>{$info[label]}</strong></a><br/>
								{$info[description]}
							</div>
						</div><br/>
EOL;
	return $output;
}

/*****
 * Display logic
 */



/************
 * Display
 */

$view->page_title('New static page');
$view->tooltype('chap');
$view->headline('New static page');
$view->action($action_output);

$output  = $view->open_view();
$output .= $view->view_header();
$output .= $alert_output;
print($output);


?>

			<div id="sttc-pg-edit">
				<form accept-charset="UTF-8" action="sttc.xml-new.php" method="post" data-abide enctype="multipart/form-data">
					<input type="hidden" name="grlx_xss_token" value="<?=$_SESSION['admin']?>"/>
<?php if ( !$_POST ) : ?>

				<div class="row">
					<div class="medium-4 columns">
						<h2>Text</h2>
<?=display_new_block($page_type_list[7],7) ?>

<?=display_new_block($page_type_list[1],1) ?>

<?=display_new_block($page_type_list[2],2) ?>
					</div>

					<div class="medium-4 columns">
						<h2>Lists</h2>
<?=display_new_block($page_type_list[3],3) ?>

<?=display_new_block($page_type_list[4],4) ?>

<?=display_new_block($page_type_list[5],5) ?>

<?=display_new_block($page_type_list[8],8) ?>
					</div>

					<div class="medium-4 columns">
						<h2>Other</h2>
<?=display_new_block($page_type_list[6],6) ?>
					</div>
				</div>
<?php endif; ?>
<?php if ( $_POST['static-type'] ) : ?>
<?php endif; ?>
				</form>
			</div>

<?php
$view->add_jquery_ui();
$view->add_inline_script($js_call);
print($view->close_view());
