<!-- GRAWLIX TEMPLATE: This comes from snippet.follow -->
<div class="social-media-icons">


<a class="social-icon patreon-icon" href="https://www.patreon.com/AlfaFilly">
    <i class="fab fa-patreon"></i><span>Support</span>
</a>


<a class="social-icon rss" class="social-icon" href="<?=show('rss')?>"><i class="fas fa-rss"></i></a>
<?php
$info = $this->services['follow']; // Get info from database via page class
if ( $info['deviantart'] ) : ?>
<a class="social-icon" href="http://<?=$info['deviantart'] ?>.deviantart.com"><i class="fab fa-deviantart"></i></a>
<?php endif;
if ( $info['facebook'] ) : ?>
<a class="social-icon" href="https://www.facebook.com/<?=$info['facebook'] ?>"><i class="fab fa-facebook"></i></a>
<?php endif;
if ( $info['googleplus'] ) : ?>
<a class="social-icon" href="https://plus.google.com/+<?=$info['googleplus'] ?>"><i class="fab fa-google-plus-g"></i></a>
<?php endif;
if ( $info['instagram'] ) : ?>
<a class="social-icon" href="http://instagram.com/<?=$info['instagram'] ?>"><i class="fab fa-instagram"></i></a>
<?php endif;
if ( $info['linkedin'] ) : ?>
<a class="social-icon" href="http://www.linkedin.com/in/<?=$info['linkedin'] ?>"><i class="fab fa-linkedin"></i></a>
<?php endif;
if ( $info['pinterest'] ) : ?>
<a class="social-icon" href="http://www.pinterest.com/<?=$info['pinterest'] ?>/"><i class="fab fa-pintrest"></i></a>
<?php endif;
if ( $info['tumblr'] ) : ?>
<a class="social-icon" href="http://<?=$info['tumblr'] ?>.tumblr.com/"><i class="fab fa-tumblr"></i></a>
<?php endif;
if ( $info['twitter'] ) : ?>
<a class="social-icon" href="https://twitter.com/<?=$info['twitter'] ?>"><i class="fab fa-twitter"></i></a>
<?php endif; ?>
</div>
