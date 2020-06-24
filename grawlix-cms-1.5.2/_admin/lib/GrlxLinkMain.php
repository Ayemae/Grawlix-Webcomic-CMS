<?php

/*
# Sample usage

You only have to define one new link.

$link = new GrlxLink;


Return a preformatted link, complete with URL and tappable text.

$link-> paint('preset name');


Want a different URL?

$link-> preset('preset name');
$link-> url = 'somewhere.php';
$link-> paint();


Want different tap text?

$link-> preset('preset name');
$link-> tap = 'Go somewhere';
$link-> paint();

Want a series?

$link-> preset('preset name');
$link-> url = 'somewhere1.php';
$link-> tap = 'Somewhere one';
$link-> paint();
$link-> url = 'somewhere2.php';
$link-> tap = 'Somewhere two';
$link-> paint();
$link-> url = 'somewhere3.php';
$link-> tap = 'Somewhere three';
$link-> paint();
*/

class GrlxLinkMain {

	public $css;
	public $data;
	public $icon; // use 'true' for <i></i>. use class name for <i class="name"></i>
	public $id;
	public $query; // 'var=value'
	public $rel;
	public $tap;
	public $target;
	public $title;
	public $url;
	public $transpose; // position of icon and text

	function url($string='') {
		$this->url = $string;
		$this->css = '';
		$this->data = '';
		$this->icon = '';
		$this->id = '';
		$this->query = '';
		$this->tap = '';
		$this->target = '';
		$this->title = '';
		$this->transpose = false;
		if ( (substr($string,0,4) == 'http') || (substr($string,0,2) == '//') ) {
			$this->rel('external '.$this->rel);
		}
		else {
			$this->rel = '';
		}
	}

	function id($string='') {
		$this->id = $string;
	}

	function rel($string='') {
		$this->rel = $string;
	}

	function tap($string='') {
		$this->tap = $string;
	}

	function title($string='') {
		$this->title = $string;
	}

	function anchor_class($string='') {
		$this->css = $string;
	}

	function icon($string='') {
		if ( ($string == '') || ($string == 'false') ) {
			$this->icon = '';
		}
		elseif ( $string == 'true' ) {
			$this->icon = '<i></i>';
		}
		else {
			$this->icon = '<i class="'.$string.'"></i>';
		}
	}

	function query($string='') {
		$this->query = $string;
	}

	function paint($ref='') {

		if ( $ref ) {
			$this->preset($ref);
		}
		if ( $this->query ) {
			$query = '?'.$this->query;
			$url = $this->url.$query;
		}
		else {
			$url = $this->url;
		}
		$url ? $url = ' href="'.$url.'"': null;
		$this->rel ? $rel = ' rel="'.$this->rel.'"' : null;
		$this->title ? $title = ' title="'.$this->title.'"' : null;
		$this->id ? $id = ' id="'.$this->id.'"' : null;
		$this->css ? $css = ' class="'.$this->css.'"' : null;
		$this->target ? $target = ' target="'.$this->target.'"' : null;
		$this->data ? $data = $this->data : null;
		$this->icon ? $icon = $this->icon : null;
		$tap = $this->tap;
		$this->transpose ? $tap_output = $tap.$icon : $tap_output = $icon.$tap;

		$output = '<a'.$css.$id.$url.$title.$target.$rel.$data.'>'.$tap_output.'</a>';
		return $output;
	}
}