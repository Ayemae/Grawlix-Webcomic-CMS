<?php
$info = $this->showArchiveNav; // Get info from database via page class
if ( $info ) : ?>
	<nav class="archive">
		<ul>
			<li><a href="<?=show('archive_url_prev')?>" class="<?=show('archive_css_prev')?>" title="Previous page" rel="prev"><i></i>Previous</a></li>
			<li><a href="<?=show('archive_url')?>" title="Main page">Main</a></li>
			<li><a href="<?=show('archive_url_next')?>" class="<?=show('archive_css_next')?>" title="Next page" rel="next">Next<i></i></a></li>
		</ul>
	</nav>
<?php endif;