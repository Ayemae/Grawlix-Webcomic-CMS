<?php

class GrlxDate {

	function GrlxDate(){
		$this-> day_list = $this-> build_day_list();
		$this-> month_list = $this-> build_month_list();
		$this-> year_list = $this-> build_year_list();
		$this-> date_string = date('Y-m-d H:i:s');
		$this-> date_format = 'F d, Y';
	}
	function build_month_list(){
		for($m=1;$m<13;$m++){
			$m < 10 ? $m = '0'.$m : $m;
			$month_list[$m] = date('F',mktime(0,0,0,$m,1,2014));
		}
		return $month_list;
	}
	function build_year_list($start=1900,$end=null){
		$end ? $end : $end = date('Y')+2;
		return range($start,$end);
	}
	function build_day_list(){
		return range(1,31);
	}
	function format_ymd_date($format='',$string=''){
		$string ? $string : $string = $this-> date_string;
		$format ? $format : $format = $this-> date_format;

		$string_set = array (
			substr($string,0,10),
			substr($string,11)
		);
		$string_set['date'] = explode('-',$string_set[0]);
		$string_set['time'] = explode(':',$string_set[1]);
		$output = date($format,mktime($string_set['time'][0],$string_set['time'][1],$string_set['time'][2],$string_set['date'][1],$string_set['date'][2],$string_set['date'][0]));
		return $output;
	}

	function build_year_options($current=null,$year_list=null){
		$year_list ? $year_list : $year_list = array_reverse($this-> year_list);
		if ( $year_list ) {
			foreach ( $year_list as $year ) {
				if ( $year == $current ) {
					$output .= '<option value="'.$year.'" selected="selected">'.$year.'</option>'."\n";
				}
				else {
					$output .= '<option value="'.$year.'">'.$year.'</option>'."\n";
				}
			}
		}
		return $output;
	}

	function build_month_options($current=null,$month_list=null){
		$month_list ? $month_list : $month_list = $this-> month_list;
		if ( $month_list ) {
			foreach ( $month_list as $key => $val ) {
				if ( $key == $current ) {
					$output .= '<option value="'.$key.'" selected="selected">'.$val.'</option>'."\n";
				}
				else {
					$output .= '<option value="'.$key.'">'.$val.'</option>'."\n";
				}
			}
		}
		return $output;
	}

	function build_day_options($current=null,$day_list=null){
		$day_list ? $day_list : $day_list = $this-> day_list;
		if ( $day_list ) {
			foreach ( $day_list as $day ) {
				if ( $day == $current ) {
					$output .= '<option value="'.$day.'" selected="selected">'.$day.'</option>'."\n";
				}
				else {
					$output .= '<option value="'.$day.'">'.$day.'</option>'."\n";
				}
			}
		}
		return $output;
	}

}