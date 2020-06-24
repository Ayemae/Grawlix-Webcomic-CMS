<?php

/**
 * Specific to JSON feeds
 */

class GrlxPage_JSON extends GrlxPage {

	protected $xml;
	protected $xmlVersion;
	protected $httpHeader = 'Content-Type: application/json; charset=utf-8';
	protected $display;
	protected $feedItems;

	/**
	 * Set defaults, etc.
	 */
	public function __construct() {

		parent::__construct(func_get_args());

		// Get XML feed options for this book.
		$this->setBook();
		if ( substr($this->bookInfo['options'], 0,5) == '<?xml' ) {
			$args['stringXML'] = $this->bookInfo['options'];
			$this->xml = new GrlxXMLPublic($args);
			$this->xmlVersion = $this->xml->version;
			$this->routeVersion();
		}

		// A few defaults, just in case.
		if ( !$this->display ) {
			$this->display = array('title','number');
		}
	}

	/**
	 * Get requested book if it's not the default
	 */
	protected function setBook() {
		if ($this->query['id'] && is_numeric($this->query['id']))
		{
			$book_id = $this->query['id'];
		}
		else
		{
			$book_id = 1; // HARDCODED for testing.
		}

		// Root folder or subfolder?
		if ( $this->path[1] == '/json') {
			if ( $book_id ) {
				$this->getBookInfo('id',$book_id);
			}
			else
			{
				$this->getBookInfo();
			}
		}
		elseif ( $this->path[2] == '/json' && $this->path[1] != $this->bookInfo['url'] ) {
			$this->getBookInfo('url');
		}
	}

	/**
	 * Route the page build according to xml version number
	 */
	protected function routeVersion() {
		switch ( $this->xmlVersion ) {
			case '1.1':
				$this->display = $this->xml->getClones('/rss','option');
				break;
			default:
				echo('Incompatible info');
				break;
		}
	}

	/**
	 * Put the page together
	 */
	public function buildPage() {
		$this->getPages();
		$this->formatFeedItems();
		$this->formatOutput();
	}

	/**
	 * Get all published book pages with their images, if needed
	 */
	protected function getPages() {

		// Get comic page info.
		$cols[] = 'bp.id';
		$cols[] = 'bp.sort_order';
		$cols[] = 'DATE_FORMAT(bp.date_publish,"%a, %d %b %Y 00:00:00 GMT") AS date_publish';
		$this->db->where('bp.book_id',$this->bookInfo['id']);
		$this->db->where('bp.date_publish <= NOW()');
		$this->db->where('bp.sort_order',$this->bookInfo['latest_page'],'<=');
		$this->db->orderBy('bp.sort_order','DESC');
		foreach ( $this->display as $str ) {
			if ( $str != 'number' ) { // always get sort_order
				$cols[] = 'bp.'.$str;
			}
		}
		$result = $this->db->get('book_page bp',null);
		if ( $result ) {
			foreach ( $result as $i=>$array ) {

				if ( in_array('image', $this->display) )
				{
					
					// Get the related images.
					$cols = array(
						'ir.id',
						'ir.url',
						'ir.description',
						'im.sort_order'
					);
					$array['image_info'] = $this->db
						->join('image_reference ir','ir.id = im.image_reference_id','LEFT')
						->where('im.rel_id',$array['id'])
						->where('im.rel_type','page')
						->orderBy('im.sort_order','ASC')
						->get('image_match im',null,$cols);
				}
				// Correct for sort_order.
				$sortOrder = (integer)$array['sort_order'];
				foreach ( $array as $key=>$val ) {
					if ( $key == 'sort_order' ) {
						$val = $sortOrder;
					}
					$this->feedItems[$array['id']][$key] = $val;
				}
				$this->feedItems[$array['id']]['permalink']  = 'http://'.$this->domainName;
				$this->feedItems[$array['id']]['permalink'] .= $this->buildPermalink($sortOrder,'page');
			}
		}
	}

	/**
	 * Arrange and add any HTML to appropriate items
	 */
	protected function formatFeedItems() {
		if ( $this->feedItems )
		{
			foreach ( $this->feedItems as $i=>$array ) {
				// Item title
				if ( in_array('number', $this->display) && $array['sort_order'])
				{
					$title = 'Page '.$array['sort_order'].': ';
				}
				$array['title'] ? $title = $array['title'] : $title;

				if ( in_array('description', $this->display) && $array['description'])
				{
					$this->feedItems[$i]['title'] = $array['sort_order'].'. '.$title;
				}
				else
				{
					$this->feedItems[$i]['title'] = $title;
				}

				// Any other text
				$text = array(); // reset
	
				if ( in_array('description', $this->display) && $array['description'])
				{
					$text[] = '<p>'.$array['description'].'</p>';
				}
				if ( in_array('blog', $this->display) && $array['blog_post'])
				{
					$text[] = '<h3>'.$array['blog_title'].'</h3>';
					$this->styleMarkdown($array['blog_post']);
					$text[] = $array['blog_post'];
				}
				if ( in_array('transcript', $this->display) && $array['transcript'])
				{
					$this->styleMarkdown($array['transcript']);
					$text[] = $array['transcript'];
				}
				$text ? $text = implode('',$text) : $text = '';
				// Add source info in case of content scrapers
				$text .= '<p>This content originally published by '.$this->milieu['artist_name'].' at <a href=\''.$array['permalink'].'\'>'.$this->bookInfo['title'].'</a>.</p>';
				$this->feedItems[$i]['description'] = $text;
			}
		}
	}

	/**
	 * Format and print
	 */
	protected function formatOutput() {
//echo '<pre>$this->bookInfo|';print_r($this->bookInfo);echo '|</pre>';
		$output  = '{'."\n";
		$output .= '"version" : "https://jsonfeed.org/version/1",'."\n";
		$output .= '"title" : "'.$this->bookInfo['title'].'",'."\n";
		$output .= '"home_page_url" : "http://'.$this->domainName.'",'."\n";
		$output .= '"feed_url" : "http://'.$this->domainName.'/json",'."\n";
		$output .= '"author" : {'."\n";
		$output .= '	"name" : "'.$this->milieu['artist_name'].'"'."\n";
		$output .= '},'."\n";
		$output .= '"icon" : "http://'.$this->domainName.$this->bookInfo['url'].'/assets/system/favicons/apple-touch-icon-72x72",'."\n";
		$output .= '"favicon" : "http://'.$this->domainName.$this->bookInfo['url'].'/assets/system/favicons/apple-touch-icon-72x72",'."\n";
		$output .= '"items" : ['."\n";

		if ( $this->feedItems )
		{
			foreach ( $this->feedItems as $page ) {
				$item  = ''; // reset
				$item .= '{'."\n";
				$item .= '	"title" : "'.$page['title'].'",'."\n";
				$item .= '	"date_published" : "'.$page['date_publish'].'",'."\n";
				if ( $page['options'] )
				{
					$item .= '	"id" : "'.$page['permalink'].'-'.$page['options'].'",'."\n";
					$item .= '	"url" : "'.$page['permalink'].'-'.$page['options'].'",'."\n";
				}
				else
				{
					$item .= '	"id" : "'.$page['permalink'].'",'."\n";
					$item .= '	"url" : "'.$page['permalink'].'",'."\n";
				}
				$item .= '	"author" : {'."\n";
				$item .= '		"name" : "'.$this->milieu['artist_name'].'"'."\n";
				$item .= '	},'."\n";
				if ( in_array('image', $this->display) && $page['image_info'] && count($page['image_info']) > 0)
				{
					$item .= '	"image" : "http://'.$this->domainName.$page['image_info'][0]['url'].'",'."\n";
				}

				$item .= '	"content_html" : "'.$page['description'].'"'."\n";
				$item .= '}'."\n";
				$item_list[] = $item;
			}
		}
		if ( $item_list )
		{
			$output .= implode(',',$item_list);
		}
		$output .= 	"]\n}";
		print($output);

	}
}
