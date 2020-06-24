<?php

/**
 * Common functions for working with the db.
 *
 * # Instantiate:
 * $db_ops = new GrlxDbOps;
 * This has been done in panl.init.php
 */

class GrlxDbOps {

	protected $db;
	protected $list;
	protected $where_id;

	/**
	 * Setup
	 */
	public function __construct($db=null) {
		$this->db = $db;
	}

	/**
	 * Set ID of row for the where clause
	 *
	 * @param int $id - the id of the item to use for “WHERE = $id”
	 */
	public function set_where_id($id=null) {
		$this->where_id = $id;
	}

	/**
	 * Set an array for use in this class
	 *
	 * @param array $array - list to use
	 */
	public function set_list($array=null) {
		$this->list = $array;
	}

	/**
	 * Clear the array
	 */
	public function unset_list() {
		unset($this->list);
	}

	/**
	 * Return an array whose keys are based on a given bit of data per item in the array itself
	 *
	 * @param str $field - the new key
	 */
	public function rekey_array($field='id') {
		foreach ( $this->list as $val ) {
			$key = $val[$field];
			$list[$key] = $val;
		}
		$this->set_list($list);
	}

// ! Chapter-centric

	/**
	 * Get list of chapters and tones, used in theme manager
	 *
	 * @return array $list - chapters with tones list
	 */
	public function get_chapter_and_tone_list() {
		$cols = array(
			'b.id AS comic_id',
			'b.title AS comic_title'
//			'c.id AS chapter_id',
//			'c.title AS chapter_title',
//			'tone_id'
		);
		$result = $this->db
//			->join('comic_chapter c', 'b.id = c.comic_book_id', 'LEFT')
			->orderBy('b.sort_order', 'ASC')
//			->orderBy('c.sort_order', 'ASC')
			->get('book b', null, $cols);
		if ( $this->db->count > 0 ) {
			foreach ( $result as $key => $val ) {
				$list[$val['comic_title']][$val['chapter_id']] = array(
					'chapter_title' => $val['chapter_title'],
					'tone_id' => $val['tone_id']
				);
			}
		}
		return $list;
	}

// ! Slots

	/**
	 * Get the non-ad slots and linked images for a given tone
	 *
	 * @param int $tone_id - tone id
	 * @return array $list
	 */
	public function get_slots_and_images($tone_id=null) {
		$tone_id === null ? $tone_id = $this->where_id : $tone_id;
		$cols = array(
			's.id AS id',
			'image_reference_id AS image_id',
			'm.id AS match_id',
			'url',
			'description',
			's.title',
			's.label',
			'max_width',
			'max_height'
		);
		$result = $this->db
			->join('theme_slot s', 'm.slot_id = s.id', 'INNER')
			->join('image_reference i', 'm.image_reference_id = i.id', 'INNER')
			->where('tone_id', $tone_id)
			->get('image_tone_match m', null, $cols);
		$this->set_list($result);
		$this->rekey_array();
		return $this->list;
	}

// ! Themes & tones

	/**
	 * Get list of installed themes
	 *
	 * @return array $list - theme list
	 */
	public function get_themes_installed_list() {
		$cols = array(
			'l.id AS id',
			'l.title AS title',
			'label',
			'directory',
			'COUNT(t.id) AS num_tones'
		);
		$result = $this->db
			->join('theme_tone t', 't.theme_id = l.id', 'LEFT')
			->orderBy('l.title', 'ASC')
			->groupBy('l.id', 'ASC')
			->get('theme_list l', null, $cols);
		if ( $this->db->count > 0 ) {
			foreach ( $result as $item ) {
				$list[$item['id']] = array(
					'title' => ucfirst( $item['title'] ),
					'label'=> $item['label'],
					'directory' => $item['directory'],
					'num_tones'=> $item['num_tones']
				);
			}
		}
		return $list;
	}

	/**
	 * Get list of installed themes and their tones
	 *
	 * @param string $group_by - 'theme_name' or 'tone_id'
	 * @return array $list - theme and tone list
	 */
	public function get_theme_and_tone_list($group_by='theme_name') {
		$cols = array(
			'l.id AS theme_id',
			'l.title',
			't.id AS tone_id',
			't.title AS tone_title'
		);
		$result = $this->db
			->join('theme_tone t', 'l.id = t.theme_id', 'LEFT')
			->orderBy('l.title', 'ASC')
			->orderBy('t.user_made', 'ASC')
			->orderBy('t.title', 'ASC')
			->get('theme_list l', null, $cols);
		switch ($group_by) {
			case 'tone_id':
				foreach ( $result as $val ) {
					$list[$val['tone_id']] = array(
						'theme' => $val['title'],
						'tone' => $val['tone_title']
					);
				}
				break;
			case 'theme_name':
				foreach ( $result as $val ) {
					$list[$val['title']][$val['tone_id']] = $val['tone_title'];
				}
				break;
		}
		return $list;
	}

	/**
	 * Get the milieu id for the site tone
	 *
	 * @return int - milieu id
	 */
	public function get_site_tone_milieu_id() {
		$result = $this->db
			->where('label', 'tone_id')
			->getOne('milieu', array('id'));
		return $result['id'];
	}

	/**
	 * Get the tone id for the site theme
	 *
	 * @return int - tone id
	 */
	public function get_site_tone_id() {
		$milieu_id = $this->get_site_tone_milieu_id();
		$result = $this->db
			->where('id', $milieu_id)
			->getOne('milieu', array('value'));
		$id = $result['value'];
		if ( ($id === null) || ($id == '') ) {
			$id = $this->get_first_tone_id();
			$this->update_site_tone_id($id);
		}
		return $id;
	}

	/**
	 * Get the tone id for the site theme
	 *
	 * @param int $id - new tone id for the site
	 * @return array $result
	 */
	public function update_site_tone_id($id=null) {
		$data = array('value' => $id);
		$result = $this->db
			->where('label', 'tone_id')
			->update('milieu', $data);
		return $result;
	}

	/**
	 * Get the tone id for a given chapter
	 *
	 * @param int $chapter_id - chapter id to check, can pass it here or with set_where_id
	 * @return int - tone id
	 */
	public function get_chapter_tone_id($chapter_id=null) {
		$chapter_id === null ? $chapter_id = $this->where_id : $chapter_id;
		$result = $this->db
			->where('id', $chapter_id)
			->getOne('comic_chapter', array('tone_id'));
		return $result['tone_id'];
	}

	/**
	 * Update the tone id for a given chapter
	 *
	 * @param int $tone_id - new tone id
	 * @param int $chapter_id - chapter id, can pass it here or with set_where_id
	 * @return
	 */
	public function update_chapter_tone_id($tone_id=null,$chapter_id=null) {
		$chapter_id === null ? $chapter_id = $this->where_id : $chapter_id;
		$data = array('tone_id' => $tone_id);
		$result = $this->db
			->where('id', $chapter_id)
			->update('comic_chapter', $data);
		return $result;
	}

	public function get_first_tone_id() {
		$result = $this->db
			->orderBy('id', 'ASC')
			->getOne('theme_tone', 'id');
		return $result['id'];
	}

	/**
	 * Will fetch db info for a theme & tone. If no tone id is given then it will fetch default tone.
	 *
	 * @return array $info - db info for selected theme & tone
	 */
	public function get_theme_and_tone($theme_id=null,$tone_id=null) {
		$cols = array(
			'l.id AS theme_id',
			'l.title',
			't.id AS tone_id',
			't.title AS tone_title',
			'directory_name',
			'description',
			'version',
			'author',
			'url',
			'user_made',
			'value_map',
			'value',
			'palette',
			'DATE_FORMAT(date_created,"%b %d, %Y") AS date'
		);
		$this->db->join('theme_tone t', 'l.id = t.theme_id', 'LEFT');
		$this->db->where('l.id', $theme_id);
		if ( $tone_id ) {
			$this->db->where('t.id', $tone_id);
		}
		else {
			$this->db->orderBy('t.id', 'ASC');
		}
		$info = $this->db->getOne('theme_list l', $cols);
		return $info;
	}
}