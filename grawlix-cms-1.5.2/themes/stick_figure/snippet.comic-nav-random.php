<!-- GRAWLIX TEMPLATE: This comes from snippet.comic-nav-random -->
<nav role="navigation" class="backnext-nav">
<?php if ( !$this->isFirst ) : ?>
	<a class="button <?=show('comic_css_first')?>" href="<?=show('comic_url_first')?>" title="First comic"><span>First</span></a>
	<a class="button <?=show('comic_css_prev')?>" href="<?=show('comic_url_prev')?>" title="Previous comic" rel="prev"><span>Previous</span></a>
<?php endif; ?>
	<a class="button <?=show('comic_css_rand')?>" href="<?=show('comic_url_rand')?>" title="Random comic"><span>Random</span></a>
<?php if ( !$this->isLatest ) : ?>
	<a class="button <?=show('comic_css_next')?>" href="<?=show('comic_url_next')?>"title="Next comic" rel="next"><span>Next</span></a>
	<a class="button <?=show('comic_css_latest')?>" href="<?=show('comic_url_latest')?>" title="Latest comic"><span>Latest</span></a>
<?php endif; ?>
	<br class="clearfix"/>
</nav>
