<?php

class GrlxImage {
	public $db = null;
	public $src = '';
	public $alt = '';
	public $class = '';
	public $style = null;
	public $target = null;

	function paint() {
		$this-> check_src();
		$src = $this->src ? ' src="'.$this->src.'"' : null;
		$alt = $this->alt ? ' alt="'.$this->alt.'"' : null;
		$class = $this->class ? ' class="'.$this->class.'"' : null;
		$style = $this->style ? ' style="'.$this->style.'"' : null;
		$target = $this->target ? ' target="'.$this->target.'"' : null;

		$output = '<img'.$src.$alt.$class.$style.'/>';

		return $output;
	}

	function check_src($src='') {
		$src ? $src : $src = $this-> src;
		if(!empty($src)) {
			if (substr($src,0,1) == '/' ) {
				$src = '..'.$src;
			}
			$file_headers = @get_headers($src);
		}
		if(!isset($file_headers) || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
			$this-> src = 'img/image_not_found.100x.png';
		}
		else {
			$this-> src = $src;
		}
	}

	// Check image_reference table to see if record exists and return the ID if yes
	function check_image_ref($query='', $col='url') {
		$db = $this-> db_new;
		$ref_id = $db
			-> where($col, $query)
			-> getOne('image_reference', array('id'))
		;
		$ref_id = $ref_id['id'];
		return $ref_id;
	}
}
/*

$i = new GrlxImage;
$i->src = 'aaa';
$i->alt = 'bbb';
$i->class = 'ccc';
$i->style = 'ddd';
$i->url = 'eee';
$image = $i->paint();

*/