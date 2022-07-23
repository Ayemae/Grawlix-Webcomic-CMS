	<?=snippet('header')?>
	<!-- template: page.archive -->
		<main>
			<article id="archive" class="archive-content">
				<h2><?=show('archive_headline')?></h2>
				<div>
					<?=show('archive_content')?>
				</div>
			</article>
			<?=snippet('archive-nav')?>
		</main>
	<?=snippet('footer')?>
