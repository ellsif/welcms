<?php
namespace ellsif;

/**
 * ファイルアップロードエリアを表示
 */
use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\Router;

$url = Router::getInstance();
$config = Pocket::getInstance();
$config->addVarFooterJsAfter('assets/js/dropzone.js');
?>
<form class="dropzone" action="<?php echo $url->getUrl('admin/files/add') ?>"></form>
