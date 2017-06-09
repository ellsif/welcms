<?php
namespace ellsif;
use ellsif\WelCMS\Router;$config = WelCMS\Pocket::getInstance();
$urlInfo = $config->varUrlInfo();
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php Router::getViewPath('admin/head.php') ?>
  </head>
  <body>
    <div id="wrapper">
      <?php Router::getViewPath('admin/nav.php') ?>

      <div id="page-wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">DashBoard</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-body">
                <p>ダッシュボードです。</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php Router::getViewPath('admin/foot_js.php') ?>
  </body>
</html>
