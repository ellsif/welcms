<?php
namespace ellsif;
use ellsif\WelCMS\Router;
$config = WelCMS\Pocket::getInstance();
$urlInfo = $config->varUrlInfo();
$url = Router::getInstance();
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
        <h1 class="page-header">顧客情報管理</h1>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            検索条件入力
          </div>
          <div class="panel-body">

            <div class="form-group">
              <label>住所</label>
              <input type="text" value="春日井" class="form-control">
            </div>
            <div class="form-group">
              <label>応募回数</label>
              <select class="form-control" style="">
                <option>3回以上
              </select>
            </div>
            <label><button type="button" class="btn btn-primary" onclick="location.href='<?php echo $url->getUrl('/admin/templates/add')?>'">検索</button></label>

          </div>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            検索結果一覧
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-sm-6 col-sm-offset-6">
                <div style="text-align: right;">
                  <test-modal></test-modal><script>riot.mount('test-modal')</script>
                  <label><button type="button" class="btn btn-primary" onclick="location.href='<?php echo $url->getUrl('/admin/templates/add')?>'">メルマガ配信</button></label>
                  <label><button type="button" class="btn btn-primary" onclick="location.href='<?php echo $url->getUrl('/admin/templates/add')?>'">CSV出力</button></label>
                </div>
              </div>
            </div>

            <table width="100%" class="table table-striped table-bordered table-hover">
              <thead>
              <tr>
                <th>氏名</th>
                <th>住所</th>
                <th>電話</th>
                <th>Eメール</th>
                <th>応募回数</th>
                <th>当選回数</th>
                <th>引渡回数</th>
              </tr>
              </thead>
              <tbody>
                <tr>
                  <td>ヤマダ　タロウ</td>
                  <td>春日井市○○町1-2-3</td>
                  <td>000-000-1111</td>
                  <td>test@example.com</td>
                  <td>3</td>
                  <td>1</td>
                  <td>1</td>
                </tr>
                <tr>
                  <td>ヤマダ　タロウ</td>
                  <td>春日井市○○町1-2-3</td>
                  <td>000-000-1111</td>
                  <td>test@example.com</td>
                  <td>3</td>
                  <td>0</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>ヤマダ　タロウ</td>
                  <td>春日井市○○町1-2-3</td>
                  <td>000-000-1111</td>
                  <td>test@example.com</td>
                  <td>3</td>
                  <td>0</td>
                  <td>0</td>
                </tr>
                <tr>
                  <td>ヤマダ　タロウ</td>
                  <td>春日井市○○町1-2-3</td>
                  <td>000-000-1111</td>
                  <td>test@example.com</td>
                  <td>3</td>
                  <td>1</td>
                  <td>0</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include dirname(__FILE__) . "/foot_js.php" ?>
</body>
</html>
