<?php
$info = $this->showArchiveNav; // Get info from database via page class
if ( $info ) : ?>
	<nav role="navigation">
		<ul class="text-center">
			<li>
				<a href="<?=show('archive_url_prev')?>" class="<?=show('archive_css_prev')?>" title="Previous page" rel="prev">
					Previous
				</a>
			</li>
			<li>
				<a href="<?=show('archive_url')?>" class="<?=show('archive_css_next')?>" title="Main page">
					Main
				</a>
			</li>
			<li>
				<a href="<?=show('archive_url_next')?>" class="<?=show('archive_css_next')?>" title="Next page" rel="next">
					Next
				</a>
			</li>
		</ul>
	</nav>
<?php endif;
