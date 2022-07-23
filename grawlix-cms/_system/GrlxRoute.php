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
	public    $request;
	public    $path;
	public    $query;

	/**
	 * All the action happens here
	 */
	public function __construct() {
		global $_db;
		$this->db = $_db;
		$this->getArgs(func_get_args());
		$this->request = htmlspecialchars($_SERVER['REQUEST_URI'],ENT_COMPAT);
		$this->parseRequest();
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
		$part = parse_url($this->request);
		if ( $this->milieu['directory'] != '' ) {
			$part['path'] = str_replace($this->milieu['directory'], '', $part['path']);
		}
		$this->parseRequestPath($part['path']);
	  	if ( $part['query'] ) {
		  	$this->parseRequestQuery($part['query']);
	 	}
	}

	/**
	 * Build an array of the path segments
	 *
	 * @param string $str - path string
	 */
	protected function parseRequestPath($str=null) {
		if ( $str != '/' ) {
			remove_trailing_slash($str);
			$path = explode('/',$str);
			array_walk($path,'prepend_slash'); // Put forward slash back
			$this->path = $path;
			if ( $this->path[1] == $this->milieu['directory'] ) {
				array_shift($this->path);
			}
		}
		else {
			$this->path[0] = '/';
		}
	}

	/**
	 * Get query vars from the request string and build associative array
	 *
	 * @param string $str - string list of arguments
	 */
	protected function parseRequestQuery($str=null) {
		$array = explode('&amp;',$str);
	 	foreach ( $array as $item ) {
		 	$val = explode('=',$item);
		 	$this->query[$val[0]] = $val[1];
	 	}
		if ( array_key_exists('ckcachecontrol',$this->query) ) {
			unset($this->query['ckcachecontrol']); // Ignore codekit cache control
		}
		if ( array_key_exists('bookmark1',$this->query) ) {
			$this->setBookmark($_SERVER['REQUEST_URI']);
		}
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
		$levelCount = count($this->path);
		$base = $this->path[0];
		$p1 = $this->path[1];
		if ( $levelCount == 1 || $p1 == '/index.php' ) {
			switch ( $this->menu['/']['rel_type'] ) {
				case 'book':
					$route = 'comic-home';
					break;
				case 'static':
					$route = 'static';
					break;
			}
		}
		elseif ( in_array('/rss', $this->path ))
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

	/**
	 * For requests one level deep such as “/about”
	 *
	 * @return string $route - keyphrase for route
	 */
	protected function requestLevelOne() {
		switch ( $this->menu[$this->path[1]]['rel_type'] ) {
			case 'book':
				$this->query ? $route = 'comic-inside' : $route = 'comic-home';
				break;
			case 'static':
				$route = 'static';
				break;
		}
		return $route;
	}

	/**
	 * For requests two levels deep such as “/mycomic/123”
	 *
	 * @return string $route - keyphrase for route
	 */
	protected function requestLevelTwo() {
		$str = $this->path[1].$this->path[2];
		if ( $this->menu[$str] && ($this->menu[$str]['rel_type'] == 'archive') ) {
			$route = 'comic-archive';
		}
		$x = $this->path[2];
		remove_first_slash($x);
		$x = explode('-',$x);
		if ( $this->menu[$this->path[1]] && is_numeric($x[0]) ) {
			$route = 'comic-inside';
		}
		return $route;
	}
}

