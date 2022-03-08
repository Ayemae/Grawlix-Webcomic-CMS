<!doctype html>
<!-- GRAWLIX TEMPLATE: This comes from snippet.header -->
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?=show('meta_head')?>

		<!-- Titles are great for getting found on Google. Learn more at http://www.getgrawlix.com/docs/1/seo and https://moz.com/learn/seo/title-tag -->
		<title><?=show('page_title')?> | <?=show('site_name')?></title>

		<?=show('support_head')?>
		<?=show('favicons')?>

	</head>

	<!-- “role” helps screen readers like Jaws and search engines like Google to figure out what your site’s all about. -->
	<body role="document">

		<!-- Only people who have logged in to your admin panel — namely, you — will see the admin bar. -->
		<?=snippet('adminbar')?>

		<header role="contentinfo" class="container sitewide-head">

			<!-- Site/comic logo and name -->
			<img src="http://placehold.it/960x100?text=Comic+logo+(change+in+snippet.header.php)" alt="logo for <?=show('site_name')?>"/>
			<h1><a href="<?=show('home_url')?>"><?=show('site_name')?></a></h1>
		</header>

		<!-- Back/next links -->
		<?=snippet('menu-main')?>
