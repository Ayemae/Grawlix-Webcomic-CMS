<?php

/**
 * Work with XML for comic books (archives and RSS options).
 *
 * Instantiate:
 * $xml = new GrlxXML_Book;
 */

class GrlxXML_Book extends GrlxXML {

	protected $db;
	protected $dirBook;
	protected $bookID;
	protected $infoXML;
	protected $archiveNew;
	protected $rssNew;
	public    $archive;
	public    $rss;
	public    $behavior;
	public    $structure;
	public    $layout;
	public    $meta;
	public    $saveResult;

	/**
	 * Set defaults, etc.
	 */
	public function __construct() {
		global $db;
		$this->db = $db;
		$this->dirBook = '../'.DIR_BOOK;
		$this->getArgs(func_get_args());
		if ( !$this->bookID && !$this->infoXML ) {
			echo('No bookID');
		}
		if ( $this->archiveNew ) {
			$this->loadOptions();
			parent::__construct();
			$this->updateArchive();
			$this->saveOptions();
		}
		if ( $this->rssNew ) {
			$this->loadOptions();
			parent::__construct();
			$this->updateRSS();
			$this->saveOptions();
		}
		if ( $this->infoXML ) { // load up the info/guide xml file
			$args['filepath'] = $this->dirBook.'book.xml';
			parent::__construct($args);
			$this->loadInfo();
		}
		else {
			$this->loadOptions();
			parent::__construct();
			$this->parseArchive();
			$this->parseRSS();
		}
	}

	/**
	 * For version 1.1
	 * Store the available settings for this XML
	 */
	protected function loadInfo() {
		$list = $this->getItemSets('/archive/behavior');
		if ( $list )
		{
			foreach ( $list as $option=>$info ) {
				$info['image'] = $this->dirBook.'behavior.'.$info['name'].'.svg';
				$this->archive['behavior'][] = $info;
			}
		}

		$list = $this->getItemSets('/archive/structure');
		if ( $list )
		{
			foreach ( $list as $option=>$info ) {
				$info['image'] = $this->dirBook.'structure.'.$info['name'].'.svg';
				$this->archive['structure'][] = $info;
			}
		}
		$this->archive['chapter']['layout'] = $this->getClones('/archive/chapter/','layout');
		foreach ( $this->archive['chapter']['layout'] as $key=>$info ) {
			$this->archive['chapter']['layout'][$key] = array(
				'name' => $info,
				'image' => $this->dirBook.'chapter.layout_'.$info.'.svg'
			);
		}
		$this->archive['chapter']['option'] = $this->getClones('/archive/chapter/','option');
		$this->archive['page']['layout'] = $this->getClones('/archive/page/','layout');
		foreach ( $this->archive['page']['layout'] as $key=>$info ) {
			$this->archive['page']['layout'][$key] = array(
				'name' => $info,
				'image' => $this->dirBook.'page.layout_'.$info.'.svg'
			);
		}
		$this->archive['page']['option'] = $this->getClones('/archive/page/','option');
		$this->rss['option'] = $this->getClones('/rss','option');
	}

	/**
	 * Get the XML for the current book and load it
	 */
	protected function loadOptions() {
		$result = $this->db
			->where('id',$this->bookID)
			->getOne('book','options');
		$this->stringXML = $result['options'];
	}

	/**
	 * Save XML for the current book
	 */
	protected function saveOptions() {
		$data = array('options'=>$this->simpleXML->asXML());
		$result = $this->db
			->where('id',$this->bookID)
			->update('book',$data);
		$result > 0 ? $this->saveResult = 'success' : $this->saveResult = 'error';
	}

	/**
	 * Interpret the XML for the book's archive
	 */
	protected function parseArchive() {
		$this->behavior = $this->getValue('/archive/behavior');
		$this->structure = $this->getValue('/archive/structure');
		$this->layout['page'] = $this->getValue('/archive/page/layout');
		$this->layout['chapter'] = $this->getValue('/archive/chapter/layout');
		$this->meta['page'] = $this->getClones('/archive/page','option');
		$this->meta['chapter'] = $this->getClones('/archive/chapter','option');
	}

	/**
	 * Interpret the XML for the book's RSS
	 */
	protected function parseRSS() {
		$this->rss = $this->getClones('/rss','option');
	}

	/**
	 * Write new settings to XML for the book's archive
	 */
	protected function updateArchive() {
		unset($this->simpleXML->archive);
		foreach ( $this->archiveNew as $key=>$val ) {
			if ( is_string($val) ) {
				$this->simpleXML->archive->{$key} = $val;
			}
			if ( is_array($val) ) {
				foreach ( $val as $key2=>$val2 ) {
					if ( is_string($val2) ) {
						$this->simpleXML->archive->{$key}->{$key2} = $val2;
					}
					if ( is_array($val2) ) {
						foreach ( $val2 as $key3=>$val3 ) {
							$this->simpleXML->archive->{$key}->{$key2}[$key3] = $val3;
						}
					}
				}
			}
		}
	}
	protected function updateRSS() {
		unset($this->simpleXML->rss);
		foreach ( $this->rssNew as $key=>$val ) {
			if ( is_string($val) ) {
				$this->simpleXML->rss->{$key} = $val;
			}
			if ( is_array($val) ) {
				foreach ( $val as $key2=>$val2 ) {
					if ( is_string($val2) ) {
						$this->simpleXML->rss->{$key}->{$key2} = $val2;
					}
					if ( is_array($val2) ) {
						foreach ( $val2 as $key3=>$val3 ) {
							$this->simpleXML->rss->{$key}->{$key2}[$key3] = $val3;
						}
					}
				}
			}
		}
	}
}
