<?php /* Asterisk 2.0 — www.getgrawlix.com */ ?>

<!-- The archive page is simple. All links come from the show('archive_content') line. Use your admin panel to change up how links are arranged, and check your tone files for their styles. -->

<!-- Get the site-wide header. -->
<?=snippet('header')?>

<!-- GRAWLIX TEMPLATE: This comes from page.archive -->
			<main class="page__content">
				<h2><?=show('archive_headline')?></h2>
				<div class="archive-content">
					<?=show('archive_content')?>
				</div>
			</main>
<?=snippet('archive-nav')?>

<!-- Get the site-wide footer. -->
<?=snippet('footer')?>
