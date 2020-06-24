<?php

class GrlxXML_Static extends GrlxXML {

	/**
	 * Set defaults, etc.
	 */
	public function __construct() {
		$this->getArgs(func_get_args());
		parent::__construct();
	}

	function getClonesValues($xpath=null,$name=null){
		$this->setXPath($xpath);
		if ( is_object($xpath) ) {
			foreach ( $xpath->{$name} as $val ) {
				$list[] = (string)$val;
			}
		}
		return $list;
	}
	function interpretItem($obj=null){
		if ( $obj ) {
			foreach ($obj as $key => $val ) {
				$output[$key] = (string)$val;
			}
		}
		return $output;
	}
}


function build_quick_label($number,$value,$for){
	$output = '<label for="item['.$number.']['.$for.']">'.$value.'</label>'."\n";
	return $output;
}
function build_heading_field($number,$value=null){
	$output .= '<input type="text" id="item['.$number.'][heading]" name="item['.$number.'][heading]" value="'.$value.'" style="max-width:12rem"/>';
	return $output;
}

function build_text_field($number,$value=null){
	$output .= '<textarea id="item['.$number.'][text]" name="item['.$number.'][text]" style="height:15rem">'.$value.'</textarea>';
	return $output;
}

function build_link_field($number,$value=null){
	if ( trim($value) != '' && substr($value,0,4) != 'http' ) {
		$value = 'http://'.$value;
	}
	$output .= '<input type="text" id="item['.$number.'][link]" name="item['.$number.'][link]" value="'.$value.'" style="max-width:24rem"/>';
	return $output;
}

function build_free_field($number,$value=null){
	$output .= '<textarea id="item['.$number.'][free]" name="item['.$number.'][free]" rows="30" cols="80">'.$value.'</textarea>';
	return $output;
}

function build_image_field($number,$value=null){
	if ( is_file('..'.(string)$value)) {
		$output .= '<img src="..'.(string)$value.'" alt="img['.(string)$value.']"/>';
	}
	elseif ( $value && $value != '' ) {
		$output .= '<img src="img/image_not_found.100x.png" alt="image not found"/>';
	}
	else {
	}
	// This hidden image is a default. If the user doesn’t upload a new pic, this script “updates” the XML record with the prior value.
	$output .= '<input type="hidden" name="item['.$number.'][original_image]" value="'.$value.'"/>';
	$output .= '<input type="file" name="item['.$number.'][image]"/>';
	return $output;
}

