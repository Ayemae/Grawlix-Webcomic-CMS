<!-- GRAWLIX TEMPLATE: This comes from snippet.comments -->
<?php
$info = $this->services['comments']; // Get info from database via page class
$open_output = '<article id="comments">';
$close_output = '</article>';
?>

<?php if ( !empty($info['disqus']) ) : ?>
<?=$open_output?>
<div id="disqus_thread"></div><!-- Comments load here -->
<script type="text/javascript">/**
	*  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
	*  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables*/
	var disqus_config = function () {
	this.page.url = '<?=show('permalink')?>';  // Replace PAGE_URL with your page's canonical URL variable
	this.page.identifier = <?=show('page_id')?>;
  	this.page.title = '<?=show('page_title')?>';
	};
	/* * * DON'T EDIT BELOW THIS LINE * * */
	(function() { 
		var d = document, s = d.createElement('script');
		s.src = 'https://<?=$info['disqus']?>.disqus.com/embed.js';
		s.setAttribute('data-timestamp', +new Date());
		(d.head || d.body).appendChild(s);
		})();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink" target="_blank">comments powered by <span class="logo-disqus">Disqus</span></a>
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
