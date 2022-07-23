<?php

/**
 * Formats tabular data list views.
 */

class GrlxList {

	public $row_id; // Unique number, usually from the database, for draggable rows.
	public $row_class;
	public $row_vis; // Visibility, 0 for off, 1 for on
	public $draggable;
	public $sort_var;
	public $actions;
	public $headings;
	public $content;

	/**
	 * Set defaults
	 */
	public function __construct() {
		$this->draggable = true;
		$this->sort_var = 'sort';
		$this->actions = true;
	}

	public function row_id($str=null) {
		if ( $str ) {
			$this->row_id = $str;
		}
	}

	public function row_class($str=null) {
		if ( $str ) {
			$this->row_class = $str;
		}
	}

	public function row_vis($str=null) {
		if ( $str == '0' || $str == '1' ) {
			$this->row_vis = ' vis_'.$str;
		}
		else {
			$this->row_vis = $str;
		}
	}

	public function draggable($boolean=true) {
		$this->draggable = $boolean;
	}

	public function sort_by($str=null) {
		if ( $str ) {
			$this->sort_var = $str;
		}
	}

	public function actions($boolean=true) {
		$this->actions = $boolean;
	}

	public function headings($array=null) {
		if ( $array ) {
			$this->headings = $array;
		}
	}

	public function content($array=null) {
		if ( $array ) {
			$this->content = $array;
		}
	}

	public function format_headings() {
		if ( $this->headings ) {
			$i = 1;
			$this->row_class ? $class = ' '.$this->row_class : null;
			$output = '<div class="heading'.$class.'">';
			foreach ( $this->headings as $val ) {
				if ( isset($val['value']) ) {
					$val['class'] ? $css = ' class="'.$val['class'].'"' : null;
					$output .= '<div'.$css.'><h6 id="th-'.$i.'">'.$val['value'].'</h6></div>';
				}
				else {
					$output .= '<div><h6>'.$val.'</h6></div>';
				}
				$i++;
			}
			$output .= '</div>';
		}
		return $output;
	}

	protected function parse_key($str=null) {
		$parts = explode('||', $str);
		$this->row_id($parts[0]);  // id
		$this->row_vis($parts[1]); // on/off
	}

	public function format_content() {
		if ( $this->content ) {
			foreach ( $this->content as $key => $array ) {
				$this->parse_key($key);
				$output .= $this->format_row($array);
			}
		}
		if ( $this->draggable ) {
			$output = '<ul id="sortable">'.$output.'</ul>';
		}
		return $output;
	}

	protected function format_row($array) {
		reset($array);
		$first = key($array);
		end($array);
		$last = key($array);
		$this->row_class ? $class = ' '.$this->row_class : null;
		$this->row_vis ? $vis = $this->row_vis : null;
		$output = '<div class="item'.$class.$vis.'">';
		foreach ( $array as $key => $val ) {
			if ( $this->draggable && ($key == $first) ) {
				$output .= '<div title="Drag to sort."><i class="sort"></i><span id="sort-'.$this->row_id.'">'.$val.'</span></div>';
			}
			elseif ( $this->actions && ($key == $last) ) {
				$output .= '<div class="actions">'.$val.'</div>';
			}
			else {
				$output .= '<div>'.$val.'</div>';
			}
		}
		$output .= '</div>';
		if ( $this->draggable ) {
			$output = '<li id="'.$this->sort_var.'-'.$this->row_id.'">'.$output.'</li>';
		}
		return $output;
	}
}