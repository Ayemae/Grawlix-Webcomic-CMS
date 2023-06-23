<?php

/**
 * Specific to front-end comic pages
 */

class GrlxPage2_Comic extends GrlxPage2 {

	protected $where;
	protected $orderBy;
	protected $sqlQuery;
	protected $isFirst;
	protected $isLatest;

	/**
	 * Set defaults, etc.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Use page request to determine correct action.
	 *
	 * @param		object		$grlxRequest
	 */
	public function contents($request)
	{
		parent::contents($request);

		if (is_array($request->query))
		{
			// Get a page by its sort order
			if (array_key_exists('sort_order', $request->query))
			{
				$this->where['sort_order'] = $request->query['sort_order'];
			}
			$this->getComicPage();
		}
		// Load comic home
		else
		{
			$this->getComicHome();
		}
	}

	/**
	 * Shortcut for a comic’s home page
	 */
	public function getComicHome() {
		$this->canonicalLink = $this->bookInfo['url']; // Don’t use a comic page for the comic home canonical link
		$this->where['sort_order'] = $this->bookInfo['latest_page']; //TODO: Add a milieu field to allow defaulting to the first page instead
		$this->isLatest = true;
		$this->getComicPage();
	}

	/**
	 * Shortcut for an inside comic page
	 */
	public function getComicInside() {
		if ( $this->query ) {
			$this->getVarsComicNav();
		}
		// Permalink scheme matters here
		else {
			$x = $this->path[2];
			remove_first_slash($x);
			$this->where['sort_order'] = $x;
		}
		$this->getComicPage();
	}

	/**
	 * Catch the first, prev, random, next, latest queries
	 */
	protected function getVarsComicNav() {
		if ( array_key_exists('sort',$this->query) ) { // Select page by its sort order
			$x = $this->query['sort'];
			switch ( $x ) {
				case 'first':
					$this->isFirst = true;
					$this->where['sort_order'] = 1;
					break;
				case 'latest':
					$this->isLatest = true;
					$this->where['sort_order'] = $this->bookInfo['latest_page'];
					break;
				case 'random':
					// <http://jan.kneschke.de/projects/mysql/order-by-rand/> writes ORDER BY RAND() is too slow
					$max = (int) $this->bookInfo['latest_page'];
					$rand = rand(1, $max);
					$this->where['sort_order'] = $rand;
					$rand == 1 ? $this->isFirst = true : $rand;
					$rand == $max ? $this->isLatest = true : $rand;
					break;
				default:
					is_numeric($x) ? $x = $x : $x = 1;
					$this->where['sort_order'] = $x;
					break;
			}
		}
	}

	/**
	 * Get comic page info
	 */
	protected function getComicPage() {
		$cols = array(
			'id AS page_id',
			'title AS page_title',
			'description AS meta_description',
			'book_id',
			'tone_id',
			'marker_id',
			'sort_order',
			'blog_title',
			'blog_post',
			'transcript',
			'date_publish',
			'options'
		);
		$this->db->where('date_publish <= NOW()');
		$this->db->where('sort_order',$this->bookInfo['latest_page'],'<=');
		$this->db->where('book_id',$this->bookInfo['id']);
		if ( $this->where ) {
			foreach ( $this->where as $key=>$val ) {
				$this->db->where($key,$val);
				$sortRequest = (integer)$val; // if page does not exist
			}
		}
		if ( $this->orderBy ) {
			foreach ( $this->orderBy as $key=>$val ) {
				$this->db->orderBy($key,$val);
			}
		}
		$result = $this->db->getOne('book_page bp',$cols);
		if ( $result ) {
			switch ( $result['sort_order'] ) {
				case 1:
					$this->isFirst = true;
					break;
				case $this->bookInfo['latest_page']:
					$this->isLatest = true;
					break;
			}
			$result['sort_order'] = (integer)$result['sort_order'];
			if ( $result['options'] )
			{
				$result['permalink'] = $this->buildPermalink($result['sort_order'],'page').'-'.$result['options'];
			}
			else
			{
				$result['permalink'] = $this->buildPermalink($result['sort_order'],'page');
			}
			$this->prependDomainName($result['permalink']);
			$result['permalink'] = 'http://'.$result['permalink'];
			if ( !$result['page_title'] ) {
				$result['page_title'] = 'Page '.$result['sort_order'];
			}
			$result['edit_this']['text'] = 'Edit comic page';
			$result['edit_this']['link'] = 'book.page-edit.php?page_id='.$result['page_id'];
			
			//Get any markers relevant to this page:
			//First, we create a subquery that gets the most recent page per marker type.
			//We can't get the marker name, etc in this subquery due to MySQL limitations.
			$cols = array(
				'marker.marker_type_id AS sub_marker_type_id',
				'MAX(bp.sort_order) AS sub_sort_order'
			);
			$subquery = $this->db->subQuery('recentMarkers');
			$subquery->join('marker marker', 'marker.id = bp.marker_id', 'INNER') //we have to name the marker table because in truth it's actually something like grlx_marker
			->where('bp.sort_order <= '.$result['sort_order'])
			->groupBy('marker.marker_type_id')
			->get('book_page bp',null,$cols);
			//Then, using the returned pages, get the actual marker titles, ranks, etc:
			$cols = array(
				'type.`rank` AS `rank`',
				'sub_sort_order AS sort_order',
				'marker.title AS marker_title'
				//'type.title AS marker_type',
				//'marker.id AS marker_id'
			);
			$allMarkers = $this->db
			->join($subquery, 'sub_marker_type_id = type.id', 'INNER')
			->join('book_page bp', 'bp.sort_order = sub_sort_order', 'INNER')
			->join('marker marker', 'bp.marker_id = marker.id', 'INNER')
			->orderBy('type.`rank`', 'ASC')
			->get('marker_type type',null,$cols);
			//And finally, filter out any child markers that aren't inside the current parent marker:
			if($allMarkers) {
				$parentMarker = 0;
				foreach($allMarkers as $marker) {
					if($marker['sort_order'] >= $parentMarker) { //valid marker
						$result['marker_title_'.$marker['rank']] = $marker['marker_title'];
						$parentMarker = $marker['sort_order'];
					}
				}
			}
			
			$this->pageInfo = $result;
			$this->pageInfo['book_description'] = $this->bookInfo['description'];
			$this->buildComicNavURLs();
			$this->getComicImages();
			$this->getImageURL('0');
			$this->formatComicParts();
		}
		else {
			// A quick page not found view for when a page outside of the published set is requested
			$this->statusCode = 404;
			$this->template = $this->templateFileList['static'];
			$this->pageInfo['page_content']  = '<h2>Page '.$sortRequest.' not found!</h2>';
			$this->pageInfo['page_content'] .= '<p>Maybe it doesn’t exist. Or maybe you’re not allowed to see it yet.</p>';
		}
		if ( $result && !empty($result['tone_id']) ) {
			$this->theme['tone_id'] = $result['tone_id'];
		}
	}

	/**
	 * Build URL-only strings for comic nav links
	 * Always uses sort order for previous and next
	 */
	protected function buildComicNavURLs() {
		$prev = $this->pageInfo['sort_order'] - 1;
		$prev < 1 ? $prev = 1 : $prev;
		$next = $this->pageInfo['sort_order'] + 1;

		// Set some defaults.
		$navLinks['first']['url']     = $this->milieu['directory'].$this->bookInfo['url'];
		$navLinks['prev']['url']      = $this->milieu['directory'].$this->bookInfo['url'];
		$navLinks['rand']['url']      = $this->milieu['directory'].$this->bookInfo['url'].'?sort=random';
		$navLinks['next']['url']      = $this->milieu['directory'].$this->bookInfo['url'];
		$navLinks['latest']['url']    = $this->milieu['directory'].$this->bookInfo['url'];

		// Pages after sort_order 1
		if ( !$this->isFirst ) {
			$navLinks['first']['url']  .= '/1';
			$navLinks['prev']['url']   .= '/'.$prev;
		}

		// Nav links before the most recent comic page
		if ( !$this->isLatest ) {
			$navLinks['next']['url']   .= '/'.$next;
			$navLinks['latest']['url'] .= '/';
		}

		// The first comic page
		if ( $this->isFirst ) {
			$navLinks['first']['css']   = ' disabled';
			$navLinks['prev']['css']    = ' disabled';
		}

		// The latest comic page
		if ( $this->isLatest ) {
			$navLinks['next']['css']    = ' disabled';
			$navLinks['latest']['css']  = ' disabled';
		}

		// Get custom page URL info.
		if ( $navLinks ) {
			foreach ( $navLinks as $key => $val ) {
				$test_url = explode('/',$val['url']);
				$test_url = array_pop($test_url);
				if ( is_numeric($test_url)) {
					$this->db->where('book_id', '1'); // HARDCODED for testing
					$this->db->where('sort_order', $test_url);
					$x = $this->db->getOne('book_page', 'options');
					if ($x['options'] && $x['options'] != '') {
						$navLinks[$key]['url'] .= '-'.$x['options'];
					}
				}
			}
		}

		$this->navLinks['comic'] = $navLinks;
	}

	/**
	 * Format the rest of the comic pieces
	 */
	protected function formatComicParts() {
		if ( $this->pageInfo['blog_post'] ) {
			$this->styleMarkdown($this->pageInfo['blog_post']);
		}
		if ( $this->pageInfo['transcript'] ) {
			$this->pageInfo['raw_transcript'] = $this->pageInfo['transcript'];
			$this->styleMarkdown($this->pageInfo['transcript']);
		}
	}

	/**
	 * Fetch an URL for the main image, mostly for custom <img> generation.
	 */
	protected function getImageURL($which=0)
	{
		if ( isset($this->imageInfo) )
		{
			return $this->milieu['firstImageURL'] = $this->imageInfo[$which]['url'];
		}
		return '';
	}

	/**
	 * Get comic images for a the specified comic page id
	 */
	protected function getComicImages() {
		$cols = array(
			'ir.id',
			'ir.url',
			'ir.description',
			'im.sort_order'
		);
		$result = $this->db
			->join('image_reference ir','ir.id = im.image_reference_id','LEFT')
			->where('im.rel_id',$this->pageInfo['page_id'])
			->where('im.rel_type','page')
			->orderBy('im.sort_order','ASC')
			->get('image_match im',null,$cols);
		if ( $result ) {
			$this->imageInfo = $result;
			$this->formatComicImages();
		}
	}

	/**
	 * Format alt and title attributes for given image
	 *
	 * @param string $str - text from db
	 * @return string $attr = formatted html
	 */
	protected function formatAttributes($str=null) {
		if ( $str !== null && $str != '' ) {
			$attr = ' alt="'.$str.'" title="'.$str.'"';
		}
		else {
			$attr = ' alt="image"';
		}
		return $attr;
	}

	/**
	 * Format a given web path correctly
	 *
	 * @param string $path - path
	 */
	protected function formatWebPath(&$path) {
		if ( $path ) {
			if ( substr($path,0,4) != 'http' && substr($path,0,2) != '//' ) {
				$path = $this->milieu['directory'].$path;
				$path = str_replace('//','/',$path);
				if ( substr($path,0,1) != '/' )
				{
					$path = '/'.$path;
				}
		}
			return $path;
		}
	}

	/**
	 * Format one or more comic images for display
	 */
	protected function formatComicImages() {
		$count = count($this->imageInfo);
		if ( $count == 1 ) {
			$image = $this->imageInfo[0];
			$attr = $this->formatAttributes($image['description']);
			$src = $image['url'];
			$src = $this->formatWebPath($src);
			$output = '<img itemprop="image" src="'.$src.'"'.$attr.'/>';
		}
		else {
			$output = '<ol class="comic-images">';
			foreach ( $this->imageInfo as $image ) {
				$image['url'] = str_replace('//', '/', $image['url']); // Stopgap patch for the 1.0.0 slash problem.
				$attr = $this->formatAttributes($image['description']);
				$src = $image['url'];
				$src = $this->formatWebPath($src);
				$output .= '<li class="comic-'.$image['sort_order'].'">';
				$output .= '<img itemprop="image" src="'.$src.'"'.$attr.'/>';
				$output .= '</li>';
			}
			$output .= '</ol>';
		}
		$this->pageInfo['comic_image'] = $output;
	}
}
