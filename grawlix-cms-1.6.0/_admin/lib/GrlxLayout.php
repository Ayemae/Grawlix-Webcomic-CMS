<?php

/*
$l = new GrlxLayout();
$l-> decode('sg12|sg12|sg06,sg06');
$l-> content = array('aa','bb','cc','dd');
echo $l-> populate();
*/

class GrlxLayout {

	public $filler = '…';
	public $height_unit = 'rem';

	function GrlxLayout($string=''){
		if ( $string ) {
			return $this-> decode($string);
		}
	}

	function decode($encoded=''){
		$encoded ? $encoded : $encoded = $this-> encoded;
		$this-> encoded = $encoded;
		$rows = explode("\n",$encoded);
		$this-> layout = '';
		if ( $rows ) {
			foreach ( $rows as $this_row ) {
				$result .= '<div class="row">'."\n";
				$result .= $this-> interpret_row($this_row,$height);
				$result .= '</div>'."\n";
			}
		}
		return $result;
	}

	function interpret_row($row=''){
		if ( $row ) {
			$row_set = explode(':',$row);
			$height = trim($row_set[0]);
			$cols = trim($row_set[1]);
			if ( $cols ) {
				$cols = explode(',',$cols);
				foreach ( $cols as $this_col ) {
					$result .= $this-> interpret_col($this_col,$height);
				}
			}
		}
		return $result;
	}

	function interpret_col($col='',$height='5'){
		$col = trim($col);
		if ( $col ) {
			$col_set = explode('#', $col);
			$id = trim($col_set[1]);
			$span = trim($col_set[0]);
			$result = '	<div id="'.$id.'" style="height:' . $height . $this-> height_unit . '" class="small-'.$span.' columns">'.$this-> filler.'</div>'."\n";
		}
		return $result;
	}

	function height_unit($string='') {
		$string = trim($string);
		if ( $string ) {
			$this-> height_unit = $string;
		}
		else {
			$this-> height_unit = 'rem';
		}
	}
}