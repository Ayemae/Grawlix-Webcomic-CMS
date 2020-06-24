<!doctype html>
<!-- GRAWLIX TEMPLATE: This comes from snippet.header -->
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?=show('meta_head')?>

		<title><?=show('page_title')?> | <?=show('site_name')?></title>

		<?=show('support_head')?>
		<?=show('favicons')?>

	</head>

	<body role="document">
	<div class="page">
		<!-- Only people who have logged in to your admin panel — namely, you — will see the admin bar. -->
		<?=snippet('adminbar')?>

		<header class="page__header" role="contentinfo">


			<h2><a href="<?=show('home_url')?>"><span><?=show('site_name')?></span></a></h2>

		<?=snippet('menu-main')?>
		</header>

