<?php

/**
 * Operations for themes and tones.
 *
 * Instantiate:
 * $theme = new GrlxTheme;
 */

class GrlxTheme {

	protected $db;
//	protected $milieuSettings;
	public    $milieuID;
	public    $multiTone;
	public    $defaultToneID;
	protected $action;
	protected $fileOps;
	protected $dirList;
	protected $dbDirList;
	protected $themesList;
	protected $uninstalledList;
	public    $toInstallOutput;
	public    $outputList;
	protected $dirName;
	protected $optList;
	protected $xml;
	protected $xmlFile;
	protected $xmlVersion;
	protected $themeMeta;
	protected $themeSlots;
	protected $themeID;
	protected $toneID;
	protected $toneList;
	protected $error;
	protected $success;
	public    $errorOutput;
	public    $successOutput;
	public    $toneSelectList;

	/**
	 * Setup
	 */
	public function __construct() {
		global $db;
		$this->db = $db;
		$this->getThemeMilieu();
		$this->fileOps = new GrlxFileOps;
		$this->getArgs(func_get_args());
		unset($this->error);
		unset($this->success);
		switch ($this->action) {
			case 'install':
				$this->dbCheckDirs();
				if ( !$this->dbDirList || !in_array($this->dirName,$this->dbDirList) ) {
					$this->installTheme();
				}
				unset($this->optList);
				break;
			case 'addtone':
				if ( $this->dirName && $this->toneList ) {
					$list = explode('||', $this->toneList);
					$this->optList[$this->dirName] = $list;
					$this->installTones();
				}
				break;
			case 'toggle-multi':
				$this->toggleMultiTone();
				break;
		}
		$this->setupManage();
	}

	/**
	 * Get theme settings from milieu table
	 */
	protected function getThemeMilieu() {
		$cols = array(
			'id',
			'title',
			'description',
			'label',
			'value'
		);
		$result = $this->db
			->where('milieu_type_id',2)
			->orderBy('id','ASC')
			->get('milieu',null,$cols);
		if ( $result ) {
			foreach ( $result as $key=>$val ) {
				switch ($val['label']) {
					case 'tone_id':
						$this->milieuID['tone'] = $val['id'];
						$this->defaultToneID = $val['value'];
						break;
					case 'multi_tone':
						$this->milieuID['switch'] = $val['id'];
						$this->multiTone = $val['value'];
						break;
				}
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
				if ( property_exists($this, $key) ) {
					$this->{$key} = $val;
				}
			}
		}
	}

	/**
	 * Constructor actions for the manage/main themes view
	 */
	protected function setupManage() {
		$this->getDirList();
		$this->getDbList();
		$this->buildThemeList();
//		$this->dbCheckOptions();
//		$this->formatErrorOutput();
//		$this->formatSuccessOutput();
		$this->buildToneSelectList();
	}

	/**
	 * Toggle the value of the multi tone switch
	 */
	protected function toggleMultiTone() {
		$this->multiTone == 1 ? $int = 0 : $int = 1;
		$this->multiTone = $int;
		$data = array('value'=>$this->multiTone);
		$result = $this->db
			->where('id',$this->milieuID['switch'])
			->update('milieu',$data);
		if ( !$result ) {
			$this->error['multi-switch'] = 'Unable to change multi-theme setting.';
		}
	}

	/**
	 * Format errors for display in main script
	 */
	protected function formatErrorOutput() {
		unset($this->errorOutput);
		if ( $this->error['db_install'] ) {
			array_walk($this->error['db_install'],'strfunc_li_wrap');
			$output .= 'The following could not be added to the database:<ul>';
			$output .= implode('',$this->error['db_install']);
			$output .= '</ul>';
		}
		if ( $this->error['missing_file'] ) {
			array_walk($this->error['missing_file'],'strfunc_li_wrap');
			$output .= 'The following files cannot be found:<ul>';
			$output .= implode('',$this->error['missing_file']);
			$output .= '</ul>';
		}
		if ( $this->error['multi-switch'] ) {
			$output = $this->error['multi-switch'];
		}
		$this->errorOutput = $output;
	}

	/**
	 * Format success messages for display in main script
	 */
	protected function formatSuccessOutput() {
		unset($this->successOutput);
		if ( $this->success['theme_install'] && $this->success['tone_install'] ) {
			$count = count($this->success['tone_install']);
			$toneStr = qty('tone',$count);
			$output = 'Theme <b>'.$this->themeMeta['title'].'</b> and '.$toneStr.' have been installed.';
		}
		elseif ( $this->success['theme_install'] ) {
			$output = 'Theme <b>'.$this->themeMeta['title'].'</b> has been installed.';
		}
		elseif ( $this->success['tone_install'] ) {
			array_walk($this->success['tone_install'],'strfunc_li_wrap');
			$output  = 'New tones have been installed:<ul>';
			$output .= implode('',$this->success['tone_install']);
			$output .= '</ul>';
		}
		$this->successOutput = $output;
	}

	/**
	 * Build list of themes for use in main script view
	 */
	protected function buildThemeList() {
		if ( $this->dirList ) {
			$this->checkThemeInstalled();
			$this->checkTonesInstalled();
		}
		if ( $this->themesList ) {
			foreach ( $this->themesList as $dir=>$info ) {
				$info['id'] ? $themeID = $info['id'] : $themeID = $dir;
				if ( $info['preview'] ) {
					$info['preview'] = '../'.DIR_THEMES.$dir.'/'.$info['preview'];
				}
				else {
					$info['preview'] = '../'.DIR_SYSTEM_IMG.'no_preview.svg';
				}
				$this->outputList[$themeID] = $info;
			}
		}
	}

	/**
	 * Build list of installed themes and tones for use in a select form element
	 */
	protected function buildToneSelectList() {
		$this->toneSelectList[] = array(
			'id' => 0,
			'title' => 'None'
		);
		if ( $this->themesList ) {
			foreach ( $this->themesList as $id=>$array) {
				$tones = $array['tones'];
				if ( $tones ) {
					foreach ( $tones as $key=>$info ) {
						if ( $info['title'] != 'placeholder' ) {
							$str = $array['title'].' — '.$info['title'];
						}
						$this->toneSelectList[$info['id']] = array(
							'id' => $info['id'],
							'title' => $str
						);
					}
				}
			}
		}
	}

	/**
	 * Get list of directories inside front-end themes directory
	 */
	protected function getDirList() {
		$list = $this->fileOps->get_dir_list('../'.DIR_THEMES);
		if ( $list ) {
			foreach ( $list as $item ) {
				if ( is_dir('../'.DIR_THEMES.$item) ) {
					$this->dirList[$item] = $item;
					$tones = $this->checkForToneFiles($item);
					$preview = $this->checkForPreview($item);
					$this->dirList[$item] = array(
						'label' => $item,
						'directory' => $item,
						'preview' => $preview,
						'tones' => $tones
					);
				}
			}
		}
	}

	/**
	 * Get list of installed themes in the db
	 */
	protected function getDbList() {
		$cols = array(
			'l.id',
			'l.title AS title',
			'label',
			'directory',
			't.id AS tone_id',
			't.title AS tone_title',
			't.options AS tone_options'
		);
		$result = $this->db
			->join('theme_tone t','t.theme_id = l.id','LEFT')
			->orderBy('l.title','ASC')
			->orderBy('t.title','ASC')
			->get('theme_list l',null,$cols);
		if ( $this->db->count > 0 ) {
			foreach ( $result as $item ) {
				$dir = $item['directory'];
				$theme[$dir] = array(
					'id'        => $item['id'],
					'title'     => ucfirst($item['title']),
					'label'     => $item['label'],
					'directory' => $item['directory']
				);
				$tone[$dir][$item['tone_title']] = array(
					'id'      => $item['tone_id'],
					'title'   => $item['tone_title'],
					'options' => $item['tone_options']
				);
			}
			foreach ( $theme as $dir=>$info ) {
				$info['tones'] = $tone[$dir];
				$this->dbList[$dir] = $info;
			}
		}
	}

	/**
	 * Compare options in db with the filesystem and report/fix discrepancies.
	 */
/*
	protected function dbCheckOptions() {
		if ( $this->dbList ) {
			foreach ( $this->dbList as $id=>$array ) {
				$dir = $array['directory'];
				foreach ( $array['tones'] as $key=>$list ) { // Check if linked CSS file is in its dir
					if ( mb_substr($list['options'],0,5,"UTF-8") == 'tone.' ) {
						if ( !in_array($list['options'],$this->dirList[$dir]) ) {
							$this->error['missing_file'][] = DIR_THEMES.$dir.'/'.$list['options'];
							// ASK IF MISSING FILES SHOULD BE DELETED
						}
						else {
							unset($this->dirList[$dir][$list['options']]);
						}
					}
					else {
						//echo '<pre>$list|';print_r($list);echo '|</pre>';
					}
				}
				if ( is_array($this->dirList[$dir]) ) { // Check for tone CSS file that's not in the db
					foreach ( $this->dirList[$dir] as $file ) {
						$prep['file'] = $file;
						$tone = $this->dbPrepTone($prep);
						$tone['theme_id'] = $id;
						$toneID = $this->dbAddTone($tone);
						if ( $toneID ) {
							$this->success['tone_install'][] = DIR_THEMES.$dir.'/'.$file;
						}
					}
				}
			}
			if ( $toneID ) {
				$this->getDbList(); // Get updated db info
			}
		}
	}
*/

	/**
	 * Check for which themes are in the database or not
	 */
	protected function checkThemeInstalled() {
		if ( $this->dirList )
		{
			foreach ( $this->dirList as $dir=>$info ) {
				if ( array_key_exists($dir,$this->dbList) ) {
					$info['id'] = $this->dbList[$dir]['id'];
					$info['title'] = $this->dbList[$dir]['title'];
				}
				else {
					$info['action'] = 'install';
					$info['title'] = strfunc_make_title($dir);
				}
				$this->themesList[$dir] = $info;

			}
		}
	}

	/**
	 * Check for which tones are installed or not
	 */
	protected function checkTonesInstalled() {
		foreach ( $this->themesList as $dir=>$info ) {
			unset($dirTones);
			unset($dbTones);
			// Only check the installed themes for now
			if ( !$this->themesList[$dir]['action'] ) {
				$dirTones = $info['tones'];
				$dbTones = $this->dbList[$dir]['tones'];
				// Add placeholders to the list
				if ( $dbTones['placeholder'] ) {
					$this->themesList[$dir]['tones'] = $dbTones;
				}
				// Check for matching tones
				elseif ( is_array($dbTones) && is_array($dirTones) ) {
					$missingTone = array_diff_key($dbTones,$dirTones);
					$newTone = array_diff_key($dirTones,$dbTones);
					if ( $missingTone ) {
						foreach ( $missingTone as $title=>$options ) {
							$dbTones[$title]['action'] = 'missing';
						}
					}
					if ( $newTone ) {
						foreach ( $newTone as $title=>$options ) {
							$dirTones[$title]['action'] = 'install';
						}
					}
					$this->themesList[$dir]['tones'] = $dbTones;
				}
			}
		}
	}

	/**
	 * Get list of installed directories from db for easy pre-install check.
	 */
	protected function dbCheckDirs() {
		$result = $this->db->get('theme_list',null,'directory');
		flatten_array($result);
		$this->dbDirList = $result;
	}

	/**
	 * Direct the theme install
	 */
	protected function installTheme() {
		if ( is_dir('../'.DIR_THEMES.$this->dirName) ) {
			$this->checkForOptions();
		}
		if ( $this->xmlFile ) {
			$args['filepath'] = $this->xmlFile;
			$this->xml = new GrlxXML($args);
			$this->xmlVersion = $this->xml->version;
			$this->parseXML();
		}
		$this->buildThemeMeta();
		$this->dbAddTheme();
		if ( $this->themeID > 0 ) {
			$this->successOutput = 'Theme installed.';

			$tone_list = $this->checkForToneFiles($this->dirName);
			if ( $tone_list )
			{
				foreach ( $tone_list as $tone_info )
				{
					$tone_info['theme_id'] = $this->themeID;
					$tone_info['date_created'] = $this->db->now();
					$id = $this->db->insert('theme_tone',$tone_info);
				}
			}

//			$this->installTones($tone_list);
			if ( $this->themeSlots ) {
				$this->dbAddSlots();
			}
		}
		else {
			$this->error['db_install'][] = 'theme: '.$this->themeMeta['title'];
		}
	}

	/**
	 * Install either placeholder tone or references to css files
	 */
	protected function installTones($tone_list=null) {
		if ( $this->optList[$this->dirName] ) { // Has tone CSS files
			foreach ( $this->optList[$this->dirName] as $file ) {
				$prep['file'] = $file;
				$tone = $this->dbPrepTone($prep);
				$tone['theme_id'] = $this->themeID;
				$toneID = $this->dbAddTone($tone);
				if ( $toneID ) {
			//		$this->success['tone_install'][] = $file;
				}
				else {
					$this->error['db_install'][] = 'tone: '.$tone['title'];
				}
			}
		}
		else { // Add a placeholder tone
			$tone = $this->dbPrepTone();
			$tone['theme_id'] = $this->themeID;
			$toneID = $this->dbAddTone($tone);
			if ( !$toneID ) {
				$this->error['db_install'][] = 'tone link: '.$this->themeMeta['title'];
			}
		}
	}

	/**
	 * Check a theme directory for optional CSS files starting with ‘tone.’ & ending with ‘.css’
	 *
	 * @return array $tones - list of tone files
	 */
	protected function checkForToneFiles($dir=null) {
		$path = '../'.DIR_THEMES.$dir.'/';
		$list = $this->fileOps->get_dir_list($path);
		if ( $list ) {
			foreach ( $list as $file ) {
				$parts = explode('.',$file);
				if ( is_file($path.$file) && $parts[0] == 'tone' && end($parts) == 'css' ) {
					array_shift($parts);
					array_pop($parts);
					$title = implode('',$parts);
					$tones[$title] = array('title'=>$title,'options'=>$file);
				}
			}
			return $tones;
		}
	}

	/**
	 * Check a theme directory for optional preview image starting with ‘theme’ & ending with any allowed image extension
	 *
	 * @param string $dir - item to open
	 * @return string $name - filename of preview image
	 */
	protected function checkForPreview($dir=null) {
		$path = '../'.DIR_THEMES.$dir.'/';
		$list = $this->fileOps->get_dir_list($path);
		if ( $list ) {
			foreach ( $list as $file ) {
				$parts = explode('.',$file);
				if ( is_file($path.$file) && $parts[0] == 'theme' ) {
					$ext = $this->fileOps->check_allowed_types($path.$file);
					if ( $ext ) {
						$name = 'theme'.$ext;
						return $name;
					}
				}
			}
		}
	}

	/**
	 * Check for an optional XML file included with the theme
	 */
	protected function checkForOptions() {
		$file = '../'.DIR_THEMES.$this->dirName.'/theme.xml';
		if ( is_file($file) ) {
			$this->xmlFile = $file;
		}
	}

	/**
	 * Read the theme xml node
	 */
	protected function parseXML() {
		switch ($this->xmlVersion) {
			case '1.0':
				$this->themeMeta = $this->xml->getChildren('/metadata');
				$this->themeSlots = $this->xml->getClonesChildren('/slots');
				break;
		}
	}

	/**
	 * Generate the minimum metadata
	 */
	protected function buildThemeMeta() {
		if ( !$this->themeMeta['title'] ) {
			$str = strfunc_make_title($this->dirName);
			$this->themeMeta['title'] = $str;
		}
		if ( !$this->themeMeta['label'] ) {
			$this->themeMeta['label'] = $this->dirName;
		}
		if ( !$this->themeMeta['directory'] ) {
			$this->themeMeta['directory'] = $this->dirName;
		}
		$this->themeMeta['date_created'] = $this->db->now();
	}

	/**
	 * Add theme info to db
	 */
	protected function dbAddTheme() {
		if ( $this->themeMeta ) {
			$insertID = $this->db->insert('theme_list',$this->themeMeta);
		}
		if ( $insertID ) {
			$this->themeID = $insertID;
		}
	}

	/**
	 * Prepares different types of tone formats for db install
	 *
	 * @param array $array - info to be prepped
	 * @return array $info - array formatted for easy db insert
	 */
	protected function dbPrepTone($array=null) {
		if ( !$array ) { // Placeholder
			$info['title'] = 'placeholder';
		}
		if ( $array['file'] ) { // CSS file
			$title = explode('.',$array['file']);
			array_shift($title);
			array_pop($title);
			$title = implode(' ',$title);
			$info['title'] = $title;
			$info['options'] = $array['file'];
		}
		return $info;
	}

	/**
	 * Add tone info to db
	 *
	 * @param array $info - tone info to be added
	 * @return int $id - unique ID for tone
	 */
	protected function dbAddTone($info=null) {
		if ( $info ) {
			$info['date_created'] = $this->db->now();
			$id = $this->db->insert('theme_tone',$info);
		}
		return $id;
	}

	/**
	 * Insert slot info into db
	 */
	protected function dbAddSlots() {
		switch ($this->xmlVersion) {
			case '1.0':
				$slots = $this->themeSlots;
				foreach ( $slots as $key=>$slot ) {
					if ( $slot['type'] == 'ad' ) {
						$slot['theme_id'] = $this->themeID;
						$slotID = $this->db->insert('theme_slot',$slot);
						if ( $slotID ) {
							$this->themeSlots[$key]['theme_id'] = $this->themeID;
							$this->themeSlots[$key]['slot_id'] = $slotID;
						}
						else {
							$this->error['db_install'][] = 'slot: '.$slot['label'];
						}
					}
				}
				break;
		}
	}
}