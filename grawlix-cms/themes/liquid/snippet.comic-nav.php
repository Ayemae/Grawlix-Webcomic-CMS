<!-- GRAWLIX TEMPLATE: This comes from snippet.comic-nav -->
<nav role="navigation" class="comic-nav">
	<a class="button <?=show('comic_css_first')?>" href="<?=show('comic_url_first')?>">
		first
	</a>
	<a class="button <?=show('comic_css_prev')?>" href="<?=show('comic_url_prev')?>" rel="prev">
		back
	</a>
	<a class="button <?=show('comic_css_next')?>" href="<?=show('comic_url_next')?>" rel="next">
		next
	</a>
	<a class="button <?=show('comic_css_latest')?>" href="<?=show('comic_url_latest')?>">
		latest
	</a>
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