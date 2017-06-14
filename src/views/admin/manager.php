<?php
namespace ellsif\WelCMS;
$managers = $managers ?? [];
$manager = $manager ?? [];
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
          <?php ViewUtil::printErrors($errors ?? []); ?>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-body">
                <form class="mb-3" id="validateForm" action="<?php echo WelUtil::getUrl('/admin/manager') ?>" method="post">
                  <div class="form-group">
                    <label class="control-label">ログインID <span class="text-danger">*</span></label>
                    <input type="text" value="<?php echo isset($manager['managerId']) ? $manager['managerId'] : '' ?>" class="form-control" name="Manager[managerId]" placeholder="半角英数">
                  </div>
                  <div class="form-group">
                    <label class="control-label">名前 <span class="text-danger">*</span></label>
                    <input type="text" value="<?php echo isset($manager['name']) ? $manager['name'] : '' ?>" class="form-control" name="Manager[name]" placeholder="表示名">
                  </div>
                  <div class="form-group">
                    <label class="control-label">Eメール <span class="text-danger">*</span></label>
                    <input class="form-control" name="Manager[email]" type="text" value="<?php echo isset($manager['email']) ? $manager['email'] : '' ?>">
                  </div>
                  <div class="form-group">
                    <label class="control-label">パスワード <span class="text-danger">*</span></label>
                    <input type="text" value="<?php echo isset($manager['password']) ? $manager['password'] : '' ?>" class="form-control js-password" name="Manager[password]">
                  </div>
                  <input type="submit" class="btn btn-lg btn-primary btn-submit" value="登録">
                </form>
                <?php if (count($managers)): ?>
                  <table class="table table-bordered">
                    <tr>
                      <th>ログインID</th><th>Eメール</th><th>名前</th>
                    </tr>
                    <?php foreach($managers as $manager): ?>
                      <tr>
                        <td><?php echo $manager['managerId'] ?></td><td><?php echo $manager['email'] ?></td><td><?php echo $manager['name'] ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </table>
                <?php else: ?>
                  <p>管理者が登録されていません。</p>
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
