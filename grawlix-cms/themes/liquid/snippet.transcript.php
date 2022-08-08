<!-- GRAWLIX TEMPLATE: This comes from snippet.transcript -->
<?php
$transcript = show('transcript');
if ( !empty($transcript) ) : ?>
<article role="text" id="transcript">
	<h3>Comic transcript</h3>
	<?=$transcript?>
</article>
<?php endif;