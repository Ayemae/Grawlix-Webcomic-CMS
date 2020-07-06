		<?=snippet('header')?>
		<!-- template: page.comic -->
		<main>
			<?=snippet('comic-nav')?>
			<article itemscope itemtype="http://schema.org/CreativeWork" id="comic">
				<header>
					<h2 itemprop="name"><a href="<?=show('permalink')?>" rel="bookmark"><?=show('page_title')?></a></h2>
					<h2><?=show('date_publish')?></h2>
				</header>
				<figure>
					<a href="<?=show('comic_url_next')?>" rel="next"><?=show('comic_image')?></a>
				</figure>
			</article>
			<?=snippet('comic-nav')?>
			<div><?=snippet('share')?></div>
			<article role="article" itemscope itemtype="https://schema.org/BlogPosting" id="blog_post">
				<h3 itemprop="headline"><?=show('blog_title')?></h3>
				<div itemprop="articleBody">
					<?=show('blog_post')?>
					<p role="complementary" class="meta">Published on <?=show('date_publish')?> by <?=show('artist_name')?>.</p>
				</div>
			</article>
			<?=snippet('transcript')?>
			<?=snippet('comments')?>
			<div class="follow">
				<div>
				<?=snippet('follow')?>
				</div>
			</div>
		</main>
		<?=snippet('footer')?>
