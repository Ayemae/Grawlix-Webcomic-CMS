<!-- GRAWLIX TEMPLATE: This comes from snippet.comic-nav -->
<nav role="navigation" class="container backnext-nav">
<?php if ( !$this->isFirst ) : ?>
	<a class="button <?=show('comic_css_first')?>" href="<?=show('comic_url_first')?>"><span>First</span></a>
	<a class="button <?=show('comic_css_prev')?>" href="<?=show('comic_url_prev')?>" rel="prev"><span>Previous</span></a>
<?php endif; ?>
<?php if ( !$this->isLatest ) : ?>
	<a class="button <?=show('comic_css_next')?>" href="<?=show('comic_url_next')?>" rel="next"><span>Next</span></a>
	<a class="button <?=show('comic_css_latest')?>" href="<?=show('comic_url_latest')?>"><span>Latest</span></a>
<?php endif; ?>
</nav>

