<?php
$info = $this->grlxbar ?? null;
if ( $info ) : ?>
	<div id="special">
		<a class="login" href="<?=($info['panel_link'] ?? null)?>"><?=($info['panel_text'] ?? null)?></a>
		<a class="logo" href="http://www.getgrawlix.com"><img src="<?=($info['img'] ?? null)?>" /></a>
		<a class="edit" href="<?=($info['edit_link'] ?? null)?>"><?=($info['edit_text'] ?? null)?></a>
	</div>
<?php endif;
