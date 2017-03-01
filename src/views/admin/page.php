<?php
  namespace ellsif\WelCMS;

  $pages = $pages ?? [];
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
            <h1 class="page-header">個別ページ管理</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                個別ページ一覧
              </div>
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-6 col-sm-offset-6">
                    <div style="text-align: right;">
                      <label><button type="button" class="btn btn-primary" onclick="location.href='<?php echo Router::getUrl('/admin/page/add')?>'">新規作成</button></label>
                    </div>
                  </div>
                </div>
                <?php if (count($pages) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>名前</th>
                        <th>パス（相対URL）</th>
                        <th>テンプレート</th>
                        <th>Keyword</th>
                        <th>Description</th>
                        <th>公開</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($pages as $page) : ?>
                        <?php $_options = $page['options'] ? json_decode($page['options'], true) : [] ?>
                        <tr>
                          <td><?php echo $page['name'] ?? '' ?></td>
                          <td><?php echo $page['path'] ?? '' ?></td>
                          <td><?php echo $page['template_id'] ?? '' ?></td>
                          <td><?php echo $_options['seo']['keywords'] ?? '' ?></td>
                          <td><?php echo $_options['seo']['description'] ?? '' ?></td>
                          <td><?php echo \ellsif\WelCMS\Admin\getLabel('published', $page['published']) ?></td>
                          <td>
                            <a href="<?php echo Router::getUrl('/admin/page/edit/' . $page['id'])?>" class="btn btn-outline btn-primary btn-sm">編集</a>
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
