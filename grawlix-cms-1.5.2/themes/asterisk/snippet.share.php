<!-- GRAWLIX TEMPLATE: This comes from snippet.share -->
<?php
$info = $this->services['share']; // Get info from database via page class
if ( count($info) > 3 ) { // Three of the items in $info are metadata about the comic
	$display = true;
}
if ( $display ) : ?>
		<h3>Share this comic</h3>
		<div class="share-icons">
<?php endif;
if ( $info['facebook'] ) : ?>
	<span><a target="_blank" class="social-icon facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?=$info['url']?>"><img src="<?=show('directory') ?>/themes/asterisk/images/fab/icon-facebook.svg" alt="Facebook"></a></span>
<?php endif;
if ( $info['googleplus'] ) : ?>
	<span><a target="_blank" class="social-icon googleplus" href="https://plus.google.com/share?url=<?=$info['title']?>%20<?=$info['url']?>"><img src="<?=show('directory') ?>/themes/asterisk/images/fab/icon-google-plus.svg" alt="Google Plus"></a></span>
<?php endif;
if ( $info['pinterest'] ) : ?>
	<span><a target="_blank" class="social-icon pinterest" href="http://pinterest.com/pin/create/button/?url=<?=$info['url']?>&amp;media=http://<?=$info['image']?>&amp;description=<?=$info['title']?>"><img src="<?=show('directory') ?>/themes/asterisk/images/fab/icon-pinterest.svg" alt="Pinterest"></a></span>
<?php endif;
if ( $info['reddit'] ) : ?>
	<span><a target="_blank" class="social-icon reddit" href="http://www.reddit.com/submit?url=<?=$info['url']?>"><img src="<?=show('directory') ?>/themes/asterisk/images/fab/icon-reddit.svg" alt="Reddit"></a></span>
<?php endif;
if ( $info['stumbleupon'] ) : ?>
	<span><a target="_blank" class="social-icon stumbleupon" href="http://www.stumbleupon.com/submit?url=<?=$info['url']?>&title=<?=$info['title']?>"><img src="<?=show('directory') ?>/themes/asterisk/images/fab/icon-stumbleupon.svg" alt="Stumble Upon"></a></span>
<?php endif;
if ( $info['tumblr'] ) : ?>
	<span><a target="_blank" class="social-icon tumblr" href="http://tumblr.com/share?s=&amp;v=3&t=<?=$info['title']?>&amp;u=<?=$info['url']?>"><img src="<?=show('directory') ?>/themes/asterisk/images/fab/icon-tumblr.svg" alt="Tumblr"></a></span>
<?php endif;
if ( $info['twitter'] ) : ?>
	<span><a target="_blank" class="social-icon twitter" href="http://twitter.com/home?status=<?=$info['title']?>%20<?=$info['url']?>"><img src="<?=show('directory') ?>/themes/asterisk/images/fab/icon-twitter.svg" alt="Twitter"></a></span>
<?php endif;
if ( $display ) : ?>
</div>
<?php endif;
