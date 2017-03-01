<?php
namespace ellsif;
use ellsif\WelCMS\Router;
$config = WelCMS\Config::getInstance();
$urlInfo = $config->varUrlInfo();
$url = Router::getInstance();
$templates = $templates ?? [];
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
            <h1 class="page-header">テンプレート管理</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                テンプレート一覧
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-6 col-sm-offset-6">
                    <div style="text-align: right;">
                      <label><button type="button" class="btn btn-primary" onclick="location.href='<?php echo $url->getUrl('/admin/templates/add')?>'">新規作成</button></label>
                    </div>
                  </div>
                </div>
                <?php if (count($templates) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>名前</th>
                        <th>種類</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($templates as $template) : ?>
                        <?php $_options = $template['options'] ? json_decode($template['options'], true) : [] ?>
                        <tr>
                          <td><?php echo $template['name'] ?? '' ?></td>
                          <td><?php echo $template['media_type'] ?? '' ?></td>
                          <td>
                            <a href="<?php echo $url->getUrl('/admin/templates/edit/' . $template['id'])?>" class="btn btn-outline btn-primary btn-sm">編集</a>
                            <a href="" class="btn btn-outline btn-primary btn-sm">プレビュー</a>
                            <a href="" class="btn btn-success btn-sm">記事用</a>
                            <a href="" class="btn btn-outline btn-danger btn-sm">削除</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php } else { ?>
                  <div class="alert alert-info">登録されているテンプレートがありません。</div>
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
