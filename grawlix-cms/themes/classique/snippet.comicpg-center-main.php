<!-- GRAWLIX TEMPLATE: This comes from snippet.comicpg-top-center -->

<!-- Comic navigation -->
<?=snippet('comic-nav')?>

	<article itemscope itemtype="http://schema.org/CreativeWork" class="comic-stage">

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
	</article>

<!-- Comic navigation -->
<?=snippet('comic-nav')?>

<!-- Social media -->
<?=snippet('share')?>