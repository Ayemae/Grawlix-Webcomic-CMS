<!-- GRAWLIX TEMPLATE: This comes from snippet.transcript -->
<?php
$transcript = show('transcript');
if ( $transcript ) : ?>
<article role="text" id="transcript">
	<h3>Comic transcript</h3>
	<?=$transcript?>
</article>
<?php endif;