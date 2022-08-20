<?php

/**
 * Core to build every front-end page
 *
 * # Instantiate:
 * $grlxPage = new GrlxPage;
 */

class GrlxPage2 {

//	protected $httpHeader = 'Content-Type: text/html; charset=utf-8';
	protected $u_id = NULL;
	protected $db;
	protected $isHome;
	protected $isAdmin;
	protected $templateFileList;
	protected $template;
	protected $path;
	protected $statusCode;
	protected $query;
	protected $request;
	protected $filebase;
	protected $pageInfo;
	protected $bookInfo;
	protected $imageInfo;
	protected $parsedown;
	protected $menu;
	protected $milieu;
	protected $domainName;
	protected $navLinks;
	protected $permalinkFormat;
	protected $canonicalLink;
	protected $themeOverride;
	public    $theme;
	public    $content;
	public    $ads;
	public    $share;
	public    $follow;
	public    $services;
	public    $widgets;
	public    $grlxbar;

	/**
	 * Setup, set defaults, etc.
	 *
	 */
	public function __construct() {
		global $_db;
		$this->db = $_db;
	}

	/**
	 * Move stuff out of the constructor.
	 *
	 * @param		array		$args
	 * @param		object		$route
	 */
	public function setup($args = NULL, $route = NULL) {

		$this->setTemplateFiles();

		// Pass in site info from db
		if ($args)
		{
			foreach ( $args as $key => $val ) {
				if ( property_exists($this, $key) ) {
					$this->{$key} = $val;
				}
			}
		}

		// Check the given route data
		if (is_object($route)) {
			// Template type
			if (strlen($route->template) > 0)
			{
				$this->template = $this->templateFileList[$route->template] ?? NULL;
			}

			// Check for a u_id
			if (isset($route->u_id) && is_numeric($route->u_id))
			{
				$this->u_id = $route->u_id;
			}
			
			if(isset($route->statusCode))
			{
				$this->statusCode = $route->statusCode;
			}

			// Set the book_id
			(isset($route->book_id) && is_numeric($route->book_id)) ? $id = $route->book_id : $id = 1;
			$this->getBookInfo($id);
		}

		$this->domainName = $_SERVER['HTTP_HOST'];
		$this->permalinkFormat = explode('/',$this->milieu['permalink_format']);
		array_shift($this->permalinkFormat);
		$this->filebase = $this->milieu['home_url'];
	}

	/**
	 * Set some vars from $grlxRequest
	 *
	 * @param		object		$grlxRequest
	 * @vars			string		$request
	 */
	public function contents($request) {
		$this->request = $request->path;
	}

	/**
	 * Pretty much what the name says
	 *
	 * @param string $path - full path and name of file
	 * @param string $contents - file contents
	 */
	protected function getFileContents($path) {
		if ( file_exists($path) ) {
			$contents = file_get_contents($path);
			return $contents;
		}
	}

	/**
	 * Get book info with url slug and latest page's sort order
	 *
	 */
	protected function getBookInfo($id = NULL) {
		$id < 1 ? $id = 1 : $id;

		$cols = array(
			'b.id',
			'b.title',
			'b.description',
			'b.tone_id',
			'b.sort_order',
			'b.publish_frequency',
			'b.options',
			'bp.sort_order AS latest_page',
			'p.url'
		);
		$this->db->join('book_page bp', 'b.id = bp.book_id', 'INNER');
		$this->db->join('path p', 'b.id = p.rel_id', 'INNER');
		$this->db->where('p.rel_type', 'book');
		$this->db->where('p.url', '/', '<>');
		$this->db->where('bp.date_publish <= NOW()');
		$this->db->where('b.id', $id);
		$this->db->orderBy('bp.sort_order', 'DESC');

		$result = $this->db->getOne('book b', $cols);
		$result['latest_page'] = (integer) $result['latest_page'];
		$result['archive_url'] = $result['url'].'/archive';

		$this->bookInfo = $result;
	}

	/**
	 * The available page template files, v1.0
	 */
	protected function setTemplateFiles() {
		$this->templateFileList = array(
			'archive'   => 'page.archive.php',
			'comic'     => 'page.comic.php',
			'static'    => 'page.static.php',
			'anthology' => 'page.archive.php'
		);
	}

	/**
	 * Get info for a theme & tone
	 */
	protected function getThemeToneInfo() {
		if ( $this->milieu['multi_tone'] < 1 ) {
			$this->theme['tone_id'] = $this->milieu['tone_id'];
		}
		$cols = array(
			't.theme_id',
			't.id AS tone_id',
			't.options',
			'l.author',
			'directory'
		);
		$result = $this->db
			->join('theme_list l','t.theme_id = l.id','INNER')
			->where('t.id',$this->theme['tone_id'])
			->getOne('theme_tone t',$cols);
		if ( $result && $result['tone_id'] < 1 ) {
			$this->theme['tone_id']   = 'default';
			$this->theme['theme_id']  = 'default';
			$this->theme['directory'] = DIR_SYSTEM_TMP;
		}
		else if($result) {
			$result['directory'] = DIR_THEMES.$result['directory'].'/';
			$this->theme = $result;
			$this->getSlotImages();
		}
		if ( $this->themeOverride ) {
			$this->theme['directory'] = DIR_THEMES.$this->themeOverride.'/';
		}
	}

	/**
	 * Get any images associated with the selected tone
	 */
	protected function getSlotImages() {
		$cols = array(
			'ir.url',
			'ts.label',
			'ir.description'
		);
		$result = $this->db
			->join('image_reference ir', 'ir.id = itm.image_reference_id', 'INNER')
			->join('theme_slot ts', 'ts.id = itm.slot_id', 'INNER')
			->where('itm.tone_id', $this->theme['tone_id'])
			->where('ts.type', 'theme')
			->get('image_tone_match itm', null, $cols);
		if ( $result ) {
			$result = rekey_array($result,'label');
			$this->theme['slots'] = $result;
		}
	}

	/**
	 * Format date according to milieu setting or via argument
	 *
	 * @param string $date - the string to format
	 * @param string $format - PHP date format code
	 * @return string $str - formatted date
	 */
	protected function formatDate($date=null,$format=null) {
		if ( !$format ) {
			$format = $this->milieu['date_format'];
		}
		if ( $date ) {
			$date = explode('-',$date);
			$str = date($format,mktime(0,0,0,$date[1],(int)$date[2],$date[0]));
			return $str;
		}
	}

	/**
	 * Convert markdown to html with Parsedown
	 *
	 * @param string $str - the string to format
	 * @return string by reference - formatted html
	 */
	protected function styleMarkdown(&$str) {
		if ( !$this->parsedown)
		{
			$this->parsedown = new Parsedown;
		}
		$str = $this->parsedown->text($str);
		return $str;
	}

	/**
	 * Make full path for use in sharing links, etc.
	 *
	 * @param string $str - the path to format
	 * @return string by reference - full URL
	 */
	protected function prependDomainName(&$str) {
		$str = $this->domainName.$str;
	}

	/**
	 * An easy theme selection override for testing purposes
	 *
	 * @param string $str - a directory name in the themes directory
	 */
	public function testTheme($str=null) {
		$path = './'.DIR_THEMES.$str.'/';
		if ( is_dir($path) ) {
			$this->themeOverride = $str;
		}
	}

	/**
	 * Put the page together
	 */
	public function buildPage() {
		if ( isset($_COOKIE['grlx_bar']) && $_COOKIE['grlx_bar'] == true ) {
			$this->isAdmin = true;
		}
		if($this->statusCode == 404) header('HTTP/1.0 404 Not Found');
		else if ( isset($this->httpHeader) ) header($this->httpHeader);
		$this->getThemeToneInfo();
		$this->buildSupportFileLinks();
		$this->buildHeaderMeta();
		$this->buildAdminBar();
		$this->buildContent();
		$this->loadPageTemplate();
	}

	/**
	 * Build output for the favicons
	 *
	 * @return string $output - links for head of html document
	 */
	protected function formatFavicons() {
		$apple_list = array('57','114','72','144','60','120','76','152');
		$icon_links = '';
		foreach ( $apple_list as $key=>$val ) {
			$sizes = $val.'x'.$val;
			$attributes = array(
				'rel'   => 'apple-touch-icon',
				'sizes' => $sizes,
				'href'  => $this->filebase.DIR_FAVICONS.'apple-touch-icon-'.$sizes.'.png'
			);
			$icon_links .= build_head_link($attributes);
		}
		$attributes = array(
			'rel'  => 'shortcut icon',
			'href' => $this->filebase.DIR_FAVICONS.'favicon.ico'
		);
		$icon_links .= build_head_link($attributes);
		$size_list = array(196,160,96,16,32);
		foreach ( $size_list as $key=>$val ) {
			$sizes = $val.'x'.$val;
			$attributes = array(
				'rel'   => 'apple-touch-icon',
				'sizes' => $sizes,
				'type'  => 'img/png',
				'href'  => $this->filebase.DIR_FAVICONS.'apple-touch-icon-'.$sizes.'.png'
			);
			$icon_links .= build_head_link($attributes);
		}
		$icon_links .= "\t\t".'<meta name="msapplication-TileColor" content="#6b4faf" />'."\n";
		$icon_links .= "\t\t".'<meta name="msapplication-TileImage" content="'.$this->filebase.DIR_FAVICONS.'mstile-144x144.png" />'."\n";
		$icon_links .= "\t\t".'<meta name="msapplication-config" content="'.$this->filebase.DIR_FAVICONS.'browserconfig.xml" />'."\n";
		return $icon_links;
	}

	/**
	 * Store all the possible output a page template may request
	 */
	protected function buildContent() {
		if ( empty($this->pageInfo['publish_frequency']) ) {
			$this->pageInfo['publish_frequency'] = $this->bookInfo['publish_frequency'];
			if(empty($this->pageInfo['publish_frequency']) || $this->pageInfo['publish_frequency'] == '0')
				$this->pageInfo['publish_frequency'] = 'occasionally';
			else
				$this->pageInfo['publish_frequency'] = NULL;
		}
		$this->content = array_merge($this->milieu,$this->pageInfo);
		$this->content = array_merge($this->content,$this->theme['html']);

		if ( !empty($this->content['date_publish']) ) {
			$date = $this->formatDate($this->content['date_publish']);
			$str = '<time itemprop="datePublished" datetime="'.$this->content['date_publish'].'">'.$date.'</time>';
			$this->content['date_publish'] = $str;
		}
		$this->content['rss'] = $this->milieu['directory'].'/comic/rss?id='.$this->bookInfo['id'];
		$this->content['menu'] = $this->formatSiteMenu();
		$this->buildNavLinks();
	}

	/**
	 * Build any nav links present
	 */
	protected function buildNavLinks() {
		$this->content['archive_url'] = $this->milieu['directory'].$this->bookInfo['archive_url'];
		if ( $this->navLinks ) {
			foreach ( $this->navLinks as $section=>$list ) {
				foreach ( $list as $label=>$info ) {
					if ( !array_key_exists('css',$info) ) {
						$info['css'] = '';
					}
					foreach ( $info as $type=>$val ) {
						switch ( $type ) {
							case 'url':
								$str = $val; //For some reason $this->milieu['directory'] is already included in the value.
								break;
							case 'css':
								$str = $label.$val.' navlink';
								break;
						}
						$this->content[$section.'_'.$type.'_'.$label] = $str;
					}
				}
			}
		}
	}

	/**
	 * Build the front-end admin bar links/info
	 */
	protected function buildAdminBar() {
		if ( $this->isAdmin ) {
			if ( !empty($this->pageInfo['edit_this']) ) {
				$this->grlxbar['edit_text'] = $this->pageInfo['edit_this']['text'] ?? null;
				$this->grlxbar['edit_link'] = $this->filebase.DIR_PANEL.$this->pageInfo['edit_this']['link'] ?? null;
			}
			if ( isset($_SESSION['admin']) && is_numeric($_SESSION['admin']) ) {
				$this->grlxbar['panel_text'] = 'Go to your Panel';
				$this->grlxbar['panel_link'] = $this->filebase.DIR_PANEL.'book.view.php';;
			}
			else {
				$this->grlxbar['panel_text'] = 'Log into your Panel';
				$this->grlxbar['panel_link'] = $this->filebase.DIR_PANEL.'panl.login.php';;
			}
			$this->grlxbar['img'] = $this->filebase.DIR_SYSTEM_IMG.'logo_small.svg';
		}
		unset($this->pageInfo['edit_this']);
	}

	/**
	 * New function ignores permalink_format since its baked-in for now
	 *
	 * @param integer $val - depending on type, number's use changes
	 * @param string $type - archive, page
	 * @return string $str - path
	 */
	protected function buildPermalink($val = null, $type = 'page') {
		if ($type == 'archive') {
			$str = $this->milieu['directory'].$this->request.'/'.$val; // Chapter number
		}

		if ($type == 'page') {
			$str = $this->milieu['directory'].$this->bookInfo['url'].'/'.$val;
		}

		return $str;
	}

	/**
	 * Construct canonical link for html head
	 *
	 * @return string $output - formatted link
	 */
	protected function buildCanonicalLink() {
		if ( $this->isHome ) {
			$str = null;
		}
		elseif ( !empty($this->canonicalLink) ) {
			$str = $this->canonicalLink;
		}
		else {
			$str = $this->pageInfo['permalink'] ?? null;
		}
		$output = '<link rel="canonical" href="http://'.$this->domainName.$str.'" />'."\n";
		return $output;
	}

	protected function buildHeaderMeta() {
		$meta = '';
		if(isset($this->pageInfo['meta_description']))
			$meta = $this->pageInfo['meta_description'];
		else if(isset($this->milieu['meta_description']))
			$meta = $this->milieu['meta_description'];
		$meta = mb_substr($meta,0,160,'UTF-8');
		$output  = '<meta name="description" content="'.$meta.'" />';
		$output .= '<meta name="generator" content="The Grawlix CMS — the CMS for comics" />';
		$output .= '<meta name="copyright" content="'.($this->milieu['copyright'] ?? '').' by '.($this->milieu['artist_name'] ?? '').'" />';
		$output .= '<meta name="author" content="'.($this->milieu['artist_name'] ?? '').'" />';
		$output .= $this->buildCanonicalLink();
		$this->pageInfo['meta_head'] = $output;
	}

	/**
	 * HTML header and footer links for theme files: css, js
	 */
	protected function buildSupportFileLinks() {
		$outputHead = '';
		if ( $this->isAdmin ) {
			$outputHead = '<link rel="stylesheet" href="'.$this->filebase.DIR_SYSTEM_CSS.'public-admin.css" />'."\n";
		}
		if(!empty($this->theme['directory']))
			$outputHead .= "\t\t".'<link rel="stylesheet" href="'.$this->filebase.$this->theme['directory'].'theme.css" />'."\n";
		if ( !empty($this->theme['options']) ) {
			$parts = explode('.',$this->theme['options']);
			// tone CSS file
			if ( $parts[0] == 'tone' && end($parts) == 'css' && !empty($this->theme['directory']) ) {
				$outputHead .= "\t\t".'<link rel="stylesheet" href="'.$this->filebase.$this->theme['directory'].$this->theme['options'].'" />'."\n";
			}
		}

		if ( $this->template == 'page.static.php' || $this->template == 'page.archive.php' ) {
			// shared css for layout patterns
			$outputHead .= "\t\t".'<link rel="stylesheet" href="'.$this->filebase.DIR_SYSTEM_CSS.'public-shared.css" />'."\n";
		}
		$outputFoot = '';
		if ( isset($this->theme['author']) && $this->theme['author'] == 'Grawlix' ) {
			$outputHead .= "\t\t".'<script src="'.$this->filebase.DIR_SCRIPTS.'modernizr.min.js"></script>'."\n";
			if(isset($this->theme['directory']))
				$outputFoot  = "\t\t".'<script src="'.$this->filebase.$this->theme['directory'].'script.min.js"></script>'."\n";
		}
		$this->theme['html']['support_head'] = $outputHead;
		$this->theme['html']['support_foot'] = $outputFoot;
	}

	/**
	 * Format menu items, ignore those not in menu
	 *
	 * @return string $menu - html for menu as list items
	 */
	protected function formatSiteMenu() {
		$this->menu = rekey_array($this->menu,'sort_order');
		ksort($this->menu);
		$menu = '';
		foreach ( $this->menu as $key=>$val ) {
			if ( substr($val['url'],0,4) != 'http' ) {
				$val['url'] = str_replace('//','/',$val['url']); // workaround, yeah, I know
			}
			if ( $val['in_menu'] == 1 ) {
				if ( $val['url'] == $this->request ) {
					$li_class = ' class="active"';
				}
				elseif ( ($val['url'] == '/' ) && ($this->request == '/index.php') ) {
					$li_class = ' class="active"';
				}
				else {
					$li_class = null;
				}
				$url = $val['url'];
				if ( substr($url,0,4) != 'http' ) {
					$url = str_replace('//', '/', $url); // Double-check the double-slash
					$url = $this->milieu['directory'].$url; //only add the directory to internal links
				}
				$menu .= '<li'.$li_class.'><a href="'.$url.'">'.$val['title'].'</a></li>'."\n";
			}
		}
		return $menu;
	}

	/**
	 * Include the page template
	 */
	protected function loadPageTemplate() {
		if(isset($this->theme['directory']))
			include_once('./'.$this->theme['directory'].$this->template);
		else die('This comic has no theme selected!');
	}

	/**
	 * Include a snippet template
	 *
	 * @param string $str - keyword used in the snippet function call
	 */
	public function loadSnippetTemplate($str=null) {
		$file = 'snippet.'.$str.'.php';
		if ( $str == 'share' ) {
			$this->getThirdServices();
			$this->getShareContent();
		}
		if ( $str == 'follow' ) {
			$this->getThirdServices();
			$this->checkUserInfo($this->services['follow']);
		}
		if ( $str == 'comments' ) {
			$this->getThirdServices();
			$this->checkUserInfo($this->services['comments']);
		}
		if ( $str == 'googleanalytics' ) {
			$this->getThirdServices();
			$this->checkUserInfo($this->services['stats']);
		}
		if ( $str == 'twitterstream' ) {
			$this->getThirdWidgets();
		}
		if ( file_exists('./'.$this->theme['directory'].$file) ) {
			include('./'.$this->theme['directory'].$file);
		}
		elseif ( file_exists('./'.DIR_SNIPPETS.$file) ) {
			include('./'.DIR_SNIPPETS.$file);
		}
		else {
			return 'Missing the &ldquo;<b>'.$str.'</b>&rdquo; snippet.';
		}
	}

	/**
	 * Get all third party service info from db
	 */
	protected function getThirdServices() {
		if ( is_null($this->services) ) {
			$cols = array(
				's.label AS `service`',
				'user_info AS `value`',
				'f.title AS `function`'
			);
			$result = $this->db
				->join('third_match m', 's.id = m.service_id', 'LEFT')
				->join('third_function f', 'm.function_id = f.id', 'LEFT')
				->where('active', 1)
				->get('third_service s',null,$cols);
			if ( $result ) {
				foreach ( $result as $val ) {
					$label = mb_strtolower($val['function'],"UTF-8");
					!$val['value'] ? $val['value'] = 1 : $val['value'];
					$this->services[$label][$val['service']] = $val['value'];
				}
			}
		}
	}

	/**
	 * Get all third widgets info from db. Currently only a twitter user timeline.
	 */
	protected function getThirdWidgets() {
		if ( is_null($this->widgets) ) {
			$cols = array(
				's.user_info AS user',
				'w.label',
				'w.value AS widget'
			);
			$result = $this->db
				->join('third_service s','s.id = w.service_id','INNER')
				->where('active',1)
				->get('third_widget w',null,$cols);
			if ( $result ) {
				foreach ( $result as $key=>$val ) {
					if ( $val['user'] && $val['widget'] ) {
						$this->widgets[$val['label']] = array('user'=>$val['user'],'widget'=>$val['widget']);
					}
					// add the js file info
					if ( $val['label'] == 'twitter_timeline' ) {
						$this->widgets[$val['label']]['link'] = '<script type="text/javascript" src="'.DIR_SCRIPTS.'twitterFetcher.min.js"></script>'."\n";
					}
				}
			}
		}
	}

	/**
	 * Unset any service link if the user_info isn't valid
	 *
	 * @param array $list - a section of the services list to check
	 */
	protected function checkUserInfo(&$list) {
		if ( $list ) {
			foreach ( $list as $key=>$val ) {
				if ( $val == 1 || empty($val) ) {
					unset($list[$key]);
				}
			}
		}
	}

	/**
	 * Get info to include in the share link
	 */
	protected function getShareContent() {
		$this->services['share']['url'] = $this->content['permalink'];
//		$this->prependDomainName($this->services['share']['url']);
		if ( $this->imageInfo[0] ) {
			$this->services['share']['image'] = $this->imageInfo[0]['url'];
			$this->prependDomainName($this->services['share']['image']);
		}
		$title = '“'.$this->bookInfo['title'].'” by '.$this->milieu['artist_name'];
		if ( $this->content['page_title'] ) {
			$title = '“'.$this->content['page_title'].'” from '.$title;
		}
		$this->services['share']['title'] = urlencode($title);
	}

	/**
	 * Format menu items, ignore those not in menu
	 *
	 * @param string $str - keyword used in the page templates, matches to content array key
	 * @return string $output - HTML for item
	 */
	public function returnShowOutput($str=null) {
		$output = '';
		if ( array_key_exists($str, $this->content) ) {
			$output = $this->content[$str];
		}
		if ( !empty($this->theme['slots']) && array_key_exists($str, $this->theme['slots']) ) {
			$output = $this->formatSlotImage($str);
		}
		if ( $str == 'links' ) {
			$output = $this->formatLinkList();
		}
		if ( $str == 'favicons' ) {
			$output = $this->formatFavicons();
		}
		return $output;
	}

	/**
	 * Pick a random ad from pool and return the HTML
	 *
	 * @param string $str - keyword used in the page templates, matches ad slot label
	 * @return string $output - HTML for item
	 */
	public function returnAdOutput($str=null) {

		if ( is_null($this->ads) ) {
			$this->getAdPool();
		}

		if ( isset($this->ads) && array_key_exists($str,$this->ads) ) {
			$pool = array_values($this->ads[$str]); // numerically index $pool
			$count = count($pool);
			$rand = rand(0, $count-1);
			$ad = $pool[$rand];

			if ( $ad['large_image_url'] && $ad['tap_url'] ) {
//				$this->prependDomainName($ad['tap_url']);
				$output = '<a href="'.$ad['tap_url'].'"><img src="'.$ad['large_image_url'].'" alt="ad"></a>';
			}
			else {
				$output = $ad['code'];
			}
		}
		return $output ?? null;
	}

	/**
	 * Get all potential ads that correspond to this theme's ad slots
	 */

	protected function getAdPool() {
		$cols = array(
			'ts.label',
			'ar.id AS ad_id',
			'ar.large_image_url',
			'ar.tap_url',
			'ar.code'
		);
		$result = $this->db
			->join('ad_slot_match asm','asm.slot_id = ts.id','LEFT')
			->join('ad_reference ar','ar.id = asm.ad_reference_id','LEFT')
			->where('ts.theme_id',1)
			->where('ts.type','ad')
			->get('theme_slot ts',null,$cols);

		// Organize results
		if ( !empty($result) ) {
			foreach ( $result as $ad ) {
				if ( is_numeric($ad['ad_id']) ) {
					$slot = $ad['label'];
					unset($ad['label']);
					// At least these bits need to be here
					if ( $ad['large_image_url'] && $ad['tap_url'] ) {
						$list[$slot][$ad['ad_id']] = $ad;
					}
					elseif ( $ad['code'] ) {
						$list[$slot][$ad['ad_id']] = $ad;
					}
				}
			}
		}
		$this->ads = $list ?? null;
	}


	/**
	 * Format an image tag from slot info
	 *
	 * @param string $str - keyword used in the page templates, matches to array key
	 * @return string $tag - image tag
	 */
	protected function formatSlotImage($str=null) {
		$img = $this->theme['slots'][$str];
		$tag = '<img id="'.$img['label'].'" src="'.$img['url'].'" alt="'.$img['description'].'"/>';
		return $tag;
	}

	/**
	 * Format link list
	 *
	 * @return string output - <li> formatted collection
	 */
	protected function formatLinkList() {
		$list = $this->db
			->orderBy('sort_order','ASC')
			->get('link_list',null);
		$this->pageInfo['links'] = build_link_list($list);
		return $this->pageInfo['links'];
	}

}
