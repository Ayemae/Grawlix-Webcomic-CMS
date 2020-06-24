
		<nav role="navigation">
			<ul class="text-center">
<?php if ( !$this->isFirst ) : ?>
				<li>
					<a class="<?=show('comic_css_first')?>" href="<?=show('comic_url_first')?>" title="First comic">
						First
					</a>
				</li>
				<li>
					<a class="<?=show('comic_css_prev')?>" href="<?=show('comic_url_prev')?>" title="Previous comic" rel="prev">
						Previous
					</a>
				</li>
<?php endif; ?>
<?php if ( !$this->isLatest ) : ?>
				<li>
					<a class="<?=show('comic_css_next')?>" href="<?=show('comic_url_next')?>"title="Next comic" rel="next">
						Next
					</a>
				</li>
				<li>
					<a class="<?=show('comic_css_latest')?>" href="<?=show('comic_url_latest')?>" title="Latest comic">
						Latest
					</a>
				</li>
<?php endif; ?>
			</ul>
		</nav>


