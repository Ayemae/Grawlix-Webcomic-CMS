<!-- GRAWLIX TEMPLATE: This comes from page.comic -->

<?=snippet('header')?>

<main class="content">	

	<section class="content-main">

		<div class="comic-stage">
			<!-- Comic navigation -->
			<?=snippet('comic-nav')?>

			<article itemscope itemtype="http://schema.org/CreativeWork" id="swipenav">

				<!-- The comic page -->
				<figure id="comic">
					<?php if ( !$this->isLatest ) : ?>
						<a href="<?=show('comic_url_next')?>" rel="next">
					<?php endif; ?>
						
						<?=show('comic_image')?>

					<?php if ( !$this->isLatest ) : ?>
						</a>
					<?php endif; ?>
				</figure>
				<div id="swipenav-arrow-wrapper">
					<div id="swipenav-arrow-prev" class="swipenav-arrow" style="border-right: 40px solid #e5e5e5;">
					</div>
					<div id="swipenav-arrow-next" class="swipenav-arrow" style="border-left: 40px solid #e5e5e5;">
					</div>
				</div>
			</article>
			<!-- Comic navigation -->
			<?=snippet('comic-nav')?>
		</div>

		<!-- Social media -->
		<?=snippet('share')?>
	</section>

	<div class="under-comic">

		<div class="flex-wrapper one">
			<div class="flex-wrapper two">

				<section class="sidebar one"><?=snippet('sidebar1')?></section>

				<section class="content-main">
					<!-- Blog post -->
					<article role="article" itemscope itemtype="https://schema.org/BlogPosting" id="blog-post">
					<div class="blog-header">
						<h2 itemprop="headline"><?=show('blog_title')?></h2>
						<div class="published" role="complementary" class="meta"><?=show('date_publish')?></div>
					</div>
						<div itemprop="articleBody">
							<?=show('blog_post')?>
						</div>
					</article>

				<?=snippet('comments')?>
				</section>
			</div>

			<section class="sidebar two"><?=snippet('sidebar2')?></section>
		</div>

	</div> <!-- end 'under-comic' -->

</main>

<?=snippet('footer')?>
