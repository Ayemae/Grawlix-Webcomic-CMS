<?=snippet('header')?>
<!-- template: page.comic -->
	<main>
		<header>
			<h3><a href="<?=show('permalink')?>" rel="bookmark"><?=show('page_title')?></a><?=show('edit_this')?></h3>
			<?=show('date_publish')?>
		</header>
		<figure>
			<a href="<?=show('link_next')?>" rel="next"><?=show('comic_image')?></a>
		</figure>
		<nav>
			<ul>
				<li><a href="<?=show('comic_url_first')?>" title="First comic">First</a></li>
				<li><a href="<?=show('comic_url_prev')?>" title="Previous comic" rel="prev">Previous</a></li>
				<li><a href="<?=show('comic_url_rand')?>" title="Random comic">Random</a></li>
				<li><a href="<?=show('comic_url_next')?>"title="Next comic" rel="next">Next</a></li>
				<li><a href="<?=show('comic_url_latest')?>" title="Latest comic">Latest</a></li>
			</ul>
		</nav>
	</main>
	<article>
		<h2><?=show('blog_title')?></h2>
		<?=show('blog_post')?>
		<p class="meta">Published on <?=show('date_publish')?> by <?=show('artist_name')?>.</p>
	</article>
<?=snippet('footer')?>
