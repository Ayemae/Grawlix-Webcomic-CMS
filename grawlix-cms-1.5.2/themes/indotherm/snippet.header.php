<!doctype html>
<html class="no-js" lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?=show('meta_head')?>
	<title><?=show('site_name')?> | <?=show('page_title')?></title>
	<?=show('support_head')?>
	<?=show('favicons')?>
</head>
<body role="document">
	<?=snippet('adminbar')?>
	<div class="wrap">
		<nav id="menu-widget">
			<header>
				<h1><a href="<?=show('home_url')?>"><?=show('site_name')?></a></h1>
				<a id="menu-tap" href="#"><i></i></a>
			</header>
			<ul id="menu-list">
				<?=show('menu')?>
			</ul>
		</nav>
		<header role="contentinfo" id="site-head">
			<h1><?=show('site_name')?></h1>
			<div>
				<a href="<?=show('home_url')?>"><img role="banner" src="<?=show('directory') ?>/themes/indotherm/images/fpo-sitebanner-800x200.svg" alt="comic image" /></a>
			</div>
			<nav id="menu">
				<ul class="text-center">
					<?=show('menu')?>
				</ul>
			</nav>
		</header>
