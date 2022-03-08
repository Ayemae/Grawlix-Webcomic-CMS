<!doctype html>
<!-- GRAWLIX TEMPLATE: This comes from snippet.header -->
<html>
	<head>
		<?=snippet('meta-head')?>
	</head>

	<body role="document">

		<!-- Only people who have logged in to this site's admin panel can see the admin bar. DO NOT REMOVE. -->
		<?=snippet('adminbar')?>

		<header role="contentinfo">
			<!-- Site/comic logo and name -->
			<a class="logo-link" href="<?=show('home_url')?>">
				<img class="logo-img" src="<?=show('directory')?>/themes/liquid/images/layout/logo.svg" alt="<?=show('site_name')?>"/>
			</a>
		</header>

		<!-- This site's main menu -->
		<nav role="navigation" class="site-nav">
		<label for="show-menu" id="show-menu-btn">
			Show Site Menu
		</label>
		<input type="checkbox" id="show-menu" role="button">
			<ul id="menu-list">
				<?=show('menu')?>
			</ul>
		</nav>

