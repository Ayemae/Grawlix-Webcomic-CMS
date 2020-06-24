<?php

/**
 * Read/write XML from strings and files.
 *
 * Instantiate:
 * $xml = new GrlxXML;
 */

class GrlxXML {

	protected $filepath;
	protected $stringXML;
	protected $simpleXML;
	protected $root;
	public    $version;
	protected $xpath;

	/**
	 * Setup
	 */
	public function __construct() {
		$this->getArgs(func_get_args());
		if ( $this->filepath ) {
			$this->loadFile();
			$this->getInfo();
		}
		if ( $this->stringXML ) {
			$this->setString($this->stringXML);
			$this->getInfo();
		}
		if ( !is_object($this->simpleXML) ) {
			die('<h1>Could not load XML.</h1>');
		}
	}

	/**
	 * Pass in any arguments
	 *
	 * @param array $list - arguments from main script
	 */
	protected function getArgs($list=null) {
		$list = $list[0];
		if ( isset($list) ) {
			foreach ( $list as $key=>$val ) {
				if ( property_exists($this,$key) ) {
					$this->{$key} = $val;
				}
			}
		}
	}

	/**
	 * Pass in a string
	 *
	 * @param string $str - XML as a string
	 */
	public function setString($str=null) {
		if ( $str ) {
			$this->simpleXML = simplexml_load_string($str);
		}
	}

	/**
	 * Pass in a filepath for an XML file
	 *
	 * @param string $str - filepath
	 */
	public function setFile($str=null) {
		if ( $str ) {
			$this->filepath = $str;
		}
	}

	/**
	 * Format the xpath for use here
	 *
	 * @param string $str - xpath part
	 */
	public function setXPath(&$str) {
		$str = '/'.trim($str,'/');
		$str = $this->root.$str;
		$str = $this->simpleXML->xpath($str);
		$str = $str[0];
	}

	/**
	 * Load the specified XML file
	 */
	protected function loadFile() {
		if ( file_exists($this->filepath) ) {
			$this->simpleXML = simplexml_load_file($this->filepath);
		}
	}

	/**
	 * Get the root element and Grawlix version number of the XML.
	 */
	protected function getInfo() {
		$this->root = '/'.$this->simpleXML->getName();
		$this->version = (string)$this->simpleXML->attributes()->version;
	}

	/**
	 * Get item sets (example: static page content)
	 * Starting v1.1 to ???
	 *
	 * @param string $xpath - to container of items
	 * @return array $list
	 */
	public function getItemSets($xpath=null) {
		$this->setXPath($xpath);
		if ( is_object($xpath) ) {
			$i = 0;
			foreach ( $xpath->children() as $item=>$children ) {
				$i++;
				foreach ( $children as $key=>$val ) {
					$list[$item.'-'.$i][$key] = (string)$val;
				}
			}
		}
		return $list;
	}

	/**
	 * Get the value for a given element
	 *
	 * @param string $xpath - path
	 * @return string $val - value
	 */
	public function getValue($xpath=null) {
		$this->setXPath($xpath);
		if ( is_object($xpath) ) {
			$val = (string)$xpath;
		}
		return $val;
	}

	/**
	 * Get children’s values for a given node
	 *
	 * @param string $xpath - node path
	 * @return array $list - name=>value list
	 */
	public function getChildren($xpath=null) {
		$this->setXPath($xpath);
		if ( is_object($xpath) ) {
			foreach ( $xpath->children() as $key=>$val ) {
				$list[$key] = (string)$val;
			}
		}
		return $list;
	}

	public function getChildNodes($xpath=null) {
		$this->setXPath($xpath);
		if ( is_object($xpath) ) {
			$i = 0;
			foreach ( $xpath->children() as $item=>$children ) {
				$i++;
				foreach ( $children as $key=>$val ) {
					$list[$item.'-'.$i][$key] = $val;
				}
			}
		}
		return $list;
	}

	/**
	 * Get values of same-named children of node
	 *
	 * @param string $xpath - node path
	 * @param string $name - name of the element
	 * @return array $list - value list
	 */
	public function getClones($xpath=null,$name=null) {
		$this->setXPath($xpath);
		if ( is_object($xpath) ) {
			foreach ( $xpath->{$name} as $val ) {
				$list[] = (string)$val;
			}
		}
		return $list;
	}

	/**
	 * Get same-named children’s children, which are very similar to their cousins.
	 *
	 * @param string $xpath - node path
	 * @return array $list
	 */
	public function getClonesChildren($xpath=null) {
		$this->setXPath($xpath);
		if ( is_object($xpath) ) {
			$name = $xpath->children()->getName();
			foreach ( $xpath->{$name} as $val ) {
				$list[] = (array)$val;
			}
		}
		return $list;
	}

	/**
	 * Get attributes for a given node
	 *
	 * @param string $xpath - node path
	 * @return array $list - name=>value list
	 */
	public function getAttributes($xpath) {
		if ( is_object($xpath) ) {
			foreach ( $xpath->attributes() as $key=>$val ) {
				$list[$key] = (string)$val;
			}
		}
		return $list;
	}

	/**
	 * Returns the values and attributes of a node and its children as a single array
	 *
	 * @param string $xpath - node path
	 * @return array $list - name=>value list
	 */
	public function getAllNodes($xpath=null) {
 		if ( !$xpath ) {
			$xpath = $this->xpath;
		}
		if ( is_object($xpath) ) {
			$json = json_encode($xpath);
			$list = json_decode($json,true);
		}
		return $list;
	}

}