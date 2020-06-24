<?php /* Asterisk 2.0 — www.getgrawlix.com */ ?>
<?=snippet('header')?>

<!-- GRAWLIX TEMPLATE: This comes from page.comic -->
			<main class="page__content"><!-- Begin primary content — stuff readers came to see -->



				<article>
					<header>
						<h1><a href="<?=show('permalink')?>" rel="bookmark"><?=show('page_title')?></a></h1>
					</header>

					<nav class="content__nav-1">
<?=snippet('comic-nav')?>
					</nav>



					<!-- The comic image(s) -->
					<div class="comic-images">
						<a href="<?=show('comic_url_next')?>" class="content__image"><?=show('comic_image')?></a>
					</div>


					<div class="publication-date"><span>Published on</span> <?=show('date_publish')?></div>
					<nav class="content__nav-2">
<?=snippet('comic-nav')?>
					</nav>
				</article>
			</main><!-- end primary content -->





			<div class="page__secondary">
				<div class="secondary__share">

<!-- Social media -->
<?=snippet('share')?>
				</div>

<!-- Blog post -->
				<article class="secondary__blog">
					<h3><?=show('blog_title')?></h3>
<?=show('blog_post')?>

<!-- Use fields in the admin panel or add your own copyright date here. -->
					<div class="byline">Published on <?=show('date_publish')?> by <?=show('artist_name')?>.</div>
<?=snippet('comments')?>
				</article>

				<article class="secondary__transcript">
<?=snippet('transcript')?>
				</article>

				<div class="secondary__follow">
<?=snippet('follow')?>
				</div>

			</div><!-- End secondary content -->


<!-- Get the site-wide footer. -->
<?=snippet('footer')?>
