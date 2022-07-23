<?php

/*

$list[] = array ('title'=>'a', 'id'='202');
$list[] = array ('title'=>'b', 'id'='101');
$list[] = array ('title'=>'c', 'id'='303');
$list[] = array ('title'=>'d', 'id'='505');

$sl = new GrlxSelectList
$sl-> setName('name-of-element'); -----> <select name="name-of-element">
$sl-> setCurrent('x'); -----> <option value="x" selected="selected"> (this is optional)
$sl-> setList($list);
$sl-> setValueID('id'); -----> <option value="id">
$sl-> setValueTitle('title'); -----> <option>title</option>
$output = $sl-> buildSelect();

*/

class GrlxSelectList {

	private function buildOption($title,$value,$selected=false){
		if ( $selected !== false ) {
			$sel = ' selected="selected"';
		}
		$output = '<option value="'.$value.'"'.$sel.'>'.$title.'</option>'."\n";
		return $output;
	}
	public function buildSelect(){
		$output = '<select name="'.$this-> name.'"'.$this-> style.'>'."\n";
		if ( $this-> list ) {
			foreach($this-> list as $key => $val) {
				if ( $this-> current == $val[$this-> valueID] ) {
					$selected = true;
				}
				else {
					$selected = false;
				}
				$output .= $this-> buildOption($val[$this-> valueTitle],$val[$this-> valueID],$selected);
			}
		}
		$output .= '</select>'."\n";
		return $output;
	}


	function setStyle($value){
		$this-> style = ' style="'.$value.'"';
	}
	function setName($name){
		$this-> name = $name;
	}
	function setList($list){
		$this-> list = $list;
	}
	function setCurrent($current){
		$this-> current = $current;
	}
	function setValueID($id){
		$this-> valueID = $id;
	}
	function setValueTitle($title){
		$this-> valueTitle = $title;
	}

}
