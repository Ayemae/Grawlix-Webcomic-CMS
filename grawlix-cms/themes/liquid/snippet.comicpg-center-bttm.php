<!-- GRAWLIX TEMPLATE: This comes from snippet.comicpg-bttm-center -->

	<!-- Blog post -->
	<article role="article" itemscope itemtype="https://schema.org/BlogPosting" id="blog-post">
		<h2 itemprop="headline"><?=show('blog_title')?></h2>
		<div class="published" role="complementary" class="meta"><?=show('date_publish')?></div>
		<div itemprop="articleBody">
			<?=show('blog_post')?>
		</div>
	</article>

	<?=snippet('comments')?>