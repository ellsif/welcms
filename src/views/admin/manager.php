<?php
namespace ellsif\WelCMS;
$managers = $managers ?? [];
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php WelUtil::loadView(Router::getViewPath('admin/head.php')) ?>
  </head>
  <body>
    <div id="wrapper">
      <?php WelUtil::loadView(Router::getViewPath('admin/nav.php')) ?>
      <div id="page-wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">管理者アカウント</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-body">
                <?php if (count($managers)): ?>
                    <table class="table table-bordered">
                      <tr>
                        <th>ログインID</th><th>名前</th>
                      </tr>
                      <?php foreach($managers as $manager): ?>
                        <tr>
                          <td><?php echo $manager['managerId'] ?></td><td><?php echo $manager['name'] ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </table>
                <?php else: ?>
                  <p>ダッシュボードです。</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php WelUtil::loadView(Router::getViewPath('admin/foot_js.php')) ?>
  </body>
</html>
