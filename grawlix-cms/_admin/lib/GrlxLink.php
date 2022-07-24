<?php

class GrlxLink extends GrlxLinkMain {

	function __construct($ref=null) {
		if ( $ref ) {
			$this->preset($ref);
		}
	}

	function preset($ref) {
		switch ($ref) {
			case 'adsense_help':
				$this->url = 'https://support.google.com/adsense/answer/181960?hl=en';
				$this->tap = 'provided by Google';
				$this->rel = 'external';
				$this->title = 'Learn more about AdSense code from Google.';
				break;

			case 'grlx ad list':
			case 'grlx_ad_list':
				$this->url = 'ad.list.php';
				$this->tap = 'See all ads';
				$this->title = 'Return to the ad list screen.';
				break;

			case 'grlx page list':
			case 'grlx_page_list':
				$this->url = 'sttc.page-list.php';
				$this->tap = 'Return to the page list';
				$this->title = 'View all static pages in your site.';
				break;

			case 'grlx menu':
				$this->url = 'site.nav.php';
				$this->tap = 'Return to the page list';
				$this->title = 'View all static pages in your site.';
				break;
		}
	}
}