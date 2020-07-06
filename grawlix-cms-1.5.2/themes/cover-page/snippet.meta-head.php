<!-- GRAWLIX TEMPLATE: This comes from snippet.meta-head -->
<!-- ...You probably don't need to worry about this stuff, though. -->

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="/themes/liquid/reset.css" rel="stylesheet" type="text/css">
<?=show('meta_head')?>

<title><?=show('site_name')?> - <?=show('page_title')?></title>

<?=show('support_head')?>
<?=show('favicons')?>

<!-- show a preview of the comic page if this is a comic page -->
<? if(show('firstImageURL')): ?>
    <meta property="og:image" content="<?=show('firstImageURL')?>"/>
    <meta property="og:description" content="<?=show('meta_description')?>"/>
<? else: ?>
    <meta property="og:image" content="<?=show('directory')?>/themes/castoff/images/layout/logo.png">
<? endif; ?>