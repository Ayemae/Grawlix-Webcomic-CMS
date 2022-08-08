<!-- GRAWLIX TEMPLATE: This comes from snippet.transcript -->
<?php
$transcript = show('transcript');
<<<<<<< HEAD
if ( !empty($transcript) ) : ?>
=======
if ( $transcript ) : ?>
>>>>>>> ab7b285a732339fb9df9fdba1161eeee8c268d8b
<article role="text" id="transcript">
	<h3>Comic transcript</h3>
	<?=$transcript?>
</article>
<?php endif;