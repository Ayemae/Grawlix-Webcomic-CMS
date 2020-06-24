<?php

/* For links that look like buttons, icon links, etc.
 *
 * $link-> url('ad.promo-create.php');
 * $link-> tap('Create an ad');
 * echo $link-> button_primary('new');
 */

class GrlxLinkStyle extends GrlxLink {

	private $modal_id = 'panel_modal'; // must be same as id in modal class
	private $type;
	private $level;
	private $keyword;
	private $i_only = false; // for clickable <i> elements, no <a> used

	function i_only($boolean=true) {
		$this->i_only = $boolean;
	}

	function reveal($boolean=false) {
		$this->data = '';
		if ( $boolean == true ) {
			$this->data = ' data-reveal-id="'.$this->modal_id.'" data-reveal-ajax="true"';
		}
	}

	function action($string='') {
		$this->keyword = $string;
	}

	// For modals that are not ajax from a separate script
	function modal_id($string='') {
		$this->modal_id = $string;
		$this->data = ' data-reveal-id="'.$this->modal_id.'"';
	}

	function styled_link($keyword='') {
		$this->type ? $type = $this->type : $type = 'btn';
		$this->level ? $level = $this->level : $level = '';
		$this->keyword ? $keyword = $this->keyword : $keyword = '';
		$this->anchor_class($type.' '.$level.' '.$keyword);
		$this->icon('true');
		$output = $this->paint();
		return $output;
	}

	function button_primary($keyword='') {
		$this->type = 'btn';
		$this->level = 'primary';
		$this->keyword = $keyword;
		$output = $this->styled_link();
		return $output;
	}

	function button_secondary($keyword='') {
		$this->type = 'btn';
		$this->level = 'secondary';
		$this->keyword = $keyword;
		$output = $this->styled_link();
		return $output;
	}

	function button_tertiary($keyword='') {
		$this->type = 'btn';
		$this->level = 'tertiary';
		$this->keyword = $keyword;
		$output = $this->styled_link();
		return $output;
	}

	function text_link($keyword='') {
		$this->type = 'lnk';
		$this->level = '';
		$this->keyword = $keyword;
		$output = $this->styled_link();
		return $output;
	}

	function icon_link($keyword='') {
		if ( $keyword == '' && $this->keyword ) {
			$keyword = $this->keyword;
		}
		if ( $this->i_only ) {
			$output = $this->icon_no_anchor($keyword);
		}
		else {
			$this->anchor_class($keyword);
			$this->icon('true');
			$output = $this->paint();
		}
		return $output;
	}

	function tour_link() {
		$output = '<a class="icn tour" title="Take a quick tour" href="?tour=1"><i></i></a>';
		return $output;
	}

	function icon_no_anchor($keyword='') {
		$keyword ? $keyword = ' class="'.$keyword.'"' : null;
		$this->title ? $title = ' title="'.$this->title.'"' : null;
		$this->id ? $id = ' id="'.$this->id.'"' : null;
		$output = '<i'.$keyword.$id.$title.'></i>';
		return $output;
	}

	function external_link() {
		$this->anchor_class('extlink');
		$this->tap .= '<i></i>';
		$output = $this->paint();
		return $output;
	}

	function arrow_link() {
		$this->type = 'lnk';
		$this->level = '';
		$this->keyword = 'pages';
		$this->transpose = true;
		$output = $this->styled_link();
		return $output;
	}
}