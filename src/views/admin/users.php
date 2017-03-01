<?php
namespace ellsif;
use ellsif\WelCMS\Router;
$config = WelCMS\Config::getInstance();
$urlInfo = $config->varUrlInfo();
$url = Router::getInstance();
$users = $users ?? [];
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
            <h1 class="page-header">ユーザー管理</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                ユーザー一覧
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-6 col-sm-offset-6">
                    <div style="text-align: right;">
                      <label><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal">新規作成</button></label>
                    </div>
                    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form action="<?php echo $url->getUrl('/admin/users/regist')?>">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                              <h4 class="modal-title" id="userModalLabel">ユーザー情報</h4>
                            </div>
                            <div class="modal-body">
                              <div class="form-group">
                                <label>ユーザー名</label>
                                <input class="form-control" name="name">
                              </div>
                              <div class="form-group">
                                <label>ユーザーID</label>
                                <input class="form-control" name="userId">
                              </div>
                              <div class="form-group">
                                <label>Eメール</label>
                                <input class="form-control" name="email">
                              </div>
                              <div class="form-group">
                                <label>パスワード</label>
                                <input class="form-control" name="password">
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button class="btn btn-default" data-dismiss="modal">閉じる</button>
                              <button class="btn btn-primary js-submit" data-success="userCallback">適用</button>
                              <script>
                                var userCallback = function($elem, data){
                                  // モーダルを消し、一覧を更新する。
                                  console.log('TODO モーダルを消し、一覧を更新する。');
                                };
                              </script>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php if (count($users) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ユーザーID</th>
                        <th>ユーザー名</th>
                        <th>Eメール</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($users as $user) : ?>
                        <tr>
                          <td><?php echo $user['userId'] ?? '' ?></td>
                          <td><?php echo $user['name'] ?? '' ?></td>
                          <td><?php echo $user['email'] ?? '' ?></td>
                          <td>
                            <a href="#" class="btn btn-outline btn-primary btn-sm">編集</a>
                            <a href="#" class="btn btn-outline btn-danger btn-sm">削除</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php } else { ?>
                  <div class="alert alert-info">登録されているユーザーがありません。</div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include dirname(__FILE__) . "/foot_js.php" ?>
  </body>
</html>
