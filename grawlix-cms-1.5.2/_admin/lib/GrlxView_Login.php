<?php

/**
 * Assembles the bits to build views for the admin login/logout pages.
 */

class GrlxView_Login extends GrlxView {

	protected $id;
	protected $alert;

	/**
	 * Set defaults
	 */
	public function __construct() {
		parent::__construct();
		unset($this->css_file);
		unset($this->js_file);
		$this->css_file[] = 'login.css';
		$this->js_file[] = 'login.min.js';
	}

	/**
	 * Pass in css info
	 *
	 * @param string $str - css id for <main> element
	 */
	public function main_id($str=null) {
		$this->id = $str;
	}

	/**
	 * Show an error/info message
	 *
	 * @param string $str - text for message
	 */
	public function alert_msg($str=null) {
		$this->alert = $str;
	}

	/**
	 * Call this function at the start of view output
	 *
	 * @return string - html for the head of the page, opens content
	 */
	public function open_view() {
		$output  = $this->html_head();
		$output .= $this->open_body();
		$output .= '<div id="wrap">';
		$output .= '<main id="'.$this->id.'" class="dialog">';
		$output .= $this->view_header();
		if ( $this->alert ) {
			$output .= '<div class="message exclaim">';
			$output .= '<i></i>'.$this->alert;
			$output .= '</div>';
		}
		return $output;
	}

	/**
	 * Call this function at the end of view output, closes content
	 *
	 * @return string - html for the foot of the page
	 */
	public function close_view() {
		$output .= '</main>';
		$output .= '</div>';
		$output .= $this->html_foot();
		return $output;
	}

	/**
	 * Call this function to build the h1 headline
	 *
	 * @return string - html for <header> block
	 */
	public function view_header() {
		$output  = '<header><img src="img/logo_small.svg"><h1>';
		$output .= $this->headline;
		$output .= '</h1></header>';
		return $output;
	}
}