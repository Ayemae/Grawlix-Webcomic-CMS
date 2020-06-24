<?php

/**
 * Specific to front-end static pages
 */

class GrlxPage_Static extends GrlxPage {

	protected $load404;
	protected $xml;
	protected $xmlVersion;
	protected $code;
	protected $mdFunction;
	protected $layout;
	protected $items;
	protected $itemCount;
	protected $pattern;
	protected $patternName;
	protected $patternList;
	protected $blockGrid;

	/**
	 * Set defaults, etc.
	 */
	public function __construct() {
		parent::__construct(func_get_args());
		$this->template = $this->templateFileList['static'];
		$this->getStaticPage();
		if ( !$this->pageInfo ) {
			die('<h1>Oops.</h1><p>Could not get page info for "'.$this->path.'".</p>');
		}
		if ( substr($this->pageInfo['options'], 0,5) == '<?xml' ) {
			$args['stringXML'] = $this->pageInfo['options'];
			$this->xml = new GrlxXMLPublic($args);
			$this->xmlVersion = $this->xml->version;
			$this->routeVersion();
		}
		else {
			// Backwards compatibility
			$this->pageInfo['pattern'] = $this->pageInfo['options'];
			$this->layout['layout'] = $this->pageInfo['layout'];

			$content_blocks = $this->db
				->where('page_id', $this->pageInfo['id'])
				->orderBy('sort_order','ASC')
				->get('static_content',null,array('*'));

			$this->pageInfo['page_content'] = $this->assembleBlocks($content_blocks);
		}
		if ( $this->load404 == 'pattern_test' ) {
			$this->outputPatternTests();
		}
	}


	protected function assembleBlocks($content_blocks=array())
	{

// Which theme (folder) are we using?
	if ($this->milieu['tone_id'] && $this->milieu['tone_id'] > 0)
	{
		$cols = array(
			't.theme_id',
			't.id AS tone_id',
			't.options',
			'l.author',
			'directory'
		);
		$theme_info = $this->db
			->join('theme_list l','t.theme_id = l.id','INNER')
			->where('t.id',$this->milieu['tone_id'])
			->getOne('theme_tone t',$cols);
	}

	// Get the page’s default pattern.
	$pattern_filename_1 = DIR_THEMES.'/'.$theme_info['directory'].'/pattern.'.$this->pageInfo['pattern'].'.html';
	$pattern_filename_2 = DIR_THEMES.'/'.$theme_info['directory'].'/pattern.'.$this->pageInfo['pattern'].'.php';

	if (is_file($pattern_filename_1))
	{
		$default_page_pattern = file_get_contents($pattern_filename_1);
	}
	elseif (is_file($pattern_filename_2))
	{
		$default_page_pattern = file_get_contents($pattern_filename_2);
	}

	if ($this->layout['layout'] == 'grid')
		{
			$content .= '<div class="static-layout-grid"><!-- begin layout grid -->'."\n";
		}
		else
		{
			$content .= '<div class="static-layout-list"><!-- begin layout list -->'."\n";
		}
		if ( $content_blocks )
		{
			foreach ( $content_blocks as $key => $val )
			{
				if ($val['pattern'] && $val['pattern'] != '')
				{
					$pattern_filename = DIR_THEMES.'/'.$theme_info['directory'].'/pattern.'.$val['pattern'].'.php';
					if (is_file($pattern_filename))
					{
						$pattern_code = file_get_contents($pattern_filename);
					}
				}
				else
				{
					$pattern_code = $default_page_pattern;
				}

				if ( $pattern_code )
				{
					$val['content'] = $this->styleMarkdown($val['content']);
					$pattern_code = str_replace('{title}',$val['title'],$pattern_code);
					$pattern_code = str_replace('{link}',$val['url'],$pattern_code);
					$pattern_code = str_replace('{image}',$val['image'],$pattern_code);
					$pattern_code = str_replace('{content}',$val['content'],$pattern_code);
					$pattern_code = str_replace('{id}','block-'.$val['id'],$pattern_code);
					$content .= $pattern_code;
				}
				else
				{
					$content .= $this->styleMarkdown($val['content']);
				}
			}
		}
		$content .= "\n".'</div><!-- end layout -->'."\n";
		return $content;
	}

	/**
	 * Route the page build according to xml version number
	 */
	protected function routeVersion() {
		switch ( $this->xmlVersion ) {
			case '1.1':
				$this->defineBlockGrid();
				$this->mdFunction = $this->xml->getValue('/options/function');
				$this->layout['layout'] = $this->xml->getValue('/options/layout');
				$this->layout['pattern'] = $this->xml->getValue('/options/pattern');
				$this->layout['types'] = $this->xml->getClones('/options','option');
				$this->items = $this->xml->getStaticPageItems();
				$this->itemCount = count($this->items);
				$this->getTypeCode();
				$this->loadPattern();
				$this->formatContent();
				break;
			default:
				echo('Incompatible info');
				break;
		}
	}

	/**
	 * Get the abbreviations for the allowed content types
	 */
	protected function getTypeCode() {
		if ( $this->layout['types'] ) {
			foreach ( $this->layout['types'] as $type ) {
				$part[] = substr($type,0,1);
			}
			asort($part);
			$this->code = implode('',$part);
		}
	}

	/**
	 * Get page content based on url slug
	 */
	protected function getStaticPage() {
		$this->path[1] ? $str = $this->path[1] : $str = $this->path[0];
		if ( $this->load404 ) {
			$str = '/404';
		}
		$cols = array(
			'sp.id',
			'sp.title AS page_title',
			'description AS meta_description',
			'options',
			'tone_id',
			'layout',
			'url AS permalink'
		);
		$this->pageInfo = $this->db
			->join('static_page sp','p.rel_id = sp.id','INNER')
			->where('p.rel_type','static')
			->where('p.url',$str)
			->getOne('path p',$cols);
		if ( $this->pageInfo['tone_id'] ) {
			$this->theme['tone_id'] = $this->pageInfo['tone_id'];
		}
		$this->pageInfo['edit_this']['text'] = 'Edit static page';
		$this->pageInfo['edit_this']['link'] = 'sttc.page-edit.php?page_id='.$this->pageInfo['id'];
	}

	/**
	 * Append some prep for static pages
	 */
	public function buildPage() {
		parent::buildPage();
	}

	/**
	 * Define how many items wide a block grid with certain patterns is
	 */
	protected function defineBlockGrid() {
		// 2up
		$this->blockGrid[2] = array (
			'hil.header_left',
			'hil.header_right',
			'hilt.header_left',
			'hilt.header_right',
			'hilt.header_top-image_left',
			'hilt.header_top-image_right',
			'hit.header_left',
			'hit.header_right',
			'hit.header_top-image_left',
			'hit.header_top-image_right',
			'ht.header_left',
			'ht.header_right',
			'ilt.image_left',
			'ilt.image_right',
			'it.image_left',
			'it.image_right',
		);
	}

	/**
	 * Get the correct layout file
	 */
	protected function loadPattern() {
		$this->patternName = $this->code.'.'.$this->layout['pattern'];
		$file = $this->patternName.'.php';
		$this->pattern = $this->getFileContents('./'.DIR_PATTERNS.$file);
		if ( !$this->pattern ) {
			die('<h1>Oops.</h1><p>Could not load the file "'.$file.'".</p>');
		}
	}

	/**
	 * Plug one item into the given pattern
	 *
	 * @param array $id - a label to use as the element id
	 * @param array $item - info to format
	 * @return string $html - formatted output
	 */
	protected function formatXMLItem($id=null,$item=null) {
		$html = $this->pattern;
		$html = str_replace('{id}',$id,$html);
		foreach ( $item as $type=>$value ) {
			switch ( $type ) {
				case 'heading':
					$this->itemCount > 1 ? $i = 3 : $i = 2;
					$value = '<h'.$i.'>'.$value.'</h'.$i.'>';
					break;
				case 'image':
					if ( substr($value,0,4) != 'http' && substr($value,0,2) != '//' ) {
						$value = $this->milieu['directory'].$value;
					}
					break;
				case 'text':
					$this->styleMarkdown($value);
					break;
			}
			$html = str_replace('{'.$type.'}',$value,$html);
		}
		return $html;
	}

	/**
	 * Plug content into the layout
	 */
	protected function formatContent() {
		if ( $this->items && $this->pattern ) {
			foreach ( $this->items as $id=>$item ) {
				$itemOutput = $this->formatXMLItem($id,$item);
				if ( $this->layout['layout'] == 'grid' ) {
					$itemOutput = '<li>'.$itemOutput.'</li>';
				}
				$output .= $itemOutput;
			}
		}
		if ( $this->layout['layout'] == 'grid' ) {
			in_array($this->patternName,$this->blockGrid[2]) ? $css = 'two-up' : $css = 'three-up';
			$output = '<ul class="'.$css.'">'.$output.'</ul>';
		}
		if ( $output ) {
			$this->pageInfo['page_content'] = $output;
		}
	}

	/**
	 * Output a static page with all the patterns and some sample data
	 */
	protected function outputPatternTests() {
		// Make some test data
		$args['stringXML'] = '<?xml version="1.0" encoding="UTF-8"?><page version="1.1"><options><pattern></pattern><layout>list</layout><function></function><option>heading</option><option>image</option><option>link</option><option>text</option></options><content><item><pattern></pattern><heading><![CDATA[Here’s a headline]]></heading><image>http://placehold.it/500x260/4F378E/fff.png&amp;text=500x260</image><link>getgrawlix.com</link><text>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.

Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibu.</text></item></content></page>';
		$this->xml = new GrlxXMLPublic($args);
		$this->layout['layout'] = 'list';
		$this->items = $this->xml->getStaticPageItems();
		$item = $this->items['item-1'];
		// Get patterns and do output
		$path = './'.DIR_PATTERNS;
		if ( $path && is_string($path) && is_dir($path) ) {
			if ( $handle = opendir($path) ) {
				while ( false !== ($entry = readdir($handle)) ) {
					if ( substr($entry,0,1) != '.' && substr(strrchr($entry,'.'),1) == 'php' ) {
						$list[] = $entry;
					}
				}
			}
		}
		if ( $list ) {
			foreach ( $list as $file ) {
				$part = explode('.',$file);
				$this->patternList[$part[0]][] = $part[1];
			}
		}
		if ( $this->patternList ) {
			foreach ( $this->patternList as $code=>$list ) {
				foreach ( $list as $pattern ) {
					$this->patternName = $code.'.'.$pattern;
					$file = $this->patternName.'.php';
					$this->pattern = $this->getFileContents('./'.DIR_PATTERNS.$file);
					$output .= $this->formatXMLItem('item-1',$item).'<br/>'.$file.'<hr/><br/>';
				}
			}
		}
		if ( $output ) {
			$this->pageInfo['page_content'] = $output;
		}
	}

}
