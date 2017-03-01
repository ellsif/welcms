<?php
namespace ellsif;

/**
 * ファイルアップロードエリアを表示
 */
use ellsif\WelCMS\Config;
use ellsif\WelCMS\Router;

$url = Router::getInstance();
$config = Config::getInstance();
$config->addVarFooterJsAfter('assets/js/dropzone.js');
?>
<form class="dropzone" action="<?php echo $url->getUrl('admin/files/add') ?>"></form>
