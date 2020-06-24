<!-- GRAWLIX TEMPLATE: This comes from snippet.follow -->
<h3>Follow me</h3>
<div class="share-icons">
<a class="social-icon rss" class="social-icon" href="<?=show('rss')?>"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-rss.svg" alt="RSS"></a>
<?php
$info = $this->services['follow']; // Get info from database via page class
if ( $info['patreon'] ) : ?>
<a class="social-icon" href="http://www.patreon.com/<?=$info['patreon'] ?>"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-patreon.svg" alt="Patreon"></a>
<?php endif;
if ( $info['deviantart'] ) : ?>
<a class="social-icon" href="http://<?=$info['deviantart'] ?>.deviantart.com"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-deviantart.svg" alt="deviantArt"></a>
<?php endif;
if ( $info['facebook'] ) : ?>
<a class="social-icon" href="https://www.facebook.com/<?=$info['facebook'] ?>"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-facebook.svg" alt="Facebook"></a>
<?php endif;
if ( $info['googleplus'] ) : ?>
<a class="social-icon" href="https://plus.google.com/+<?=$info['googleplus'] ?>"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-google-plus.svg" alt="Google Plus"></a>
<?php endif;
if ( $info['instagram'] ) : ?>
<a class="social-icon" href="http://instagram.com/<?=$info['instagram'] ?>"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-instagram.svg" alt="Instagram"></a>
<?php endif;
if ( $info['linkedin'] ) : ?>
<a class="social-icon" href="http://www.linkedin.com/in/<?=$info['linkedin'] ?>"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-linkedin.svg" alt="LinkedIn"></a>
<?php endif;
if ( $info['pinterest'] ) : ?>
<a class="social-icon" href="http://www.pinterest.com/<?=$info['pinterest'] ?>/"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-pinterest.svg" alt="Pinterest"></a>
<?php endif;
if ( $info['tumblr'] ) : ?>
<a class="social-icon" href="http://<?=$info['tumblr'] ?>.tumblr.com/"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-tumblr.svg" alt="Tumblr"></a>
<?php endif;
if ( $info['twitter'] ) : ?>
<a class="social-icon" href="https://twitter.com/<?=$info['twitter'] ?>"><img src="<?=show('directory') ?>/themes/indotherm/images/icon-twitter.svg" alt="Twitter"></a>
<?php endif; ?>
</div>
