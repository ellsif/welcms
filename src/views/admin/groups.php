<?php
namespace ellsif;
use ellsif\WelCMS\Router;
$config = WelCMS\Pocket::getInstance();
$urlInfo = $config->varUrlInfo();
$url = Router::getInstance();
$groups = $groups ?? [];
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
            <h1 class="page-header">ユーザーグループ管理</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                ユーザーグループ一覧
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-6 col-sm-offset-6">
                    <div style="text-align: right;">
                      <label><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#groupModal">新規作成</button></label>
                    </div>
                    <div class="modal fade" id="groupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form action="<?php echo $url->getUrl('/admin/groups/regist')?>">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                              <h4 class="modal-title" id="myModalLabel">ユーザーグループ</h4>
                            </div>
                            <div class="modal-body">
                              <div class="form-group">
                                <label>グループ名</label>
                                <input class="form-control" name="name">
                              </div>
                              <div class="form-group">
                                <label>所属ユーザー</label>
                                <?php includePart('UserSelect') ?>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button class="btn btn-default" data-dismiss="modal">閉じる</button>
                              <button class="btn btn-primary js-submit" data-success="groupCallback">適用</button>
                              <script>
                                var groupCallback = function($elem, data){
                                  // モーダルを消し、一覧を更新する。
                                  console.log('TODO モーダルを消し、一覧を更新する。');
                                }
                              </script>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php if (count($groups) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>グループ名前</th>
                        <th>所属ユーザー</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($groups as $group) : ?>
                        <tr>
                          <td><?php echo $group['name'] ?? '' ?></td>
                          <td><?php echo implode(', ', $group['userLoginIds'] ?? []) ?></td>
                          <td>
                            <a href="<?php echo $url->getUrl('/admin/pages/edit/' . $group['id'])?>" class="btn btn-outline btn-primary btn-sm">編集</a>
                            <a href="#" class="btn btn-outline btn-danger btn-sm">削除</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php } else { ?>
                  <div class="alert alert-info">登録されているグループがありません。</div>
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
