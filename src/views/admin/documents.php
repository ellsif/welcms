<?php
  namespace ellsif\WelCMS;

  // 表示用データ取得
  $config = Pocket::getInstance();
  $files = [];
  $paths = \ellsif\getFileList([
    $config->dirSystem() . 'classes/core',
    $config->dirSystem() . 'classes/entity',
    $config->dirSystem() . 'classes/parts',
    $config->dirSystem() . 'functions',
  ]);

  $doc = new \ellsif\Document();
  foreach ($paths as $path) {
    try {
      $docData = $doc->getData($path);
    } catch (\Throwable $e) {
      // 何らかの理由でパースに失敗した
      $docData = ['functions' => [['function' => 'error!']]];
    }
    $files[] = [
      'path' => preg_replace('!^'.$config->dirSystem() . '!', '', $path),
      'data' => $docData,
    ];
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
            <h1 class="page-header">関数リファレンス</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                ファイル一覧
              </div>
              <div class="panel-body">
                <?php if (count($files) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <tbody>
                      <?php foreach($files as $file) : ?>
                        <tr>
                          <td><a href="<?php echo Router::getUrl('admin/documents/' . $file['path'])?>"><?php echo $file['path'] ?? '' ?></a></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                関数一覧
              </div>
              <div class="panel-body">
                <?php if (count($files) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>関数名</th>
                        <th>ファイル</th>
                        <th>概要</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php foreach($files as $file) { ?>
                      <?php if (!is_array($file['data']['functions'])) { var_dump($file['data']); } ?>
                      <?php foreach($file['data']['functions'] as $function) { ?>
                        <tr>
                          <td><?php echo $function['function'] ?? '' ?></td>
                          <td><?php echo $file['path'] ?? '' ?></td>
                          <td><?php echo $function['description'] ?? '' ?></td>
                        </tr>
                      <?php } ?>
                    <?php } ?>
                    </tbody>
                  </table>
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
