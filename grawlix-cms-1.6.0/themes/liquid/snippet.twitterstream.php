<!-- GRAWLIX TEMPLATE: This comes from snippet.twitterstream -->
<?php
$info = $this->services['follow']['twitter'];

if ( !empty($info) ) : ?>
<div class="twitterstream desktop-only">
<h6>Twitter Timeline</h6>
<a class="twitter-timeline" 
data-height="750" 
data-theme="dark"
data-chrome="transparent%20noheader"
href="https://twitter.com/<?=$info ?>">Tweets by <?=$info ?></a> 
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
</div>
<?php endif;