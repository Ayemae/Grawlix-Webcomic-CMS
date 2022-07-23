<?php

/**
 * Get URL request and route it
 *
 * # Instantiate:
 * $route = new GrlxRoute;
 */

class GrlxRoute {

	protected $db;
	protected $milieu;
	protected $menu;
	protected $key;
	protected $known_url_list;
	public    $request;
	public    $path;
	public    $query;
	public    $info;

	/**
	 * All the action happens here
	 */
	public function __construct() {
		global $_db;
		$this->db = $_db;
		$this->getArgs(func_get_args());
		$this->request = htmlspecialchars($_SERVER['REQUEST_URI'],ENT_COMPAT);
		$this->info = $this->parseRequest();
	}

	/**
	 * Get arguments passed through constructor
	 *
	 * @param array $list - arguments from index.php
	 */
	protected function getArgs($list=null) {
		$list = $list[0];
		if ( isset($list) ) {
			foreach ( $list as $key => $val ) {
				if ( property_exists($this, $key) ) {
					$this->{$key} = $val;
				}
			}
		}
	}

	/**
	 * Take request string and break it into arrays by path and queries
	 */
	protected function parseRequest() {
		global $_db; // TODO: Make this less hacky.

		$url_info = parse_url($this->request);
		if ($url_info && is_array($url_info))
		{
			// Get all URLs in the database.
			$this->known_url_list = $_db->get('path',NULL,'rel_type,rel_id,url');
			$url_parts = explode('/',$url_info['path']);
			unset($url_parts[0]); // The first one’s always blank because the string starts with a slash.
		}

		// Make it easier to find records based on their URL.
		if ( $this->known_url_list )
		{
			foreach ( $this->known_url_list as $key => $val )
			{
				$this->known_url_list[$val['url']] = $val;

				// Automatically add feed URLs.
				if ($val['rel_type'] == 'book')
				{
					$this->known_url_list[$val['url'].'/rss'] = array(
						'rel_type' => 'rss',
						'rel_id' => $val['rel_id'],
						'url' => $val['url'].'/rss'
					);
					$this->known_url_list[$val['url'].'/json'] = array(
						'rel_type' => 'json',
						'rel_id' => $val['rel_id'],
						'url' => $val['url'].'/json'
					);
				}
			}
		}

		// Got an EXACT match? Return it.
		if ($this->known_url_list[$this->request])
		{
			return $this->known_url_list[$this->request];
		}

		// No match, huh? Then walk along each part of the URL until you find one.
		if ($url_parts)
		{

			// Do it backwards to get as granular as possible.
			$url_parts = array_reverse($url_parts);

			foreach ( $url_parts as $key => $val )
			{
				// Is there an exact match?
				if($this->known_url_list['/'.$val])
				{
					return $this->known_url_list['/'.$val];
				}

				// Maybe it’s a comic page.
				$result = $this->seekComicPage($val);
				if ($result)
				{
					return $result;
				}

 			}
 		}

 		// If you’ve gotten this far, then return an error.
 		return $this->known_url_list['/404'];

	}


	protected function seekComicPage($slug=NULL)
	{
		global $_db;
		if ($slug)
		{

			// This is an exception: comic page URLs are stored without slashes 
			// because at first they were prefixed with their sort_order values.
			if(substr($slug,0,1) == '/')
			{
				$slug = substr($slug,1);
			}

			// Is the first bit a number, e.g. a sort_order?
			$slug_parts = explode('-',$slug);
			if (is_numeric($slug_parts[0]))
			{
				$_db->where('sort_order',$slug_parts[0]);
				$result = $_db->getOne('book_page','id');
				if ($result)
				{
					return array(
						'rel_type' => 'comic-inside',
						'rel_id' => $result['id']
					);
				}
			}

			$_db->where('options',$slug);
			$result = $_db->getOne('book_page','id');
			if ($result)
			{
				return array(
					'rel_type' => 'comic-inside',
					'rel_id' => $result['id']
				);
			}
		}
		return NULL;
	}

	/**
	 * Set a bookmark in user's cookie
	 *
	 * @param string $path - an URL
	 */
	protected function setBookmark($path=null) {
		setcookie('bookmark1',$path);
	}

	/**
	 * Select the correct route based on the request
	 *
	 * @return string $route - keyphrase for route
	 */
	public function setRoute() {
		if ( in_array('/rss', $this->path ))
		{
			$route = 'rss';
		}
		elseif ( in_array('/json', $this->path ))
		{
			$route = 'json';
		}
		else {
			$levelCount--;
			switch ( $levelCount ) {
				case 1:
					$route = $this->requestLevelOne();
					break;
				case 2:
				case 3:
					$route = $this->requestLevelTwo();
					break;
			}
		}
		$route ? $route : $route = '404';
		return $route;
	}
}

