<?php

/**
 * Builds a foundation reveal modal.
 */

class GrlxForm_Modal extends GrlxForm {

	private $modal_id = 'panel_modal';
	private $modal_css = 'reveal-modal';
	private $cancel_button = '<a id="cancel" class="close-reveal-modal btn tertiary">Cancel</a>';
	private $modal_save_name = 'modal-submit';
	public  $row_class = 'row modal';
	public  $dialog;
	public  $headline;
	public  $instructions;

	/**
	 * Set defaults
	 */
	public function __construct() {
		parent::__construct();
		$this->form_id = 'modal';
		$this->modal_id = 'panel_modal';
		$this->modal_css = 'reveal-modal';
		$this->cancel_button = '<a id="cancel" class="close-reveal-modal btn tertiary">Cancel</a>';
		$this->modal_save_name = 'modal-submit';
		$this->row_class = 'row modal';
	}

	function modal_id($str='panel_modal') {
		$this->modal_id = $str;
	}

	function modal_css($str='reveal-modal') {
		$this->modal_css = $str;
	}

	function dialog($str='') {
		$this->dialog = $str;
	}

	function headline($str='') {
		if ( $str ) {
			$this->headline = '<header><h2>'.$str.'</h2></header>';
		}
	}

	function instructions($str='') {
		$this->instructions = '<div class="row info"><div>'.$str.'</div></div>';
	}

	function open_container() {
		$str = '<div id="'.$this->modal_id.'" class="'.$this->modal_css.'" data-reveal>';
		return $str;
	}

	function close_container() {
		$str = '</div>';
		return $str;
	}

	public function row_wrap($str=null) {
		$output = '<div class="'.$this->row_class.'">'.$str.'</div>';
		return $output;
	}

	function paint_all() {
		$output = $this->paint_container(true);
		return $output;
	}

	function modal_container() {
		$output  = $this->open_container();
		$output .= $this->close_container();
		return $output;
	}

	function paint_modal() {
		$modal = $this->modal_id;
		$this->save_name( $this->modal_save_name );
		$output = <<<EOL
		<script>
			$(document).foundation();
			$('a#cancel').click(function(){
				$('#$modal').foundation('reveal', 'close');
			});
		</script>
EOL;
		$output .= $this->open_form();
		$output .= $this->headline;
		if ( $this->dialog ) {
			$output .= $this->dialog;
		}
		if ( $this->instructions ) {
			$output .= $this->instructions;
		}
		if ( $this->contents ) {
			$output .= $this->contents;
		}
		$output .= $this->form_buttons( $this->cancel_button );
		$output .= $this->close_form();
		$output .= '<a class="close-reveal-modal"><i></i></a>';
		return $output;
	}

	function paint_confirm_modal() {
		$modal = $this->modal_id;
		$this->save_name( $this->modal_save_name );
		$buttons  = '<div class="btn-row"><div>';
		$buttons .= $this->cancel_button;
		$buttons .= '<button class="btn alert delete" name="'.$this->save_name.'" type="submit" value="delete"><i></i>Delete</button>';
		$buttons .= '</div></div>';
		$output = <<<EOL
		<script>
			$(document).foundation();
			$('a#cancel').click(function(){
				$('#$modal').foundation('reveal', 'close');
			});
		</script>
EOL;
		$output .= $this->open_form();
		$output .= $this->headline;
		if ( $this->dialog ) {
			$output .= $this->dialog;
		}
		if ( $this->instructions ) {
			$output .= $this->instructions;
		}
		if ( $this->contents ) {
			$output .= $this->contents;
		}
		$output .= $buttons;
		$output .= $this->close_form();
		$output .= '<a class="close-reveal-modal"><i></i></a>';
		return $output;
	}

}