<?php

/* ! GrlxPage aliases * * * * * * * */

function snippet($str=null) {
	global $grlxPage;
	return $grlxPage->loadSnippetTemplate($str);
}

function show($str=null) {
	global $grlxPage;
	$output = $grlxPage->returnShowOutput($str);
	// Add itemprops
	if ( $str == 'artist_name' ) {
		$output = '<span itemprop="author copyrightHolder">'.$output.'</span>';
	}
	if ( $str == 'copyright' ) {
		$output = '<span itemprop="copyrightYear">'.$output.'</span>';
	}
	return $output;
}

/**
 * Return ad output
 *
 * @param string $label - keyword used in the page templates, matches ad slot label
 * @param string $tag - type of element to enclose the ad
 * @param string $css - class name(s)
 * @return string $output - HTML for item
 */
function show_ad($label=null,$tag='div',$css='adspace') {
	global $grlxPage;
	$ad = $grlxPage->returnAdOutput($label);
	if ( $ad ) {
		$css ? $class = ' class="'.$css.'"' : $class = null;
		$output = '<'.$tag.$class.'>'.$ad.'</'.$tag.'>';
	}
	return $output;
}
