<?php

/* ! Setup * * * * * * * */

require('./_system/init.inc.php');

$menu_list = get_menu_items();

if ( is_null($menu_list) )
{
	die ('<h1>Is the chicken soup fresh?</h1><p>Your site has no menu items.</p>');
}

$milieu_list = get_milieu_list();

if ( is_null($milieu_list) )
{
	die ('<h1>The Batphone has no dial tone.</h1><p>Important site settings are missing.</p>');
}

if ( $milieu_list['timezone'] && $milieu_list['timezone'] != '')
{
	date_default_timezone_set($milieu_list['timezone']);
}


/* ! Route page request * * * * * * * */

// Putting this here for now -- it can move to constants.inc.php
define('ARCHIVE', 'archive');

// Prep the request_uri
$grlxRequest = new GrlxRequest($milieu_list['directory']);

// Get routes, including the path table
$routes = new GrlxRoute2($menu_list,$milieu_list['directory']);

// Find correct route
$route = $routes->getRoute($grlxRequest);

if ( empty($route) )
{
	die ('<h1>The Batmobile lost its wheel!</h1><p>Grawlix can\'t determine the correct page route.</p>');
}

$controller = 'GrlxPage2_'.$route->controller;
$grlxPage = new $controller();

// Pass site data to the controller
$args['menu'] = $menu_list;
$args['milieu'] = $milieu_list;
$grlxPage->setup($args, $route);

// Send request for further parsing
$grlxPage->contents($grlxRequest);


/* ! Build the page * * * * * * * */

$grlxPage->buildPage();
