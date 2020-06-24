<?php

/**
 * Specific to RSS feeds
 */

class GrlxPage_RSS extends GrlxPage {

	protected $xml;
	protected $xmlVersion;
	protected $httpHeader = 'Content-Type: application/rss+xml; charset=utf-8';
	protected $display;
	protected $feedItems;

	/**
	 * Set defaults, etc.
	 */
	public function __construct() {

		parent::__construct(func_get_args());


		$this->setBook();
		if ( substr($this->bookInfo['options'], 0,5) == '<?xml' ) {
			$args['stringXML'] = $this->bookInfo['options'];
			$this->xml = new GrlxXMLPublic($args);
			$this->xmlVersion = $this->xml->version;
			$this->routeVersion();
		}
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
		if ( $this->path[1] == '/rss') {
			if ( $book_id ) {
				$this->getBookInfo('id',$book_id);
			}
			else
			{
				$this->getBookInfo();
			}
		}
		elseif ( $this->path[2] == '/rss' && $this->path[1] != $this->bookInfo['url'] ) {
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
		if($this->feedItems)
		{
			foreach ( $this->feedItems as $i=>$array ) {
				// Item title
				$title = 'Page '.$array['sort_order'];
				$array['title'] ? $title .= ': '.$array['title'] : $title;
				$this->feedItems[$i]['title'] = $title;
				// Any other text
				$text = array();
	
				if ( in_array('image', $this->display) && $array['image_info'] && count($array['image_info']) > 0)
				{
					$text[] = '<img src="http://'.$this->domainName.$array['image_info'][0]['url'].'" alt="'.$array['title'].'">';
				}
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
				$text .= '<p>This content originally published by '.$this->milieu['artist_name'].' at <a href="'.$array['permalink'].'">'.$this->bookInfo['title'].'</a>.</p>';
				$this->feedItems[$i]['description'] = $text;
			}
		}
	}

	/**
	 * Format and print
	 */
	protected function formatOutput() {

		$output  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$output .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
		$output .= '	<channel>'."\n";
		$output .= '		<atom:link href="'.$_SERVER['SCRIPT_URI'].'" rel="self" type="application/rss+xml" />'."\n";
		$output .= '		<title><![CDATA['.$this->bookInfo['title'].']]></title>'."\n";
		$output .= '		<description><![CDATA['.$this->bookInfo['description'].']]></description>'."\n";
		$output .= '		<link>http://'.$this->domainName.$this->bookInfo['url'].'</link>'."\n";
//		$output .= '<author>'.$this->milieu['artist_name'].'</author>'."\n";
		$output .= '		<generator>The Grawlix CMS</generator>'."\n";
		if($this->feedItems)
		{
			foreach ( $this->feedItems as $page ) {
				$output .= '		<item>'."\n";
				$output .= '			<pubDate>'.$page['date_publish'].'</pubDate>'."\n";
				$output .= '			<title><![CDATA['.$page['title'].']]></title>'."\n";
				if ( $page['options'] && $page['options'] != '' )
				{
					$output .= '			<guid>'.$page['permalink'].'-'.$page['options'].'</guid>'."\n";
				}
				else
				{
					$output .= '			<guid>'.$page['permalink'].'</guid>'."\n";
				}
				if ( $page['description'] ) {
					$output .= '			<description><![CDATA['.$page['description'].']]></description>'."\n";
				}
				$output .= '		</item>'."\n";
			}
		}
		$output .= '	</channel>'."\n";
		$output .= '</rss>'."\n";
		print($output);
	}
}
