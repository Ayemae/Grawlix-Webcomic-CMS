<?php

/* Artists use this script to create and edit text pages.
 */

/* ! Setup */

require_once('panl.init.php');
require_once('lib/htmLawed.php');

$link = new GrlxLinkStyle;
$view = new GrlxView;
$message = new GrlxAlert;
$fileops = new GrlxFileOps;

$view-> yah = 5;


// Offer these to the artist.
$page_type_list[1] = array(
	'title' => 'About the artist',
	'description' => 'Your bio, artist’s statement, or just who you want people to know you as.',
	'url' => '/about-artist'
);

$page_type_list[2] = array(
	'title' => 'About the comic',
	'description' => 'What you want new readers to know about your comic.',
	'url' => '/about-comic'
);
$page_type_list[3] = array(
	'title' => 'Cast',
	'description' => 'Name, quick bio and picture of each character.',
	'url' => '/comic-characters'
);
$page_type_list[4] = array(
	'title' => 'Links',
	'description' => 'Simple title, optional descriptive text and URL.',
	'url' => '/favorite-links'
);
$page_type_list[5] = array(
	'title' => 'FAQ',
	'description' => 'Answers to questions you get asked often it’s worth a page.',
	'url' => '/frequently-asked-questions'
);
$page_type_list[6] = array(
	'title' => 'Freeform',
	'description' => 'Add your own HTML. Anything goes.',
	'url' => '/new-page'
);
$page_type_list[7] = array(
	'title' => 'Welcome new readers',
	'description' => 'Introduction to your comic.',
	'url' => '/new-readers-begin-here'
);
$page_type_list[8] = array(
	'title' => 'Store',
	'description' => 'Quick product teasers that you link to your ecommerce package.',
	'url' => '/store'
);
$page_type_list[9] = array(
	'title' => 'Gallery',
	'description' => 'Show off your artwork outside of the comic.',
	'url' => '/gallery'
);
$page_type_list[10] = array(
	'title' => 'Book browser',
	'description' => 'Let readers choose a book in your webcomic library.',
	'url' => '/bookshelf'
);


/* ! Updates */

if ( !empty($_GET['id']) ) {
	$new_page_type_id = $_GET['id'];

	// Create the page
	$data = array (
		'title' => $page_type_list[$new_page_type_id]['title'],
		'options' => 'image-left',
		'layout' => 'list',
		'date_created' => $db->now(),
		'date_modified' => $db->now()
	);
	$static_id = $db->insert('static_page', $data);

	// Add a menu reference.
	if ( $static_id ) {
			// What’s the last order?
			$result = $db-> get ('path',null,'MAX(sort_order) AS max');
			$sort_order = $result[0]['max'] + 1;
			$sort_order ? $sort_order : $sort_order = 0;

	    // Wait — does this URL exist in the database?
	    $new_url = $page_type_list[$new_page_type_id]['url'];
	    $db-> where ('url', $new_url);
	    $maybe_existing = $db-> getOne('path',null,'url');

	    if ( $maybe_existing && is_array($maybe_existing) ) {
	      $new_url .= date('-h-i-s');
	    }

		// OK, add it to the menu.
		$data = array (
			'title' => $page_type_list[$new_page_type_id]['title'],
			'url' => $new_url,
			'rel_id' => $static_id,
			'rel_type' => 'static',
			'in_menu' => 1,
			'edit_path' => 1,
			'sort_order' => $sort_order
		);
		$db->insert('path', $data);
	}



	// Let’s add some custom default data.
	if ( $static_id && $static_id != '' ) {
		switch ( $new_page_type_id ) {
			// About the artist
			case 1:
				$data = array (
					'page_id' => $static_id,
					'title' => 'About the artist',
					'content' => '(Put something about yourself in here.)',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
				break;

			// About the comic
			case 2:
				$data = array (
					'page_id' => $static_id,
					'title' => 'About the comic',
					'content' => '(Put something about your comic in here.)',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
				break;

			// ------ Cast
			case 3:
				$data = array (
					'page_id' => $static_id,
					'title' => 'First character name',
					'content' => 'A brief bio of character 1',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Second character name',
					'content' => 'A brief bio of character 2',
					'sort_order' => 2,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Third character name',
					'content' => 'A brief bio of character 3',
					'sort_order' => 3,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
				break;

			// ------ Links
			case 4:
				$data = array (
					'page_id' => $static_id,
					'title' => 'My favorite comic',
					'url' => 'http://el-indon.com',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Another favorite comic',
					'url' => 'http://evil-inc.com',
					'sort_order' => 2,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'And a third site I like',
					'url' => 'http://www.getgrawlix.com',
					'sort_order' => 3,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
				break;

			// ------ FAQ
			case 5:
				$data = array (
					'page_id' => $static_id,
					'title' => 'A question?',
					'content' => 'An answer.',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Another question?',
					'content' => 'Another answer.',
					'sort_order' => 2,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'A third question?',
					'content' => 'A third answer.',
					'sort_order' => 3,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
			break;

			// ------ Freeform
			case 6:
				$data = array (
					'page_id' => $static_id,
					'title' => 'Freeform',
					'content' => '',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
				break;

			// ------ Welcome new readers
			case 7:
				$data = array (
					'page_id' => $static_id,
					'title' => 'Welcome new readers!',
					'content' => '(Tell people what your comic’s about.)',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
				break;

			// ------ “Store”
			case 8:
				$data = array (
					'page_id' => $static_id,
					'title' => 'Swag',
					'content' => 'About this product.',
					'url' => 'www.your-store.com',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Swag',
					'content' => 'About this product.',
					'url' => 'www.your-store.com',
					'sort_order' => 2,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Swag',
					'content' => 'About this product.',
					'url' => 'www.your-store.com',
					'sort_order' => 3,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);
				break;

			// ------ “Store”
			case 9:
				$data = array (
					'page_id' => $static_id,
					'title' => 'Picture 1',
					'content' => 'A few words about this image.',
					'sort_order' => 1,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Picture 2',
					'content' => 'A few words about this image.',
					'sort_order' => 2,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

				$data = array (
					'page_id' => $static_id,
					'title' => 'Picture 3',
					'content' => 'A few words about this image.',
					'sort_order' => 3,
					'created_on' => $db->NOW(),
					'modified_on' => $db->NOW()
				);
				$new_block_id = $db->insert('static_content', $data);

			// ------ Multi-book browser
			case 10:
				$book_list = $db->get('book',NULL,'id,title,description');

				if ( $book_list ) {
					$order = 1;
					foreach ( $book_list as $key => $val ) {

						// Get its path.
						$db->where('rel_id',$val['id']);
						$db->where('rel_type','book');
						$url = $db->getOne('path','url');

						$data = array (
							'page_id' => $static_id,
							'title' => $val['title'],
							'content' => $val['description'],
							'url' => $url['url'],
							'sort_order' => $order,
							'created_on' => $db->NOW()
						);
						$new_block_id = $db->insert('static_content', $data);
						$order++;
					}
				}

				break;
		}
	}

	// Send freeform pages straight to the block editor.
	if ( $new_page_type_id == 6
		|| $new_page_type_id == 7
		|| $new_page_type_id == 1
		|| $new_page_type_id == 2
	) {
		header('location:sttc.block-edit.php?msg=created&block_id='.$new_block_id);
	}
	else {
		header('location:sttc.page-edit.php?msg=created&page_id='.$static_id);
	}
	die();
}

function display_new_block($info,$id) {
	$output = <<<EOL
<section>
	<strong>{$info['title']}</strong><br/>
	{$info['description']}
	<a href="sttc.page-new.php?id=$id">Create&nbsp;now</a>
<br/>&nbsp;</section>
EOL;
	return $output;
}

/* ! Display logic */



/* ! Display */

$view->page_title('New static page');
$view->tooltype('chap');
$view->headline('New static page');
//$view->action($action_output);

$output  = $view->open_view();
$output .= $view->view_header();
print($output);


?>

			<div id="sttc-pg-edit">
				<form accept-charset="UTF-8" action="sttc.page-new.php" method="post" data-abide enctype="multipart/form-data">
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

<?=display_new_block($page_type_list[9],9) ?>

<?php if(is_file('book.list.php')) : ?>
<?=display_new_block($page_type_list[10],10) ?>
<?php endif; ?>
					</div>

					<div class="medium-4 columns">
						<h2>Other</h2>
<?=display_new_block($page_type_list[6],6) ?>
					</div>
				</div>
<?php endif; ?>
<?php if ( !empty($_POST['static-type']) ) : ?>
<?php endif; ?>
				</form>
			</div>

<?php
$view->add_jquery_ui();
$view->add_inline_script($js_call ?? null);
print($view->close_view());
