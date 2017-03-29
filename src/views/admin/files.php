<?php
namespace ellsif;
use ellsif\WelCMS\Router;
$config = WelCMS\Pocket::getInstance();
$url = Router::getInstance();
$files = $files ?? [];
$urlInfo = $config->varUrlInfo();
$_port = $urlInfo['port'];
if ($_port != 80) {
  $_port = ":${_port}";
} else {
  $_port = '';
}
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php include dirname(__FILE__) . '/head.php' ?>
  </head>
  <body>
    <div id="wrapper">
      <?php include dirname(__FILE__) . '/nav.php' ?>

      <div id="page-wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">ファイル管理</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                ファイルアップロード
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-12">
                    <?php includePart('FileUpload') ?>
                  </div>
                </div>
              </div>
            </div>
            <?php if (isset($files['image']) && count($files['image']) > 0): ?>
              <div class="panel panel-default">
                <div class="panel-heading">
                  画像ファイル一覧
                </div>
                <div class="panel-body">
                  <div class="row">
                    <div class="col-sm-12">
                      <?php foreach($files['image'] as $info) : ?>
                        <div class="files-image">
                          <img src="<?php echo $info['url'] ?>">
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php include dirname(__FILE__) . "/foot_js.php" ?>
  </body>
</html>
