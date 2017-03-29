<?php
namespace ellsif\WelCMS;

  $tables = $tables ?? [];
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
            <h1 class="page-header">データベース管理</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                テーブル一覧
              </div>
              <div class="panel-body">
                <?php if (count($tables) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>テーブル名</th>
                        <th>データ件数</th>
                        <th>区分</th>
                        <th>説明</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($tables as $table => $data) : ?>
                        <tr>
                          <td><a href="<?php echo Router::getUrl('/admin/database/'.$table) ?>"><?php echo $table ?></td>
                          <td><?php echo $data['count'] ?></td>
                          <td><?php echo $data['type'] ?></td>
                          <td><?php echo $data['description'] ?></td>
                          <td>
                            <a target="_blank" href="<?php echo Router::getUrl("/admin/database/${table}.csv") ?>" class="btn btn-outline btn-primary btn-sm">CSV出力</a>
                            <a href="#" class="btn btn-outline btn-danger btn-sm">削除</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php } else { ?>
                  <div class="alert alert-info">登録されているページがありません。</div>
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
