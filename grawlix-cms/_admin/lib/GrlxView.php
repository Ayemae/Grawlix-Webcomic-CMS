<?php

/**
 * Assembles the bits to build an admin panel view.
 *
 * # Instantiate:
 * $view = new GrlxView;
 *
 * # Some settings:
 * $view->page_title('text'); // html title
 * $view->tooltype('text');   // class name from the stylesheet
 * $view->headline('text');   // h1 headline
 *
 * # Get output:
 * $view->open_view();   // start the html output
 * $view->view_header(); // the html around & including the h1 headline
 * $view->close_view();  // close the html output
 */

class GrlxView {

	protected $charset;
	protected $db;
	protected $css_dir;
	protected $css_file;
	protected $js_dir;
	protected $js_file;
	protected $js_inline;
	protected $modernizr;
	protected $title;
	protected $title_prefix;
	protected $viewport;
	protected $tooltype;
	protected $headline;
	protected $dropdown_css;
	protected $action;
	protected $fonts_on;
	protected $group_css;
	protected $group_headline;
	protected $group_instruction;
	protected $group_contents;
	protected $meta_css;
	protected $meta_preview;
	protected $meta_info_list;
	protected $meta_row_css;
	protected $joyride;
	protected $memory;
	public    $yah;

	/**
	 * Set defaults
	 */
	public function __construct() {
		$this->getArgs(func_get_args());
		$this->charset = 'utf-8';
		$this->css_dir = 'css/';
		$this->css_file[] = 'panel.css';
		$this->js_dir = 'js/';
		$this->js_file[] = 'panel.min.js';
		$this->modernizr = 'modernizr.min.js';
		$this->title_prefix = 'Grawlix Panel &#8253; ';
		$this->viewport = 'width=device-width, initial-scale=1.0';
		$this->fonts_on = true;
		$this->dropdown_css = 'h-drop';
		$this->group_css = 'group';
		$this->meta_css = 'meta';
		$this->meta_row_css = 'tbl';
		$this->fonts_off(TRUE);
		$this->memory = false;
	}

	/**
	 * Pass in any arguments
	 *
	 * @param array $list - arguments from main script
	 */
	protected function getArgs($list=null) {
		if(!isset($list))
			return;
		$list = $list[0] ?? null;
		if ( $list) {
			foreach ( $list as $key=>$val ) {
				if ( property_exists($this,$key) ) {
					$this->{$key} = $val;
				}
			}
		}
	}

// ! Dev tools

	/**
	 * Pass in html for joyride tour.
	 *
	 * @param string $str - formatted html for joyride
	 */
	public function setJoyride($str=null) {
		if ( $str ) {
			$this->joyride = $str;
		}
	}

	/**
	 * Print out memory usage with “show_memory()”
	 */
	public function show_memory($boolean=true) {
		if ( $boolean ) {
			$this->memory = $boolean;
		}
	}

	/**
	 * Does the work for memory usage
	 *
	 * @return string - memory usage
	 */
	protected function memory_used() {
		$usage = memory_get_usage(true);
		$unit = array('b','kb','mb','gb','tb','pb');
		return @round($usage/pow(1024,($i=floor(log($usage,1024)))),2).' '.$unit[$i];
	}

	/**
	 * Checks if there are leftovers from an install that need user attention.
	 * Triggered to run after every panel login.
	 *
	 * @return string - an alert with instructions
	 */
	protected function install_cleanup() {
		$message = '';
		if ( is_file('../firstrun.php') ) {
			$str = '<strong>firstrun.php</strong> is present in your main Grawlix directory. You should delete this file as a safety measure.';
			$alert = new GrlxAlert;
			$alert->special_id('cleanup-close');
			$message = $alert->warning_dialog($str);
		}
		return $message;
	}

	/**
	 * Check for a newer version of Grawlix.
	 * Triggered to run after every panel login.
	 *
	 * @return string - if it exists, the details about an update
	 */
	protected function grlx_version_check() {
		$args['file_item'] = 'http://www.getgrawlix.com/notices/index.xml';
		$args['action'] = 'read_file';
		$fileops = new GrlxFileOps($args);
		$file_data = $fileops->item_contents;
		$obj = NULL;
		if ( $file_data )
		{
			$obj = simplexml_load_string($file_data,'SimpleXMLElement',LIBXML_NOCDATA);
		}
		if ( $obj && $obj->{'item'}[0] ) {
			foreach ( $obj->{'item'}[0]->children() as $key=>$val ) {
				$info[$key] = (string)$val;
				if ( GRAWLIX_VERSION == $info['version'] ) {
					session_start();
					unset($_SESSION['grawlix_version']);
					session_write_close();
					unset($info);
				}
			}
		}
		is_array($info) ? $info : $info = null;
		return $info;
	}

// ! HTML head

	/**
	 * Turn off the google fonts link by calling “fonts_off()”
	 */
	public function fonts_off($boolean=true) {
		if ( $boolean ) {
			$this->fonts_on = false;
		}
	}

	/**
	 * Include a css file after the default css.
	 *
	 * @param string $file - name of css file in the css dir
	 */
	public function append_stylesheet($file) {
		$this->css_file[] = $file;
	}

	/**
	 * Include a css file before the default css.
	 *
	 * @param string $file - name of css file in the css dir
	 */
	public function prepend_stylesheet($file) {
		array_unshift($this->css_file, $file);
	}

	/**
	 * Text that appears in the html <title>
	 *
	 * @param string $title - text for title
	 */
	public function page_title($title) {
		$this->title = $title;
	}

	/**
	 * Assembles the document from <!doctype html> to </head>
	 *
	 * @return string - html for the view
	 */
	public function html_head() {
		$output  = $this->open_head();
		$output .= $this->charset();
		$output .= $this->viewport();
		$output .= $this->html_title();
		if ( $this->fonts_on ) {
			$output .= $this->font_link();
		}
		if ( $this->css_file ) {
			foreach ( $this->css_file as $key => $val ) {
				$output .= $this->stylesheet_link($val);
			}
		}
		$output .= $this->script_src($this->modernizr);
		$output .= $this->close_head();
		return $output;
	}

	/**
	 * The following functions do the grunt work for “html_head()”
	 */
	protected function open_head() {
		$str  = '<!doctype html>';
		$str .= '<html class="no-js" lang="en">';
		$str .= '<head>';
		return $str;
	}

	protected function charset() {
		$str = '<meta charset="'.$this->charset.'" />';
		return $str;
	}

	protected function viewport() {
		$str = '<meta name="viewport" content="'.$this->viewport.'" />';
		return $str;
	}

	protected function html_title() {
		$str = '<title>'.$this->title_prefix.$this->title.'</title>';
		return $str;
	}

	protected function font_link() {
		$str = '<link href="http://fonts.googleapis.com/css?family=Fira+Sans:300,400,500,700|Fira+Mono:400,700" rel="stylesheet" type="text/css" />';
		return $str;
	}

	protected function stylesheet_link($file) {
		$str = '<link rel="stylesheet" href="'.$this->css_dir.$file.'" />';
		return $str;
	}

	protected function script_src($file) {
		if ( (substr($file, 0, 7) != 'http://') && (substr($file, 0, 2) != '//') ) {
			$file = $this->js_dir.$file;
		}
		$str = '<script src="'.$file.'"></script>';
		return $str;
	}

	protected function close_head() {
		$str = '</head>';
		return $str;
	}

// ! Open body & panel banner

	/**
	 * Assembles the panel banner
	 *
	 * @return string - html from <header> to </header>
	 */
	protected function panel_banner() {
		$output  = $this->open_body();
		$output .= '<header class="banner">';
		$output .= '<section id="brand">';
		$output .= '<a href="book.view.php"><img src="img/logo_small.svg" alt="logo"></a>';
		$output .= '<a class="grawlix" href="book.view.php">Grawlix CMS</a> '.GRAWLIX_VERSION;
		$output .= '</section>';
		$output .= '<section id="news">';
		if ( $_SESSION['grawlix_version'] == 'run_check' ) {
//			$result = $this->grlx_version_check();
		}
		if ( isset($result) && is_array($result) ) {
			$output .= '<div id="news-alert" data-alert>';
			$output .= '<a class="close" href="#" id="news-close"><i></i></a>';
			$output .= '<a class="title" href="'.$result['url'].'">'.$result['headline'].'&emsp;[&#8239;'.$result['date_posted'].'&#8239;]</a>';
			$output .= '<p>'.$result['text'].'</p>';
			$output .= '</div>';
		}
		else {
			$output .= '&nbsp;';
		}
		$output .= '</section>';
		$output .= '<section id="top-btn">';
		$output .= '<a id="view" href="../" target="site"><i></i>View site</a>';
		$output .= '<a id="logout" href="panl.logout.php"><i></i>Log out</a>';
		$output .= '</section>';
		$output .= '</header>';
		return $output;
	}

	/**
	 * Builds the open body tag
	 *
	 * @return string - html <body>
	 */
	protected function open_body() {
		$str = '<body>';
		return $str;
	}

// ! Panel menu

	/**
	 * Assembles the panel menus
	 *
	 * @return string - html from <nav> to </nav>
	 */
	protected function panel_aside() {
		$output  = '<nav>';
		$output .= $this->drop_menu();
		$output .= $this->panel_menu();
		$output .= '</nav>';
		return $output;
	}

	/**
	 * Storage for the side nav menu with groups in order
	 *
	 * @return array - headings & data for menu links
	 */
	private function menu_list() {
		//The keys here are the yah values for each page, used to determine which entry to highlight in the menu.
		$menu['Comic pages'] = array(
			'1' => array(
				'file' => 'book.page-create.php',
				'name' => 'New page',
				'icon' => 'new'
			),

			'2' => array(
				'file' => 'book.list.php',
				'name' => 'Bookshelf',
				'icon' => 'book'
			),

			'3' => array(
				'file' => 'book.view.php',
				'name' => 'Book view',
				'icon' => 'book'
			),
			'16' => array(
				'file' => 'marker.list.php',
				'name' => 'Marker list',
				'icon' => 'book'
			),
			'4' => array(
				'file' => 'book.archive.php',
				'name' => 'Archive settings',
				'icon' => 'arch'
			)
		);

		if ( !is_file('book.list.php'))
		{
			unset($menu['Comic pages'][2]);
		}

		$menu['Static pages'] = array(
			'5' => array(
				'file' => 'sttc.page-new.php',
				'name' => 'New static page',
				'icon' => 'new'
			),
			'6' => array(
				'file' => 'sttc.page-list.php',
				'name' => 'Page list',
				'icon' => 'static'
			)
		);
		$menu['Site'] = array(
			'7' => array(
				'file' => 'site.nav.php',
				'name' => 'Main menu',
				'icon' => 'menu'
			),
			'8' => array(
				'file' => 'xtra.social.php',
				'name' => 'Social media',
				'icon' => 'social'
			),
			'9' => array(
				'file' => 'site.theme-manager.php',
				'name' => 'Themes',
				'icon' => 'theme'
			),
			'15' => array(
				'file' => 'media.list.php',
				'name' => 'Media library',
				'icon' => 'image'
			),
			'10' => array(
				'file' => 'ad.list.php',
				'name' => 'Advertisements',
				'icon' => 'ad'
			),
/*
			'11' => array(
				'file' => 'site.link-list.php',
				'name' => 'Link list',
				'icon' => 'link'
			),
*/
			'12' => array(
				'file' => 'site.config.php',
				'name' => 'Settings',
				'icon' => 'config'
			),
			'13' => array(
				'file' => 'user.config.php',
				'name' => 'User info',
				'icon' => 'user'
			),
			'14' => array(
				'file' => 'diag.health-check.php',
				'name' => 'Health check',
				'icon' => 'health'
			),
			'99' => array(
				'file' => 'index.php',
				'name' => 'Index',
				'icon' => 'book'
			)
		);

		if ( !is_file('media.list.php'))
		{
			unset($menu['Site'][15]);
		}

		return $menu;
	}

	// /**
	//  * Outputs banner for multi-book
	//  *
	//  * @return string - html from <div class="multibook-banner"> to </div>
	//  */
	// protected function multibook_banner($bookID=null) {
	// 	$output='';
	// 	if (is_file('book.list.php') && $bookID) {
	// 		$book=null;
	// 		// Grab all books from the database.
	// 		$db->orderBy('title','ASC');
	// 		$book_list = $db->get ('book', NULL, 'title,id');
	// 		for ($i=0;$i<count($book_list);$i++) {
	// 			if ($book_list[$i]['ID']==$bookID) {
	// 				$book=$book_list[$i];
	// 			}
	// 		}
	// 		$output .= '<div class="multibook-banner">';
	// 		$output .= '<h2>Book: '.$book->title.'</h2>';
	// 		if (count($book_list)>1) {
	// 			$output .= '<label for="book-select">Select book:</label>';
	// 			$output .= '<select id="book-select">';
	// 			foreach ($book_list AS $booki) {
	// 				$output .= '<option value="'.$booki->ID.'"'.($booki->ID==$bookID ? ' selected' : null).'>'.$booki->$title.'</option>';
	// 			}unset($booki);
	// 			$output .= '</select>';
	// 		}
	// 		$output .= '</div>';
	// 	}
	// 	return $output;
	// }

	/**
	 * Regular panel menu
	 *
	 * @return string - html for desktop view
	 */
		private function panel_menu() {
			$menu = $this->menu_list();
			$link = new GrlxLink;
			$output  = '<div class="panel-menu">';
			foreach ( $menu as $group => $array ) {
				$output .= '<ul>';
				$output .= '<li class="parent">'.$group.'</li>';
				foreach ( $array as $item => $val ) {
					$link->url($val['file']);
					$link->tap($val['name']);
					$link->icon('true');
					if ( $this->yah == $item ) {
						$link->anchor_class($val['icon'].' current');
					}
					else {
						$link->anchor_class($val['icon']);
					}
					$output .= '<li>'.$link->paint().'</li>';
				}
				$output .= '</ul>';
			}
			$output .= '</div>';
			return $output;
		}

	/**
	 * Mobile panel menu
	 *
	 * @return string - html for small screens
	 */
	private function drop_menu() {
		$menu = $this->menu_list();
		$link = new GrlxLink();
		$output  = '<button class="drop-menu" data-dropdown="drop-menu"><i></i></button>';
		$output .= '<ul id="drop-menu" data-dropdown-content>';
		foreach ( $menu as $group => $array ) {
			$output .= '<li><a class="parent">'.$group.'</a></li>';
			foreach ( $array as $item => $val ) {
				$link->url($val['file']);
				$link->tap($val['name']);
				$link->icon('true');
				$link->anchor_class($val['icon']);
				$output .= '<li>'.$link->paint().'</li>';
			}
		}
		$output .= '</ul>';
		return $output;
	}

// ! Content

	/**
	 * Call this function at the start of view output
	 *
	 * @return string - html for the head of the page, opens content area
	 */
	public function open_view() {
		$output  = $this->html_head();
		$output .= $this->panel_banner();
		$output .= '<div id="wrap">';
		$output .= $this->panel_aside();
		$output .= '<main>';
		//$output .= '<br>TEST - BOOK ID?: '.$bookID.'<br>';
		//$output .= $this->multibook_banner($bookID);
		if ( $_SESSION['install_cleanup'] == 'run_check' ) {
			$message = $this->install_cleanup();
			if ( $message ) {
				$output .= $message;
			}
		}
		return $output;
	}

	/**
	 * Call this function to build the h1 headline for the page
	 *
	 * @return string - html for <header> block
	 */
	public function view_header() {

		$update = '_upgrade-to-1.3.php';
		$needs_update = TRUE; // Assume it needs an update until proven otherwise.
		
		$alert_output = '';

		if ( file_exists($update) ) {
			global $db; // TODO: This is bad form. Why isn’t it getting called in to the object?
			$table_list = $db->rawQuery ('SHOW TABLES', FALSE, FALSE);
			if ( $table_list )
			{
				foreach($table_list as $table)
				{
					$table_list_2[] = reset($table);
				}
			}

			if ( $table_list_2 && in_array('grlx_static_content',$table_list_2) )
			{
				$needs_update = FALSE;
			}

			$message = new GrlxAlert;
			if ( $needs_update === TRUE)
			{
				$alert_output = $message->info_dialog('Welcome to version 1.3!<br /><br />You should <a href="'.$update.'">update your database</a> now.');
			}
			else
			{
				$alert_output = $message->info_dialog('Looks like you’re running version 1.3. You should delete the file called <strong>'.$update.'</strong> in your _admin folder.');
			}
		}

		$class = $this->tooltype ? ' '.$this->tooltype : null;
		$output  = '<header class="main'.$class.'">';
		$output .= $alert_output;
		$output .= $this->headline;
		$output .= $this->format_actions();
		$output .= '</header>';
		return $output;
	}

	/**
	 * Call this function at the end of view output, closes content area
	 *
	 * @return string - html for the foot of the page
	 */
	public function close_view() {
		$output  = '</main>';
		$output .= '</div>';
		$output .= $this->html_foot();
		return $output;
	}

	/**
	 * Pass in the css class name for this view
	 *
	 * @param string $str - css class name
	 */
	public function tooltype($str) {
		$this->tooltype = $str;
	}

	/**
	 * Pass in the headline and clear any previous actions
	 *
	 * @param string $str - text for the <h1>
	 */
	public function headline($str) {
		$this->headline = '<h1>'.$str.'</h1>';
		unset($this->action);
	}

	/**
	 * Adds a button/link to the right of the view’s h1
	 *
	 * @param string $str - complete html for button
	 */
	public function action($str) {
		if(isset($this->action))
			$this->action .= $str;
		else
			$this->action = $str;
	}

	/**
	 * Adds actions to the <header> block if needed
	 *
	 * @return string - html for actions
	 */
	public function format_actions() {
		if ( isset($this->action) ) {
			return '<div class="actions">'.$this->action.'</div>';
		}
		return '';
	}

	/**
	 * Call this function to build an h1 with dropdown menu button
	 *
	 * @param string $str - html for the <div> block
	 */
	public function headline_dropdown($str) {
		$this->headline = '<div class="'.$this->dropdown_css.'">'.$str.'</div>';
		unset($this->action);
	}

	/**
	 * Reset tool group vars
	 */
	protected function reset_group_vars() {
		$this->group_css = 'group';
		unset($this->group_headline);
		unset($this->group_instruction);
		unset($this->group_contents);
	}

	/**
	 * Pass in the css class name for this tool group
	 *
	 * @param string $str - css class name
	 */
	public function group_css($str) {
		if ( $str ) {
			$this->group_css .= ' '.$str;
		}
	}

	/**
	 * Pass in a regular headline for a tool group
	 *
	 * @param string $str - text for the <h2>
	 */
	public function group_h2($str) {
		$this->group_headline = '<h2>'.$str.'</h2>';
	}

	/**
	 * Pass in a smaller headline for a tool group
	 *
	 * @param string $str - text for the <h3>
	 */
	public function group_h3($str) {
		$this->group_headline = '<h3>'.$str.'</h3>';
	}

	/**
	 * Pass in instructions for a tool group
	 *
	 * @param string $str - text for the instructions
	 */
	public function group_instruction($str) {
		if ( $str ) {
			$this->group_instruction = '<p>'.$str.'</p>';
		}
	}

	/**
	 * Pass in the content for a tool group
	 *
	 * @param string $str - formatted html for the data
	 */
	public function group_contents($str) {
		$this->group_contents = $str;
	}

	/**
	 * Call this function to assemble the group html
	 *
	 * @return string - html for this tool/data group
	 */
	public function format_group() {
		$output  = '<section class="'.$this->group_css.'">';
		$output .= '<header>';
		$output .= $this->group_headline ?? '';
		$output .= $this->group_instruction ?? '';
		$output .= '</header>';
		$output .= '<div>';
		$output .= $this->group_contents ?? '';
		$output .= '</div>';
		$output .= '</section>';
		$this->reset_group_vars();
		return $output;
	}

	/**
	 * Pass in the css class name for this meta section
	 *
	 * @param string $str - css class name
	 */
	public function meta_css($str) {
		if ( $str ) {
			$this->meta_css .= ' '.$str;
		}
	}

	/**
	 * Pass in the css class name for the meta table row
	 *
	 * @param string $str - css class name
	 */
	public function meta_row_css($str) {
		if ( $str ) {
			$this->meta_row_css .= ' '.$str;
		}
	}

	/**
	 * Pass in the meta preview image
	 *
	 * @param string $str - full image tag
	 */
	public function meta_preview($str) {
		$this->meta_preview = $str;
	}

	/**
	 * Pass in the content for a meta section
	 *
	 * @param array $arr - associative, 'meta label' => 'meta value'
	 */
	public function meta_info_list($arr) {
		$this->meta_info_list = $arr;
	}

	/**
	 * Call this function to assemble the meta section
	 *
	 * @return string - html for this meta
	 */
	public function format_meta() {
		$output  = '<section class="'.$this->meta_css.'">';
		$output .= '<div>';
		$output .= $this->meta_preview;
		$output .= '</div>';
		$output .= '<div>';
		$output .= $this->format_meta_rows();
		$output .= '</div>';
		$output .= '</section>';
		return $output;
	}

// ! Scripts & HTML foot

	public function add_jquery_ui() {
		$this->add_script('//code.jquery.com/ui/1.11.4/jquery-ui.js');
	}

	/**
	 * Add a js file before the footer
	 *
	 * @param string $str - the js filename
	 */
	public function add_script($str) {
		$this->js_file[] = $str;
	}

	/**
	 * Add inline js before the footer
	 *
	 * @param string $str - javascript
	 */
	public function add_inline_script($str) {
		$this->js_inline .= $str;
	}

	/**
	 * The following functions do the grunt work closing the document
	 */
	protected function html_foot() {
		$output = '';
		if ( $this->joyride ) {
			$output .= $this->joyride;
		}
		if ( $this->js_file ) {
			foreach ( $this->js_file as $key => $val ) {
				$output .= $this->script_src($val);
			}
		}
		if ( $this->js_inline ) {
			$output .= $this->script_inline();
		}
		$output .= $this->close_foot();
		return $output;
	}

	protected function script_inline() {
		$str  = '<script>';
		$str .= $this->js_inline;
		$str .= '</script>';
		return $str;
	}

	protected function close_foot() {
		$str = '';
		if ( $this->memory ) {
			$str = '<pre>'.$this->memory_used().'</pre>';
		}
		$str .= '</body>';
		$str .= '</html>';
		return $str;
	}
}
