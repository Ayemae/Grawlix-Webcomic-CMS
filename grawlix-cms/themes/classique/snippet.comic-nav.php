<!-- GRAWLIX TEMPLATE: This comes from snippet.comic-nav -->
<nav role="navigation" class="comic-nav">
				<ul class="list-style-type:none;">
<!--
<?php if ( !$this->isFirst ) : ?>
				<a class="<?=show('comic_css_first')?>" href="<?=show('comic_url_first')?>" title="First comic">
						<div class="navimg"><img src="/themes/classique/images/layout/first.png"></div>
					</a> 
						
<?php else: ?>
				<a class="<?=show('comic_css_first')?>" href="<?=show('comic_url_first')?>" title="First comic">
						<div class="navimg"><img src="/themes/classique/images/layout/firstnull.png"></div>
					</a>
<?php endif; ?>
-->
<?php if ( !$this->isFirst ) : ?>
				<a class="<?=show('comic_css_prev')?>" href="<?=show('comic_url_prev')?>" title="Previous comic" rel="prev">
						<div class="navimg"><img src="/themes/classique/images/layout/nav-prev.png"></div>
					</a>
<?php else: ?>
				<a class="<?=show('comic_css_prev')?>" href="<?=show('comic_url_prev')?>" title="Previous comic" rel="prev">
						<div class="navimg"><img src="/themes/classique/images/layout/nav-prev-null.png"></div>
					</a>
<?php endif; ?>
<?php if ( !$this->isLatest ) : ?>
				<a class="<?=show('comic_css_next')?>" href="<?=show('comic_url_next')?>"title="Next comic" rel="next">
						<div class="navimg"><img src="/themes/classique/images/layout/nav-next.png"></div>
					</a>
<?php else: ?>
				<a class="<?=show('comic_css_first')?>" href="<?=show('comic_url_next')?>" title="First comic">
						<div class="navimg"><img src="/themes/classique/images/layout/nav-next-null.png"></div>
					</a>

<?php endif; ?>
<!--
<?php if ( !$this->isLatest ) : ?>
				<a class="<?=show('comic_css_latest')?>" href="<?=show('comic_url_latest')?>" title="Latest comic">
						<div class="navimg"><img src="/themes/classique/images/layout/latest.png"></div>
					</a>
<?php else: ?>
				<a class="<?=show('comic_css_first')?>" href="<?=show('comic_url_latest')?>" title="First comic">
						<div class="navimg"><img src="/themes/classique/images/layout/latestnull.png"></div>
					</a>
<?php endif; ?>
-->
			</ul>
</nav>

<!-- This script allows arrow key navigation -->
<script type="text/javascript">
function navTo(destination) {
   if(destination != "/comic")
	   window.location = destination;};
document.onkeydown = function(e) {
	var prevPage = "<?=show('comic_url_prev')?>";
	var nextPage = "<?=show('comic_url_next')?>";
   if(!e) e = window.event;
   switch (e.keyCode) {
       case 37: //left arrow
           navTo(prevPage);
           break;
       case 39: //right arrow
           navTo(nextPage);
           break;};};
</script>
