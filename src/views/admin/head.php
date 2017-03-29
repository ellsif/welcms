<?php
namespace ellsif\WelCMS;
$pocket = Pocket::getInstance();
$urlInfo = $pocket->varUrlInfo();
$urlBase = $urlInfo['host'];
if (intval($urlInfo['port']) != 80) {
    $urlBase .= ':' . $urlInfo['port'];
}
?><meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<title><?php echo $pocket->varPageTitle(); ?></title>

<?php foreach($pocket->varCssBefore() as $css): ?>
  <link href="//<?php echo $urlBase ?>/<?php echo ltrim($css, '/') ?>" rel="stylesheet">
<?php endforeach; ?>

<link href="//<?php echo $urlBase ?>/asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="//<?php echo $urlBase ?>/asset/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
<link href="//<?php echo $urlBase ?>/asset/vendor/sb-admin-2/sb-admin-2.min.css" rel="stylesheet">
<link href="//<?php echo $urlBase ?>/asset/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="//<?php echo $urlBase ?>/asset/admin/css/appendAdmin.css" rel="stylesheet">

<?php foreach($pocket->varCssAfter() as $css): ?>
  <link href="//<?php echo $urlBase ?>/<?php echo ltrim($css, '/') ?>" rel="stylesheet">
<?php endforeach; ?>
