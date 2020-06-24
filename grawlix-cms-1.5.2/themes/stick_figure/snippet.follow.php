<!-- GRAWLIX TEMPLATE: This comes from snippet.follow -->
<h3>Follow me</h3>
<div class="share-icons">
<a class="social-icon rss" class="social-icon" href="<?=show('rss')?>">RSS</a>
<?php
$info = $this->services['follow']; // Get info from database via page class
if ( $info['patreon'] ) : ?>
<a class="social-icon" href="http://www.patreon.com/<?=$info['patreon'] ?>">Patreon</a>
<?php endif;
if ( $info['deviantart'] ) : ?>
<a class="social-icon" href="http://<?=$info['deviantart'] ?>.deviantart.com">deviantART</a>
<?php endif;
if ( $info['facebook'] ) : ?>
<a class="social-icon" href="https://www.facebook.com/<?=$info['facebook'] ?>">Facebook</a>
<?php endif;
if ( $info['googleplus'] ) : ?>
<a class="social-icon" href="https://plus.google.com/+<?=$info['googleplus'] ?>">Google+</a>
<?php endif;
if ( $info['instagram'] ) : ?>
<a class="social-icon" href="http://instagram.com/<?=$info['instagram'] ?>">Instagram</a>
<?php endif;
if ( $info['linkedin'] ) : ?>
<a class="social-icon" href="http://www.linkedin.com/in/<?=$info['linkedin'] ?>">LinkedIn</a>
<?php endif;
if ( $info['pinterest'] ) : ?>
<a class="social-icon" href="http://www.pinterest.com/<?=$info['pinterest'] ?>/">Pinterest</a>
<?php endif;
if ( $info['tumblr'] ) : ?>
<a class="social-icon" href="http://<?=$info['tumblr'] ?>.tumblr.com/">Tumblr</a>
<?php endif;
if ( $info['twitter'] ) : ?>
<a class="social-icon" href="https://twitter.com/<?=$info['twitter'] ?>">Twitter</a>
<?php endif; ?>
</div>
