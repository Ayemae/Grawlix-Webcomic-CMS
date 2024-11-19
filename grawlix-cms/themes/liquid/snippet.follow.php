<!-- GRAWLIX TEMPLATE: This comes from snippet.follow -->
<div class="social-media-icons">

<a class="social-icon rss" class="social-icon" href="<?=show('rss')?>">RSS</a>
<?php
$info = $this->services['follow']; // Get info from database via page class
if ( $info['deviantart'] ?? null ) : ?>
<a class="social-icon" href="http://<?=$info['deviantart'] ?>.deviantart.com">DeviantART</a>
<?php endif;
if ( $info['facebook'] ?? null ) : ?>
<a class="social-icon" href="https://www.facebook.com/<?=$info['facebook'] ?>">Facebook</a>
<?php endif;
if ( $info['instagram'] ?? null ) : ?>
<a class="social-icon" href="http://instagram.com/<?=$info['instagram'] ?>">Instagram</a>
<?php endif;
if ( $info['linkedin'] ?? null ) : ?>
<a class="social-icon" href="http://www.linkedin.com/in/<?=$info['linkedin'] ?>">LinkedIn</a>
<?php endif;
if ( $info['pinterest'] ?? null ) : ?>
<a class="social-icon" href="http://www.pinterest.com/<?=$info['pinterest'] ?>/">Pintrest</a>
<?php endif;
if ( $info['tumblr'] ?? null ) : ?>
<a class="social-icon" href="http://<?=$info['tumblr'] ?>.tumblr.com/">Tumblr</a>
<?php endif;
if ( $info['twitter'] ?? null ) : ?>
<a class="social-icon" href="https://twitter.com/<?=$info['twitter'] ?>">X/Twitter</i></a>
<?php endif; ?>
</div>
