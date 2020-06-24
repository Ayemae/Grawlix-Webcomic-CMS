<?php

/**
 * Builds panel form elements.
 *
 * Instantiate:
 * $form = new GrlxForm;
 */

class GrlxForm {

	private   $email_pattern;
	private   $email_error;
	private   $path_pattern;
	private   $path_error;
	private   $title_pattern;
	private   $title_error;
	private   $url_pattern;
	private   $url_error;
	private   $required_error;
	protected $error_check;
	protected $multipart;
	protected $save_name;
	protected $save_value;
	protected $custom_buttons;
	protected $send_to;
	protected $form_id;
	protected $contents;
	public    $row_class;
	protected $type;
	protected $id;
	protected $data;
	protected $label;
	protected $title;
	protected $name;
	protected $value;
	protected $error;
	protected $required;
	protected $pattern;
	protected $autofocus;
	protected $disabled;
	protected $maxlength;
	protected $placeholder;
	protected $readonly;
	protected $prefix;
	protected $postfix;
	protected $div_wrap;
	protected $show_error;

	/**
	 * Set defaults
	 */
	public function __construct() {
		$this->error_check_strings();
		$this->error_check();
		$this->multipart(false);
		$this->save_name('submit');
		$this->save_value('save');
		$this->row_class = 'row form';
		$this->div_wrap = true;
		$this->show_error = true;
	}

	protected function set_security()
	{
		$this->input_hidden('grlx_xss_token');
		$this->value = $_SESSION['admin'];
		return $this->paint();
	}

	/**
	 * The following functions are for various form-wide settings
	 */
	protected function error_check_strings() {
		$this->email_pattern = 'email';
		$this->email_error = 'Not a valid email.';
		$this->path_pattern = "^/[\w\-]*";
		$this->path_error = 'Forward slash (/) followed by letters, numbers, hyphens (-), and underscores (_) only.';
		$this->title_pattern = "[\w:,%!'‘’“”@#–&— \-\?\.\$\*]+";
		$this->title_error = 'Letters, numbers, spaces, and basic punctuation only.';
		$this->url_pattern = 'url';
		$this->url_error = 'Not a valid URL.';
		$this->required_error = 'Don’t leave this blank.';
	}

	public function error_check($boolean=true) {
		$this->error_check = $boolean;
	}

	public function multipart($boolean=true) {
		$this->multipart = $boolean;
	}

	public function save_name($str=null) {
		$this->save_name = $str;
	}

	public function save_value($str=null) {
		$this->save_value = $str;
	}

	public function custom_buttons($str=null) {
		$this->custom_buttons = $str;
	}

	public function form_id($str=null) {
		$this->form_id = $str;
	}

	public function send_to($str=null) {
		$this->send_to = $str;
	}

	public function contents($str=null) {
		$this->contents = $str;
	}

	/**
	 * Assemble the form
	 */
	public function build_form() {
		if ( $this->custom_buttons ) {
			$buttons = $this->custom_buttons;
		}
		else {
			$buttons = $this->form_buttons();
		}
		$output  = $this->open_form();
		$output .= $this->contents;
		$output .= $buttons;
		$output .= $this->close_form();
		return $output;
	}

	public function open_form() {
		$this->error_check ? $abide = ' data-abide' : null;
		$this->multipart ? $enctype = ' enctype="multipart/form-data"' : null;
		$this->form_id ? $id = ' id="'.$this->form_id.'"' : null;
		$str  = '<form accept-charset="UTF-8" action="'.$this->send_to.'" method="post"'.$id.$abide.$enctype.'>';
		$str .= $this->set_security();
		return $str;
	}

	public function form_buttons($buttons=null) {
		$str  = '<div class="btn-row"><div>';
		if ( $buttons ) {
			$str .= $buttons;
		}
		$str .= '<button class="btn primary save" name="'.$this->save_name.'" type="submit" value="'.$this->save_value.'"><i></i>Save</button>';
		$str .= '</div></div>';
		return $str;
	}

	public function inline_save_button() {
		$str = '<button class="btn primary save" name="'.$this->save_name.'" type="submit" value="'.$this->save_value.'"><i></i>Save</button>';
		return $str;
	}

	public function close_form() {
		$str = '</form>';
		return $str;
	}

// ! Inputs
//
//

	public function row_class($str=null) {
		$this->row_class .= ' '.$str;
	}

	public function no_div_wrap($boolean=true) {
		$this->div_wrap = false;
	}

	public function hide_error($boolean=true) {
		$this->show_error = false;
	}

	public function reset_vars() {
		$this->id();
		$this->label();
		$this->name();
		$this->value();
		$this->data();
		$this->size();
		$this->required();
		$this->pattern();
		$this->autofocus();
		$this->disabled();
		$this->maxlength();
		$this->placeholder();
		$this->readonly();
		$this->prefix();
		$this->postfix();
	}

	public function id($str=null) {
		$this->id = $str;
	}

	public function label($str=null) {
		$this->label = $str;
	}

	public function name($str=null) {
		$this->name = $str;
	}

	public function title($str=null) {
		$this->title = $str;
	}

	public function value($str=null) {
		$this->value = $str;
	}

	public function data($str=null) {
		$this->data = $str;
	}

	public function size($str=null) {
		$this->size = $str;
	}

	public function required($boolean=false) {
		$this->required = $boolean;
		if ( $boolean ) {
			$this->error = $this->required_error;
		}
		else {
			$this->error = null;
		}
	}

	protected function pattern($str=null) {
		$this->pattern = null;
		$this->error = null;
		switch ($str) {
			case 'email':
				$this->pattern = $this->email_pattern;
				$this->error = $this->email_error;
				break;
			case 'path':
				$this->pattern = $this->path_pattern;
				$this->error = $this->path_error;
				break;
			case 'title':
				$this->pattern = $this->title_pattern;
				$this->error = $this->title_error;
				break;
			case 'url':
				$this->pattern = $this->url_pattern;
				$this->error = $this->url_error;
				break;
		}
	}

	public function autofocus($boolean=false) {
		$this->autofocus = $boolean;
	}

	public function disabled($boolean=false) {
		$this->disabled = $boolean;
	}

	public function maxlength($integer=null) {
		$this->maxlength = $integer;
	}

	public function placeholder($str=null) {
		$this->placeholder = $str;
	}

	public function readonly($boolean=false) {
		$this->readonly = $boolean;
	}

	public function prefix($str=null) {
		$this->prefix = $str;
	}

	public function postfix($str=null) {
		$this->postfix = $str;
	}

	// $str will be used as the id
	public function input_text($str=null) {
		$this->reset_vars();
		$this->type = 'text';
		$this->id = $str;
	}

	public function input_number($str=null) {
		$this->reset_vars();
		$this->type = 'number';
		$this->id = $str;
	}

	public function input_hidden($str=null) {
		$this->reset_vars();
		$this->type = 'hidden';
		$this->id = $str;
	}

	public function input_path($str=null) {
		$this->reset_vars();
		$this->type = 'text';
		$this->id = $str;
		$this->label('URL');
		$this->required(true);
		$this->pattern('path');
		$this->maxlength(128);
	}

	public function input_title($str=null) {
		$this->reset_vars();
		$this->type = 'text';
		$this->id = $str;
		$this->label('Title');
		$this->required(true);
		$this->pattern('title');
		$this->maxlength(64);
	}

	public function input_clickable($str=null) {
		$this->reset_vars();
		$this->type = 'text';
		$this->id = $str;
		$this->label('Clickable text');
		$this->required(true);
		$this->pattern('title');
		$this->maxlength(64);
	}

	public function input_email($str=null) {
		$this->reset_vars();
		$this->type = 'text';
		$this->id = $str;
		$this->label('Email');
		$this->required(true);
		$this->pattern('email');
		$this->maxlength(64);
	}

	public function input_description($str=null) {
		$this->reset_vars();
		$this->type = 'text';
		$this->id = $str;
		$this->label('Description');
		$this->maxlength(160);
	}

	public function input_url($str=null) {
		$this->reset_vars();
		$this->type = 'text';
		$this->id = $str;
		$this->label('URL');
		$this->required(true);
		$this->pattern('url');
		$this->maxlength(128);
	}

	public function input_file($str=null) {
		$this->reset_vars();
		$this->type = 'file';
		$this->id = $str;
	}

	public function input_password($str=null) {
		$this->reset_vars();
		$this->label = 'Password';
		$this->type = 'password';
		$this->id = $str;
	}

	public function new_password($str=null) {
		$this->reset_vars();
		$this->type = 'password';
		$this->id = $str;
		$this->label = 'New password';
		$this->size = '16';
		$output = $this->paint();
		$this->id = 'confirm_'.$str;
		$this->label = 'Confirm';
		$this->data = ' data-equalto="'.$str.'"';
		$this->error = 'Passwords must match.';
		$this->size = '16';
		$output .= $this->paint();
		return $output;
	}

	protected function add_label() {
		if ( $this->readonly ) {
			$note = '<br/><span class="note">(This cannot be changed.)</span>';
		}
		$str = '<div><label for="'.$this->id.'">'.ucfirst($this->label).$note.'</label></div>';
		return $str;
	}

	protected function add_error_message() {
		if ( $this->show_error ) {
			$str = '<small class="error">'.$this->error.'</small>';
		}
		return $str;
	}

	public function input_wrap($str=null) {
		if ( ($this->div_wrap) && ($this->type != 'hidden') ) {
			$output = '<div>'.$str.'</div>';
		}
		else {
			$output = $str;
		}
		return $output;
	}

	public function row_wrap($str=null) {
		if ( ($this->div_wrap) && ($this->type != 'hidden') ) {
			$output = '<div class="'.$this->row_class.'">'.$str.'</div>';
		}
		else {
			$output = $str;
		}
		return $output;
	}

	protected function wrap_prefixed($str=null) {
		$output  = '<div class="prefixed">';
		$output .= '<span class="prefix">'.$this->prefix.'</span>';
		$output .= $str;
		$output .= '</div>';
		return $output;
	}

	public function paint() {
		$this->id ? $id = ' id="'.$this->id.'"' : null;
		$this->name ? $name = ' name="'.$this->name.'"' : $name = ' name="'.$this->id.'"';
		$this->size ? $size = ' size="'.$this->size.'" style="width:'.$this->size.'rem"' : null;
		$this->value ? $value = ' value="'.$this->value.'"' : null;
		$this->data ? $data = $this->data : null;
		$this->required ? $required = ' required' : null;
		$this->pattern ? $pattern = ' pattern="'.$this->pattern.'"' : null;
		$this->autofocus ? $autofocus = ' autofocus' : null;
		$this->disabled ? $disabled = ' disabled' : null;
		$this->maxlength ? $maxlength = ' maxlength="'.$this->maxlength.'"' : null;
		$this->placeholder ? $placeholder = ' placeholder="'.$this->placeholder.'"' : null;
		$this->readonly ? $readonly = ' readonly' : null;
		$output = '<input type="'.$this->type.'" '.$id.$name.$value.$maxlength.$autofocus.$required.$pattern.$placeholder.$readonly.$disabled.$data.$size.' />';
		if ( $this->error ) {
			$error = $this->add_error_message();
			$output = $output.$error;
		}
		if ( $this->prefix ) {
			$output = $this->wrap_prefixed($output);
		}
		else {
			$output = $this->input_wrap($output);
		}
		if ( $this->label ) {
			$label = $this->add_label();
			$output = $label.$output;
		}
		$output = $this->row_wrap($output);
		return $output;
	}

	public function checkbox_switch($int=1) {
		$id = $this->id;
		if ( $this->title ) {
			$title = ' title="'.$this->title.'"';
		}
		if ( $int == 1 ) {
			$checked = ' checked';
		}
		$output  = '<div class="toggle"'.$title.'>';
		$output .= '<input id="'.$id.'" type="checkbox"'.$checked.'>';
		$output .= '<label for="'.$id.'"></label>';
		$output .= '</div>';
		return $output;
	}

	public function build_tone_select($current=null) {
		global $db_ops;
		$list = $db_ops->get_theme_and_tone_list('theme_name');
		$select_output  = '<div><label>Select a tone</label></div><div><select name="tone-select">';
		$select_output .= '<option value="0"> </option>';
		foreach ( $list as $theme => $array ) {
			$select_output .= '<optgroup label="'.$theme.' theme">';
			foreach ( $array as $key => $val ) {
				unset($selected);
				if ( $key == $current ) {
					$selected = ' selected="selected"';
				}
				$select_output .= '<option'.$selected.' value="'.$key.'">'.$val.'</option>';
			}
			$select_output .= '</optgroup>';
		}
		$select_output .= '</select></div>';
		return $select_output;
	}
}