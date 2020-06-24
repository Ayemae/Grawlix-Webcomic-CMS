<?php

/**
 * Build panel message dialogs.
 */

class GrlxAlert {

	/**
	 * Storage for default values
	 */
	protected $alert_css = 'alert';
	protected $info_css = 'info';
	protected $success_css = 'success';
	protected $warning_css = 'warning';
	protected $alert_image = '/balloon-alert.svg';
	protected $info_image = '/balloon-info.svg';

	/**
	 * Storage for the output
	 */
	public $css;
	public $message;
	public $image;
	public $close_id;

	/**
	 * Set an id for the close link for use with ajax.
	 *
	 * @param string $str - an id
	 */
	public function special_id($str=null) {
		if ( $str ) {
			$this->close_id = $str;
		}
	}

	/**
	 * Assembles the dialog. Can be called directly after setting the needed class variables.
	 *
	 * @return string - html for dialog
	 */
	public function build_alert() {
		if ( $this->image === null ) {
			if ( ($this->css == 'alert') || ($this->css == 'warning') ) {
				$this->image = $this->alert_image;
			}
			else {
				$this->image = $this->info_image;
			}
		}
		isset($this->close_id) ? $id = ' id="'.$this->close_id.'"' : $id = null;
		$output  = '<div data-alert class="alert-box '.$this->css.'">';
		$output .= '<a href="#"'.$id.' class="close"></a><section>';
//		$output .= '<img src="../'.DIR_SYSTEM_IMG.$this->image.'" /><div>'.$this->message.'</div>';
		$output .= $this->message;
		$output .= '</section></div><br style="clear:both"/>';
		return $output;
	}

	/**
	 * Call for an alert dialog.
	 *
	 * @param string $msg - text for the dialog
	 * @return string - html for dialog
	 */
	public function alert_dialog($msg) {
		$this->css = $this->alert_css;
		$this->message = $msg;
		$this->image = $this->alert_image;
		$output = $this->build_alert();
		return $output;
	}

	/**
	 * Call for an info dialog.
	 *
	 * @param string $msg - text for the dialog
	 * @return string - html for dialog
	 */
	public function info_dialog($msg) {
		$this->css = $this->info_css;
		$this->message = $msg;
		$this->image = $this->info_image;
		$output = $this->build_alert();
		return $output;
	}

	/**
	 * Call for a success dialog.
	 *
	 * @param string $msg - text for the dialog
	 * @return string - html for dialog
	 */
	public function success_dialog($msg) {
		$this->css = $this->success_css;
		$this->message = '<strong>'.$this->add_enthusiasm().'</strong> '.$msg;
		$this->image = $this->info_image;
		$output = $this->build_alert();
		return $output;
	}

	/**
	 * Call for a warning dialog.
	 *
	 * @param string $msg - text for the dialog
	 * @return string - html for dialog
	 */
	public function warning_dialog($msg) {
		$this->css = $this->warning_css;
		$this->message = $msg;
		$this->image = $this->alert_image;
		$output = $this->build_alert();
		return $output;
	}

	/**
	 * Choose a word at random.
	 *
	 * @return string - wacky word for the success dialog
	 */
	protected function add_enthusiasm(){
		$success_woo = array(
			'Superbistic!',
			'Outstandish!',
			'Sensationalific!',
			'Dandy!',
			'Fabuastic!',
			'Awestruck!',
			'On ya!',
			'Ace!',
			'Brilliexcellant!',
			'Dazzlbring!',
			'Splindeed!',
			'Beyond infinity!',
			'Skookum!',
			'Keen!',
			'Cap’n Marvelous!',
			'Super… man!',
			'Zounds!',
			'Woohoo!',
			'Treeemendous!',
			'Go for gold!',
			'Impressive!',
			'Magnifi-can!',
			'Woah!',
			'Bang-up!',
			'Yippee!',
			'Yahoo!',
			'Hurrah!',
			'Three cheers!',
			'Attaway!',
			'Take a bow!',
			'Astonishing!',
			'Astronomical!',
			'Aw yeah!'
			
		);
		$x = rand(0,count($success_woo)-1);
		return $success_woo[$x];
	}
}