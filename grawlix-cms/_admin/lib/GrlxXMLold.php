<?php

class GrlxXMLold {


/* Description: Ensure this instance of a class has a XML object.
   Input: String of a /slash/delimited/path.
   Output: $this gets an entire XML object.
   Comment: The class often references this Simple XML object as a fallback.
*/
	public function source($filepath='') {
		$this-> filepath = $filepath;
		$xml_contents = $this-> read_file($this-> filepath);
		$this-> xml_object = $this-> read_xml($xml_contents);
	}

	private function read_file($filepath='') {
		$filepath ? $filepath : $filepath = $this-> filepath;
		if ( is_file ( $filepath) ) {
			$file_contents = file_get_contents($filepath);
		}
		return $file_contents;
	}

	public function read_xml($string,$prefix='') {
		$xml = simplexml_load_string($string);
		return $xml;
	}


/* Description: Ensure this instance of a class has a XML object.
   Input: None.
   Output: $this gets an entire XML object.
   Comment: Surprisingly usefule little func.
*/
	function check_xml_object() {
		if ( !$this-> xml_object ) {
			$filepath = $this-> filepath;

			if ( $filepath ) {
				source($filepath);
			}
		}
	}

/* Description: Returns an XML object.
   Input: A larger object, usually an entire doc.
   Output: An array of objects.
   Purpose: Returns a subset, handy when we don’t know a parent node’s children.
*/
	function get_xml_node_objects($object) {
		if ( $object && is_object($object)) {
			foreach ( $object-> children() as $key => $val ) {
				$result = $this-> get_xml_contents($val);
				$output[$key] = $result;
			}
		}
		return $output;
	}

/* Description:
   Input:
   Output:
   Note:
*/
	function get_xml_node_object($object) {
		if ( is_object($object)) {
			foreach ( $object as $key => $val ) {
				$output[$key] = $key;
			}
			return $output;
		}
	}

/* Description: Returns part of an XML object.
   Input: A larger object, usually an entire doc.
   Output: An array of arrays of the given object’s immediate children.
   Purpose: For zeroing in on a set of data.
*/
	function get_xml_node_values($object) {
		foreach ( $object as $key => $val ) {
			$output[] = $this-> get_xml_contents($val);
		}
		return $output;
	}

	// Returns the value and attributes of a node as a single array.
	function get_xml_contents($object) {
		$attr = $this-> get_attributes($object);
		$vals = $this-> get_children_values($object);
		if ( $attr && !$vals ) {
			$output = $attr;
		}
		if ( !$attr && $vals ) {
			$output = $vals;
		}
		if ( $attr && $vals ) {
			$output = array_merge($attr,$vals);
		}
		return $output;
	}

/* Description: What do we want? CData! When do we want it? Now!
   Input: A XML parent with unique, single-level children, e.g.
     <a>
       <b></b>
       <c></c>
     </a>
   Output: Array of keys/vals based on the child element names.
	 Comment: Every child element had better be unique.
*/
	public function get_children_values($xml_object=object) {
		$this-> check_xml_object();
		$xml_object ? $xml_object : $xml_object = $this-> xml_object;

		if ( is_object($xml_object)) {
			$kids = $xml_object->children();
			if ( $kids ) {
				foreach ( $kids as $key => $val ) {
//					$atts = $val->attributes();
					$output[$key] = (string)$val;
				}
			}
			return $output;
		}
	}

/* Description: Return a node’s attributes as an array.
   Input: SimpleXML object (the whole doc or just a part)
   Output: Array where the key = attribute, val = attribute’s value
   Comment: This assumes there are no redundant attributes because, well, that’s just silly.
*/
	function get_attributes($xml_object=object) {

		if ( is_object($xml_object) ) {
			foreach ( $xml_object->attributes() as $key => $val ) {
				$output[$key] = (string)$val;
			}
			return $output;
		}
	}
}