<!-- GRAWLIX TEMPLATE: This comes from snippet.comments -->
<?php
$info = $this->services['comments']; // Get info from database via page class
$open_output = '<article id="comments">';
$close_output = '</article>';
?>

<?php if ( !empty($info['disqus']) ) : ?>
<?=$open_output?>
<div id="disqus_thread"></div><!-- Comments load here -->
<script type="text/javascript">
	/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
	var disqus_shortname = '<?=$info['disqus']?>';
	var disqus_url = '<?=show('permalink')?>';
	var disqus_disable_mobile = true;
	var disqus_config = function () {
		this.page.identifier = <?=show('page_id')?>;
		this.page.title = '<?=show('page_title')?>';
	};
	/* * * DON'T EDIT BELOW THIS LINE * * */
	(function() {
			var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
			dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	})();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
<?=$close_output?>
<?php endif; ?>


<?php if ( !empty($info['intensedebate']) ) : ?>
<?=$open_output?>
<script>
var idcomments_acct = '<?=$info['intensedebate']?>';
var idcomments_post_id = '<?=show('page_id')?>';
var idcomments_post_url = '<?=show('permalink')?>';
</script>
<span id="IDCommentsPostTitle" style="display:none"></span>
<div><script type="text/javascript" src="http://www.intensedebate.com/js/genericCommentWrapperV2.js"></script></div>
<?=$close_output?>
<?php endif;
