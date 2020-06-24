<?php

/**
 * Use items from path table to define front-end routes.
 *
 */

class GrlxRoute2 {

	public $route = array();

	/**
	 * Accept $menu_list from index.php
	 *
	 * @param		array		$menu_list from index.php
	 */
	public function __construct($list)
	{
		ksort($list);
		$this->parseList($list);
	}

	/**
	 * Start a routes list with the db data
	 *
	 * @param		array
	 * @vars			array		$route
	 */
	public function parseList($list)
	{
		foreach ($list as $key => $arr)
		{
			$temp = array();
			$controller = $arr['rel_type'];
			$controller == 'book' ? $controller = 'comic' : $controller;

			$temp['template'] = $controller;
			$temp['controller'] = ucfirst($controller);

			switch ($controller)
			{
				case 'comic':
					$temp['book_id'] = $arr['rel_id'];
					$this->route[$key] = $temp;

					// Generate additional routes
					if ($key != '/')
					{
						$this->buildBookRoutes($key);
					}
					break;

				case 'static':
					$temp['u_id'] = $arr['rel_id'];
					$this->route[$key] = $temp;
					break;
			}
		}
	}

	/**
	 * Generate additional routes for books
	 *
	 * @param		string
	 * @vars			array		$route
	 */
	private function buildBookRoutes($slug)
	{
		// Build routes for these
		$build = array(
			ARCHIVE => ucfirst(ARCHIVE),
			'json'  => 'JSON',
			'rss'   => 'RSS'
		);

		$info = (object) $this->route[$slug];

		foreach ($build as $var => $val)
		{
			$temp = array(
				'template'   => $var,
				'controller' => $val,
				'book_id'    => $info->book_id
			);

			$this->route[$slug.'/'.$var] = $temp;
		}
	}

	/**
	 * Match request to a route
	 *
	 * @param		object		request path and query
	 * @return		object		controller and u_id
	 */
	public function getRoute($obj)
	{
		$match = $this->route[$obj->path];

		// Assign anything else as 404
		if (empty($match))
		{
			$match = $this->route['/404'];
		}

		return (object) $match;
	}

}

