<?php
  namespace ellsif\WelCMS;

  $table = $table ?? [];
  $columns = $columns ?? [];
  $data = $data ?? [];
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php include dirname(__FILE__, 2) . '/head.php' ?>
  </head>
  <body class="database">
    <div id="wrapper">
      <?php include dirname(__FILE__, 2) . '/nav.php' ?>

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
                <?php echo $table ?>
              </div>
              <div class="panel-body">
                <table width="100%" class="table table-striped table-bordered table-hover">
                  <thead>
                  <tr>
                    <?php foreach($columns as $column): ?>
                      <th><?php echo $column->name ?></th>
                    <?php endforeach; ?>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach($data as $row) : ?>
                    <tr>
                      <?php foreach($columns as $column): ?>
                        <?php
                          $data = $row[$column->name] ?? '';
                          $width = 80;
                          if (mb_strlen($data) > 200) {
                            $width = 200;
                          }
                        ?>
                        <td style="min-width: <?php echo $width ?>px;"><div style="white-space: pre-wrap;"><?php echo htmlspecialchars($data) ?></div></td>
                      <?php endforeach; ?>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include dirname(__FILE__, 2) . "/foot_js.php" ?>
  </body>
</html>
