<?php
namespace ellsif;
use ellsif\WelCMS\Form;
use ellsif\WelCMS\Validator;
use ellsif\WelCMS\Pocket;use ellsif\WelCMS\ViewUtil;
$config = WelCMS\Pocket::getInstance();
$data = $config->varFormData();
$formError = $config->varFormError();
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php include dirname(__FILE__) . '/head.php' ?>
  </head>
  <body>
    <div id="wrapper">
      <div id="page-wrapper" style="margin:0">
        <div class="row">
          <div class="col-lg-8 col-lg-offset-2">
            <h1 class="page-header"><?php echo Pocket::getInstance()->settingSiteName()?> システム管理画面ログイン</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-8 col-lg-offset-2">
            <?php ViewUtil::printFlash() ?>
            <?php ViewUtil::printErrors($errors ?? []) ?>
            <div class="panel panel-default">
              <div class="panel-body">
                <?php
                  echo Form::formAlert(Validator::getErrorMessages($data, $formError));
                ?>
                <?php
                  echo Form::formStart(
                    '/admin/login', [],
                    [
                      'adminID' => ['rule' => 'required', 'msg' => '管理者ID : 必須入力です。'],
                      'adminPass' => [
                        ['rule' => 'required', 'msg' => '管理者パスワード : 必須入力です。'],
                      ],
                    ]
                  );
                ?>
                  <?php
                    // $port = intval($urlInfo['port']) !== 80 ? ':'.$urlInfo['port'] : '';
                    echo Form::formInput(
                      '管理者ID',
                      'adminID',
                      [
                        'placeholder' => 'admin@example.com',
                        'value' => $data['adminID']['value'] ?? '',
                        'error' => $data['adminID']['error'] ?? '',
                      ]
                    );
                    echo Form::formInput(
                      '管理者パスワード',
                      'adminPass',
                      [
                        'type' => 'password',
                        'value' => $data['adminPass']['value'] ?? '',
                        'error' => $data['adminPass']['error'] ?? '',
                      ]
                    );
                  ?>
                  <button type="submit" class="btn btn-default">ログイン</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include dirname(__FILE__) . "/foot_js.php" ?>
  </body>
</html>
