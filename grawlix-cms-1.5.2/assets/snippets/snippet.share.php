<?php
$info = $this->services['share']; // Get info from database via page class
if ( count($info) > 3 ) { // Three of the items in $info are metadata about the comic
	$display = true;
}
if ( $display ) : ?>
<div class="share"><span>Share this comic</span>
<?php endif;
if ( $info['facebook'] ) : ?>
	<a target="_blank" class="facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?=$info['url']?>"><i></i></a>
<?php endif;
if ( $info['googleplus'] ) : ?>
	<a target="_blank" class="googleplus" href="https://plus.google.com/share?url=<?=$info['title']?>%20<?=$info['url']?>"><i></i></a>
<?php endif;
if ( $info['pinterest'] ) : ?>
	<a target="_blank" class="pinterest" href="http://pinterest.com/pin/create/button/?url=<?=$info['url']?>&amp;media=http://<?=$info['image']?>&amp;description=<?=$info['title']?>"><i></i></a>
<?php endif;
if ( $info['reddit'] ) : ?>
	<a target="_blank" class="reddit" href="http://www.reddit.com/submit?url=<?=$info['url']?>"><i></i></a>
<?php endif;
if ( $info['stumbleupon'] ) : ?>
	<a target="_blank" class="stumbleupon" href="http://www.stumbleupon.com/submit?url=<?=$info['url']?>&title=<?=$info['title']?>"><i></i></a>
<?php endif;
if ( $info['tumblr'] ) : ?>
	<a target="_blank" class="tumblr" href="http://tumblr.com/share?s=&amp;v=3&t=<?=$info['title']?>&amp;u=<?=$info['url']?>"><i></i></a>
<?php endif;
if ( $info['twitter'] ) : ?>
	<a target="_blank" class="twitter" href="http://twitter.com/home?status=<?=$info['title']?>%20<?=$info['url']?>"><i></i></a>
<?php endif;
if ( $display ) : ?>
</div>
<?php endif;
