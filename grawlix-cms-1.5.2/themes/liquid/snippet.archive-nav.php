<!-- GRAWLIX TEMPLATE: This comes from snippet.archive-nav -->
<?php
$info = $this->showArchiveNav; // Get info from database via page class
if ( $info ) : ?>
<nav role="navigation">
	<a class="<?=show('archive_css_prev')?>"  href="<?=show('archive_url_prev')?>"title="Previous page" rel="prev"><span>Previous chapter</span></a> | 
	<a class="button <?=show('archive_css_next')?>" href="<?=show('archive_url')?>" title="Comic archives"><span>Archives</span></a> | 
	<a class="button <?=show('archive_css_next')?>" href="<?=show('archive_url_next')?>" title="Next page" rel="next"><span>Next chapter</span></a>
</nav>
<?php endif;
