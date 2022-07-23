<?php

/**
 * Common functions involving the server filesystem.
 *
 * # Instantiate:
 * $fileops = new GrlxFileOps;
 *
 * # Uploading:
 * Given <input type="file name="these_files"/>
 * $fileops-> up_set_destination_folder('../comics/my-comic-or-something');
 * $success = $fileops-> up_process('these_files');
 *
 * ## Optional:
 * $fileops-> up_set_max_size('50000'); // Maximum file size in bytes
 * $fileops-> up_check_type($list); // Array of allowed file types
 */

class GrlxFileOps {

	public    $db;
	public    $new_directory = true;
	public    $error_list = array();
	protected $action;
	protected $dir_item;
	protected $file_item;
	public    $item_contents;
	protected $allowed_types;
	protected $max_size;
	protected $max_file_uploads;
	protected $destination_folder;

	/**
	 * Set defaults
	 */
	public function __construct() {
		$this->allowed_types = array('image/png','image/jpg','image/jpeg','image/gif','image/svg+xml');
		$this-> max_size = ($this-> convertBytes(ini_get( 'upload_max_filesize' )));
		$this-> max_file_uploads = ini_get( 'max_file_uploads' );
		$this->getArgs(func_get_args());
		if ( $this->action ) {
			$method = $this->action;
			if ( method_exists($this,$method) ) {
				$this->{$method}();
			}
		}
	}

	/**
	 * Pass in any arguments
	 *
	 * @param array $list - arguments from main script
	 */
	protected function getArgs($list=null) {
		$list = $list[0];
		if ( isset($list) ) {
			foreach ( $list as $key=>$val ) {
				if ( property_exists($this,$key) ) {
					$this->{$key} = $val;
				}
			}
		}
	}

	/**
	 * Set the var for directory to work with
	 *
	 * @param string $str - directory name with path
	 */
	public function set_dir($str=null) {
		$this->dir_item = $str;
	}

	/**
	 * Set the var for file to work with
	 *
	 * @param string $str - file name with path
	 */
	public function set_file($str=null) {
		$this->file_item = $str;
	}

	/**
	 * Fill the var with file contents
	 *
	 * @param string $str - the contents for the file
	 */
	public function set_contents($str=null) {
		$this->item_contents = $str;
	}

	/**
	 * Check if a directory exists and is writable, if not make the dir
	 *
	 * @param string $path - directory to scan
	 * @return string $alert - an error dialog if something is wrong
	 */
	function check_or_make_dir($path=null){
		if ( !is_dir($path) ) {
			@$new_dir = mkdir($path);
			if ( !$new_dir ) {
				$message = new GrlxAlert;
				$alert = $message->alert_dialog('I can’t find the '.$path.' directory.');
			}
		}
		elseif ( !is_writable($path) ) {
			$message = new GrlxAlert;
			$alert = $message->alert_dialog('I don’t have permission to save files in '.$path.'. Please <a href="diag.health-check.php#permissions">change access privileges</a>.');
		}
		return $alert;
	}

	/**
	 * Check if file is type in allowed_types list
	 *
	 * @param string $path - item to open
	 * @return string $ext - file extension
	 */
	public function check_allowed_types($path=null) {
		if ( function_exists('finfo_openn') ){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$item = finfo_file($finfo, $path);
		}
		// Account for servers without PECL fileinfo
		// See http://php.net/manual/en/function.finfo-open.php and http://php.net/manual/en/function.mime-content-type.php
		elseif ( function_exists('mime_content_type') ) {
			$item = mime_content_type($path);
		}
		// If servers can’t detect a file’s mime type, then rely on its extension.
		else {
			$parts = pathinfo($path);
			if ( $parts['extension'] == 'svg' ) {
				$item = 'image/'.$parts['extension'].'+xml';
			}
			else {
				$item = 'image/'.$parts['extension'];
			}
		}
		if ( $item && in_array($item, $this->allowed_types) ) {
			$parts = pathinfo($path);
			$ext = '.'.$parts['extension'];
			return $ext;
		}
	}

	/**
	 * Get the contents of a file
	 *
	 * @param string $path - item to open
	 * @return string $str - file contents
	 */
	public function read_file($path=null) {
		if ( $this->file_item ) {
			$path = $this->file_item;
		}
		$str = @file_get_contents($path);
		$this->item_contents = $str;
		return $str;
	}

	/**
	 * Save a file
	 *
	 * @return boolean $result - true on successful save
	 */
	public function save_file() {
		$path = $this->file_item;
		if ( ( is_file ($path) && is_writable($path) ) || !is_file($path) ) {
			$fp = fopen($path, 'w');
			fwrite($fp, $this->item_contents);
			fclose($fp);
			$result = true;
		}
		return $result;
	}

	/**
	 * Get the names of items in a directory, not recursive
	 *
	 * @param string $path - directory to scan
	 * @return array $list - list of names
	 */
	public function get_dir_list($path=null) {
		if ( $this->dir_item ) {
			$path = $this->dir_item;
		}
		if ( $path && is_string($path) && is_dir($path) ) {
			if ( $handle = opendir($path) ) {
				while ( false !== ($entry = readdir($handle)) ) {
					if ( substr($entry,0,1) != '.' ) {
						$list[] = $entry;
					}
				}
			}
		}
		return $list;
	}

	/**
	 * Get the names of all images in the comics directory
	 *
	 * @return array $list - list of names
	 */
	public function comics_dir_list() {
		$dir_list = $this->get_dir_list('../'.DIR_COMICS_IMG);
		if ( $dir_list ) {
			foreach ( $dir_list as $sub_dir ) {
				$sub_dir = DIR_COMICS_IMG . '/' . $sub_dir;
				$this_dir = $this->get_dir_list($sub_dir);
				if ( $this_dir ) {
					foreach ( $this_dir as $filename ) {
						$filename = substr($sub_dir,2) . '/' . $filename;
						$list[$filename] = $filename;
					}
				}
			}
		}
		return $list;
	}

	/**
	 * Get the names of images in the comics directory that are _not_ in the database
	 *
	 * @return array $list - list of names
	 */
	public function new_comic_image_list() {
		$file_list = $this->comics_dir_list();
			if ( $file_list ) {
			$reference_list = get_image_reference($this->db);
			foreach ( $file_list as $key => $val ) {
				if ( !$reference_list[$key] ) {
					$list[$key] = $val;
				}
			}
		}
		return $list;
	}

	/**
	 * Get the names of images in the database that are missing from the comics directory
	 *
	 * @return array $list - list of names
	 */
	public function missing_comic_image_list() {
		$file_list = $this->comics_dir_list();
		$reference_list = get_image_reference($this->db);
		foreach ( $reference_list as $key => $val ) {
			if ( !$file_list[$key] ) {
				$list[$key] = $val;
			}
		}
		return $list;
	}

	function up_set_allowed_types($list=array()){
		$this-> allowed_types = $list;
	}
	function up_set_max_size($bytes){
		$this-> max_size = $bytes;
	}
	function up_get_max_size(){
		return $this-> max_size;
	}
	function up_set_max_file_uploads($count){
		$this-> max_file_uploads = $count;
	}
	function up_get_max_file_uploads(){
		return $this-> max_file_uploads;
	}
	function up_set_destination_folder($where){
		$this-> destination_folder = $where;
	}

	function up_check_type($check_me){
		if ( in_array($check_me, $this-> allowed_types) ) {
			return true;
		}
		else {
			return false;
		}
	}

	function up_check_size($check_me){
		if ( $check_me <= $this-> max_size ) {
			return true;
		}
		else {
			return false;
		}
	}

	function up_check_error($check_me){
		if ( $check_me == 0 ) {
			return true;
		}
		else {
			return false;
		}
	}

	function up_upload_this(){
		$file_name = basename($this-> file_name);
		if ( $this-> new_directory == true ) {
			mkdir($this-> destination_folder.'/'.$this-> serial);
			$uploadfile = $this-> destination_folder.'/'.$this-> serial.'/'.$file_name;
		}
		else {
			$uploadfile = $this-> destination_folder.'/'.$file_name;
		}

		if (move_uploaded_file($this-> file_tmp, $uploadfile)) {
			return true;
		} else {
			return false;
		}
	}

	function up_process($which){
		if ( $_FILES[$which]['name'] ) {

			$this-> successful_upload = array();
			$i = 1;
			foreach ( $_FILES[$which]['name'] as $key => $val ) {

				$this-> file_tmp = $_FILES[$which]['tmp_name'][$key];
				$this-> file_error = $_FILES[$which]['error'][$key];
				$this-> file_type = $_FILES[$which]['type'][$key];
				$this-> file_size = $_FILES[$which]['size'][$key];
				$this-> file_name = $_FILES[$which]['name'][$key];

				$this-> serial = date('YmdHis').substr(microtime(),2,6).$i;

				$error_ok = $this-> up_check_error($this-> file_error);
				if ( $error_ok === true ) {
					$type_ok = $this-> up_check_type($this-> file_type);
					$size_ok = $this-> up_check_size($this-> file_size);
				}

				if ( $this-> file_error != 4 ) {
//					$this-> error_list[] = $error_ok;
				}
				if ( $type_ok === true && $size_ok === true ) {
					$upload_ok = $this-> up_upload_this();
				}
				if ( $type_ok != true ) {
					$this-> error_list[] = 'Not a valid image: '.$this-> file_name;
				}
				if ( $upload_ok === true ) {

					if ( $this-> new_directory == true ) {
						$this-> successful_upload[] = $this-> serial.'/'.$this-> file_name;
					}
					else {
						$this-> successful_upload[] = $this-> file_name;
					}
				}
				$i++;
			}

		}
		return $this-> successful_upload;
	}
	private function convertBytes( $value ) {
    if ( is_numeric( $value ) ) {
        return $value;
    } else {
        $value_length = strlen( $value );
        $qty = substr( $value, 0, $value_length - 1 );
        $unit = strtolower( substr( $value, $value_length - 1 ) );
        switch ( $unit ) {
            case 'k':
                $qty *= 1024;
                break;
            case 'm':
                $qty *= 1048576;
                break;
            case 'g':
                $qty *= 1073741824;
                break;
        }
        return $qty;
    }
	}
}
