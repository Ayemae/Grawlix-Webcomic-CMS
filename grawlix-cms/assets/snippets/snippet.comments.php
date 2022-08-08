<?php
$info = $this->services['comments']; // Get info from database via page class
$open_output = '<article id="comments"><h4>Reader comments</h4>';
$close_output = '</article>';
?>

<<<<<<< HEAD
<?php if ( !empty($info) && !empty($info['disqus']) ) : ?>
=======
<?php if ( !empty($info['disqus']) ) : ?>
>>>>>>> ab7b285a732339fb9df9fdba1161eeee8c268d8b
<?=$open_output?>
<div id="disqus_thread"></div><!-- Comments load here -->
<script type="text/javascript">
	/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
	var disqus_shortname = '<?=$info['disqus']?>';
	var disqus_url = '<?=show('permalink')?>';
<<<<<<< HEAD
	//Uncomment the next line only if you're doing a fresh install or are only about to add Disqus comments. If you already have Disqus comments, this line might unlink your existing Disqus threads from the pages.
	//var disqus_identifier = '<?=show('page_id')?>'; 
=======
    //Comment out the next line if you're updating Grawlix and you were previously using the permalink URLs instead of page identifiers, to avoid breaking existing Disqus threads
    var disqus_identifier = '<?=show('page_id')?>';  
>>>>>>> ab7b285a732339fb9df9fdba1161eeee8c268d8b
	var disqus_disable_mobile = true;
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

<<<<<<< HEAD
<?php if ( !empty($info) && !empty($info['intensedebate']) ) : ?>
=======
<?php if ( !empty($info['intensedebate']) ) : ?>
>>>>>>> ab7b285a732339fb9df9fdba1161eeee8c268d8b
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
