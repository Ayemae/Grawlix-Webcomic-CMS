<?php

/**
 * XML/CSS operations for themes and tones.
 *
 * Instantiate:
 * $theme = new GrlxXML_Theme;
 */

class GrlxXML_Theme extends GrlxXML {

	protected $fileOps;
	protected $dirList;
	protected $themesList;



	protected $themes_dir;
	protected $themes_dir_list;
	protected $install_basename;
	protected $default_tone_title;
	public    $uninstalled_list;
	protected $theme_id;
	protected $tone_id;
	protected $info_list;
	public    $options_list;
	public    $slot_list;

	public $tone_options = array();
	public $tone_css_list = array();

	/**
	 * Setup
	 */
	public function __construct() {

	}




	/**
	 * Override the parent class’s function to check for css priority declarations.
	 *
	 * @return string $output -
	 */
	public function get_children_values($xml_object=object) {
		$this->check_xml_object();
		$xml_object ? $xml_object : $xml_object = $this->xml_object;
		if ( is_object($xml_object) ) {
			$kids = $xml_object->children();
			if ( $kids ) {
				foreach ( $kids as $key => $val ) {
					$atts = $val->attributes();
					if ( $atts['priority'] ) {
						$output[$key.' priority="'.(string)$atts['priority'].'"'] = (string)$val;
					}
					else {
						$output[$key] = (string)$val;
					}
				}
			}
			return $output;
		}
	}

	function get_tone_path($tone_id) {
		$path = DIR_STYLES;
		if ( is_writable( $path ) ) {
			$full_path = $path.'/'.$tone_id.'.tone.css';
		}
		return $full_path;
	}

	function fileops_save($file_data, $path){
		$fileops = new GrlxFileOps;
		$fileops->set_file($path);
		$fileops->set_contents($file_data);
		$boolean = $fileops->save_file();
		return $boolean;
	}

	function duplicate_tone_css($duped_id, $new_id) {
		$dupe_path = $this->get_tone_path($duped_id);
		$new_path = $this->get_tone_path($new_id);
		$note = '/* copy of */';
		$file_data = $note.file_get_contents($dupe_path);
		$saved = $this->fileops_save($file_data, $new_path);
		return $saved;
	}

	function delete_tone_css($tone_id) {
		$path = $this->get_tone_path($tone_id);
		$deleted = unlink($path);
		return $deleted;
	}

	function compile_tone_css($list) {
		if ( $list ) {
			$now = date('d-M-Y').'||'.date('H:i:s');
			$css = '/*tone_ID='.$this->tone_id.'||'.$now.'*/';
			foreach ( $list as $value => $array ) {
				foreach ( $array as $key => $items ) {
					foreach ( $items as $property => $selector ) {
						$property = explode(' ', $property);
						$important = $this->check_priority($property[1]);
						$important ? $extra = ' !important' : $extra = null;
						$css .= $selector.'{'.$property[0].':'.$value.$extra.'}';
					}
				}
			}
		}
		$full_path = $this->get_tone_path($this->tone_id);
		$saved = $this->fileops_save($css, $full_path);
		return $saved;
	}

	function check_priority($str) {
		$boolean = false;
		if ( $str == 'priority="important"' ) {
			$boolean = true;
		}
		return $boolean;
	}

	function build_tone_css_list() {
		$array = $this->tone_css_list;
		foreach ( $array as $label => $list ) {
			$is_css = $this->not_stored_in_tone_css($list);
			if ( $is_css ) {
				$value = null;
				foreach ( $list as $key => $val ) {
					$list = $this->unset_item_meta($list);
					if ( !$value ) {
						$value = $this->find_value_item($list);
					}
					if ( $value ) {
						unset($list['value']);
					}
				}
				$css_list[$value][] = $list;
			}
		}
		return $css_list;
	}

	function find_value_item($array) {
		if ( $array ) {
			$value = $array['value'];
		}
		return $value;
	}

	function unset_item_meta($array) {
		if ( $array ) {
			unset($array['type']);
			unset($array['title']);
		}
		return $array;
	}

	function not_stored_in_tone_css($array) {
		if ( $array ) {
			$boolean = true;
			if ( $array['type'] == 'slot' ) {
				$boolean = false;
			}
		}
		return $boolean;
	}

	protected function build_value_xml($array) {
		$xml_str = '<value>'."\n";
		foreach ( $array as $label => $value ) {
			$label_set = explode(' ',$label); // In case $label has an attribute, e.g. ‘color priority="important"’, just use the first bit to close out the element.
			$xml_str .= "\t".'<'.$label.'>'.$value.'</'.$label_set[0].'>'."\n";
		}
		$xml_str .= '</value>'."\n";
		return $xml_str;
	}

	protected function build_value_map_xml($array) {
		$xml_str = '<value_map>'."\n";
		foreach ( $array as $label => $array2 ) {
			foreach ( $array2 as $set_key => $array3 ) {
				foreach ( $array3 as $tag => $value ) {
					if ( $set_key == 'attributes' ) {
						$attr .= ' '.$tag.'="'.$value.'"';
					}
					if ( $set_key == 'values' ) {
						$tag_set = explode(' ',$tag); // In case $tag has an attribute, e.g. ‘color priority="important"’, just use the first bit to close out the element.
						$children .= "\t\t".'<'.$tag.'>'.$value.'</'.$tag_set[0].'>'."\n";
					}
				}
			}
			$xml_str .= "\t".'<'.$label.$attr.'>'."\n".$children."\t".'</'.$label.'>'."\n";
			unset($attr);
			unset($children);
		}
		$xml_str .= '</value_map>'."\n";
		return $xml_str;
	}

	protected function build_value_array() {
		$list = $this->tone_options;
		foreach ( $list as $label => $array ) {
			foreach ( $array as $set_key => $array2 ) {
				foreach ( $array2 as $key => $value ) {
					if ( $key == 'value' ) {
						$new_list[$label] = $value;
					}
				}
			}
		}
		return $new_list;
	}

	protected function build_value_map_array() {
		$list = $this->tone_options;
		foreach ( $list as $label => $array ) {
			foreach ( $array as $set_key => $array2 ) {
				foreach ( $array2 as $key => $value ) {
					if ( $key == 'value' ) {
						unset($list[$label][$set_key][$key]);
					}
				}
			}
		}
		return $list;
	}

	protected function sort_attr_and_val($list) {
		foreach ( $list as $key => $val ) {
			if ( ($key == 'type') || ($key == 'title') ) {
				$type = 'attributes';
			}
			else {
				$type = 'values';
			}
			$sorted_list[$type][$key] = $val;
		}
		return $sorted_list;
	}

	/**
	 * Makes an array of the tone values from the xml
	 *
	 * @param string $str
	 * @return array $list
	 */
	protected function values_to_array($str=null) {
		$list = $this->read_xml($str);
		$list = $this->get_xml_contents($list);
		return $list;
	}

	/**
	 * Makes an array of the tone values map from the xml
	 *
	 * @param string $str
	 * @return array $list
	 */
	protected function map_to_array($str=null) {
		$obj = $this->read_xml($str);
		$list = $this->get_xml_node_objects($obj);
		return $list;
	}

	function list_for_editing($value_array, $map_array) {
		if ( $value_array && $map_array ) {
			foreach ( $map_array as $label => $array ) {
				$type = $array['type'];
				$list[$type][$label] = $array;
				$value = $value_array[$label];
				$list[$type][$label]['value'] = $value;
			}
		}
		return $list;
	}

	/**
	 * Build an associative array of the tone options and values for easy creation of css
	 *
	 * @return array $list -
	 */
	protected function list_for_css($value_array, $map_array) {
		if ( $value_array && $map_array ) {
			$list = $map_array;
			foreach ( $map_array as $label => $array ) {
				$value = $value_array[$label];
				$list[$label]['value'] = $value;
			}
		}
		return $list;
	}

	protected function fetch_tone($xpath='/theme/tone') {
		$this->check_xml_object();
		if ( $this->xml_object && $xpath ) {
			$list = $this->get_xml_node_objects($this->xml_object-> xpath($xpath));
			$list = $list[0];
		}
		if ( $list ) {
			foreach ( $list as $key => $val ) {
				$list[$key] = $this->sort_attr_and_val($val);
			}
		}
		return $list;
	}

	protected function fetch_slots($xpath='/theme/slots/slot') {
		$this->check_xml_object();
		if ( $this->xml_object && $xpath ) {
			$list = $this->get_xml_node_values($this->xml_object-> xpath($xpath));
		}
		return $list;
	}

	protected function fetch_metadata($xpath='/theme/metadata') {
		$this->check_xml_object();
		if ( $this->xml_object && $xpath ) {
			$list = $this->get_xml_node_values($this->xml_object-> xpath($xpath));
		}
		return $list[0];
	}

}