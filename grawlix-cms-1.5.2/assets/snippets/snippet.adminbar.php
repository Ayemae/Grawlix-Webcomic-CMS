<?php
$info = $this->grlxbar;
if ( $info ) : ?>
	<div id="special">
		<a class="login" href="<?=$info['panel_link']?>"><?=$info['panel_text']?></a>
		<a class="logo" href="http://www.getgrawlix.com"><img src="<?=$info['img']?>" /></a>
		<a class="edit" href="<?=$info['edit_link']?>"><?=$info['edit_text']?></a>
	</div>
<?php endif;
