<?php

/**
 * Parse REQUEST_URI into arrays.
 *
 */

class GrlxRequest {

	public $path = null;
	public $query = null;
	public $isHome = null;

	public function __construct($subdirectory=NULL)
	{
		if ($subdirectory && $subdirectory != '')
		{
			$this->subdirectory = $subdirectory;
		}

		$request = $_SERVER['REQUEST_URI'];
		$parts = parse_url($request);

		foreach ($parts as $var => $val)
		{
			if (property_exists($this, $var))
			{
				if ($var == 'query' && strlen($val) > 0)
				{
					// Make an array of query vars
					parse_str($val, $array);
					$this->$var = $array;
				}

				if ($var == 'path')
				{
					$this->parsePath($val);
				}
			}
		}

		if ($this->path == '/' || $this->path == '/index.php')
		{
			$this->isHome = true;
		}
	}

	/**
	 * Check the path for Grawlix's vars
	 *
	 * @param		string
	 * @vars			object
	 */
	protected function parsePath($path)
	{
		if ($this->subdirectory)
		{
			$path = str_replace($this->subdirectory, '', $path);
		}
		$parts = explode('/', $path);
		array_shift($parts);
		$slug = '/'.$parts[0];
		$next = $parts[1];

		$book_paths = array(
			ARCHIVE,
			'json',
			'rss'
		);

		// Check for subpaths
		if (in_array($next, $book_paths))
		{
			$slug .= '/'.$next;
			$next = $parts[2];
		}

		// Parse the vars
		if (strlen($next) > 0)
		{
			// For comics and archives, any number here is a sort order
			$i = (integer) $next;
			if ($i > 0)
			{
				$this->query['sort_order'] = $i;
			}
		}

		$this->path = $slug;
	}

}

