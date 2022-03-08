<?php
$transcript = show('transcript');
if ( !empty($transcript) ) : ?>
<article role="text" id="transcript">
	<h4>Comic transcript</h4>
	<div>
		<?=$transcript?>
	</div>
</article>
<?php endif;