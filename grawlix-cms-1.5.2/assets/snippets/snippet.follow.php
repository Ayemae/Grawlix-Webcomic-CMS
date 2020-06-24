<?php
$info = $this->services['follow']; // Get info from database via page class
if ( $info['deviantart'] ) : ?>
	<a class="deviantart" title="Follow me on deviantART" href="http://<?=$info['deviantart'] ?>.deviantart.com"><i></i></a>
<?php endif;
if ( $info['facebook'] ) : ?>
	<a class="facebook" title="Follow me on Facebook" href="https://www.facebook.com/<?=$info['facebook'] ?>"><i></i></a>
<?php endif;
if ( $info['googleplus'] ) : ?>
	<a class="googleplus" title="Follow me on Google Plus" href="https://plus.google.com/+<?=$info['googleplus'] ?>"><i></i></a>
<?php endif;
if ( $info['instagram'] ) : ?>
	<a class="instagram" title="Follow me on Instagram" href="http://instagram.com/<?=$info['instagram'] ?>"><i></i></a>
<?php endif;
if ( $info['linkedin'] ) : ?>
	<a class="linkedin" title="Follow me on LinkedIn" href="http://www.linkedin.com/in/<?=$info['linkedin'] ?>"><i></i></a>
<?php endif;
if ( $info['pinterest'] ) : ?>
	<a class="pinterest" title="Follow me on Pinterest" href="http://www.pinterest.com/<?=$info['pinterest'] ?>/"><i></i></a>
<?php endif;
if ( $info['tumblr'] ) : ?>
	<a class="tumblr" title="Follow me on Tumblr" href="http://<?=$info['tumblr'] ?>.tumblr.com/"><i></i></a>
<?php endif;
if ( $info['twitter'] ) : ?>
	<a class="twitter" title="Follow me on Twitter" href="https://twitter.com/<?=$info['twitter'] ?>"><i></i></a>
<?php endif;
