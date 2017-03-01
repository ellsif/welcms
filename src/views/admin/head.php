<?php
$config = \ellsif\WelCMS\Config::getInstance();
$urlInfo = $config->varUrlInfo();
?><meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<title>WelCMS初期設定画面</title>

<?php foreach($config->varCssBefore() as $css): ?>
  <link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/<?php echo $css ?>" rel="stylesheet">
<?php endforeach; ?>


<link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/theme/admin/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/theme/admin/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
<link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/theme/admin/css/sb-admin-2.min.css" rel="stylesheet">
<link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/theme/admin/vendor/morrisjs/morris.css" rel="stylesheet">
<link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/theme/admin/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/theme/admin/css/dropzone.css" rel="stylesheet">
<link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/theme/admin/css/appendAdmin.css" rel="stylesheet">

<?php foreach($config->varCssAfter() as $css): ?>
  <link href="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/<?php echo $css ?>" rel="stylesheet">
<?php endforeach; ?>


<!-- Riot.js -->
<script src="//<?php echo $urlInfo['host'] ?>:<?php echo $urlInfo['port'] ?>/welcms/assets/vendor/riot+compiler.min.js"></script>
<?php foreach($config->varRiotJs() as $js): ?>
  <script src="<?php echo $js ?>" type="riot/tag"></script>
<?php endforeach; ?>
