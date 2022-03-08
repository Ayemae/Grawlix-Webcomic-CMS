<?=snippet('header')?>

<!-- GRAWLIX TEMPLATE: This comes from page.comic -->
<main>
	<?=snippet('comic-nav')?>
	<br/>

	<article itemscope itemtype="http://schema.org/CreativeWork" id="comic">

		<!-- Page title and link -->
		<header>
			<h2 itemprop="name"><a href="<?=show('permalink')?>" rel="bookmark"><?=show('page_title')?></a></h2>
		</header>

		<!-- The comic image(s) -->
		<figure>
			<a href="<?=show('comic_url_next')?>" rel="next"><?=show('comic_image')?></a>
		</figure>
		Published on <?=show('date_publish')?>
	</article>

	<!-- Back/next links -->
	<?=snippet('comic-nav')?>

	<!-- Social media -->
	<?=snippet('share')?>

	<!-- Blog post and sidebar -->
	<article role="article" itemscope itemtype="https://schema.org/BlogPosting" id="blog-post">
		<h3 itemprop="headline"><?=show('blog_title')?></h3>
		<div itemprop="articleBody">
			<?=show('blog_post')?>

			<!-- Use fields in the admin panel or add your own copyright date here. -->
			<div role="complementary" class="meta">Published on <?=show('date_publish')?> by <?=show('artist_name')?>.</div>
		</div>

		<?=snippet('comments')?>
	</article>

	<div>
		<?=snippet('transcript')?>
		<?=snippet('follow')?>
		<?=snippet('twitterstream')?>
	</div>
	<br class="clearfix"/>
</main>
<?=snippet('footer')?>
