<!-- GRAWLIX TEMPLATE: This comes from snippet.twitterstream -->
<?php
$info = $this->services['follow']['twitter'];

if ( $info ) : ?>
<a class="twitter-timeline" href="https://twitter.com/<?=$info ?>">Tweets by <?=$info ?></a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
<?php endif;
