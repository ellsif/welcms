<?php
namespace ellsif;
use ellsif\WelCMS\Form;
use ellsif\WelCMS\Validator;
use ellsif\WelCMS\Pocket;
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
            <h1 class="page-header"><?php echo Pocket::getInstance()->settingSiteName()?> 管理画面ログイン</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-8 col-lg-offset-2">
            <div class="panel panel-default">
              <div class="panel-body">
                <?php
                  echo Form::formAlert(Validator::getErrorMessages($data, $formError));
                ?>
                <?php
                  echo Form::formStart(
                    '/manager/login', [],
                    [
                      'managerId' => ['rule' => 'required', 'msg' => 'ログインID : 必須入力です。'],
                      'password' => [
                        ['rule' => 'required', 'msg' => 'パスワード : 必須入力です。'],
                      ],
                    ]
                  );
                ?>
                  <?php
                    // $port = intval($urlInfo['port']) !== 80 ? ':'.$urlInfo['port'] : '';
                    echo Form::formInput(
                      'ログインID',
                      'managerId',
                      [
                        'placeholder' => 'admin@example.com',
                        'value' => $data['managerId']['value'] ?? '',
                        'error' => $data['managerId']['error'] ?? '',
                      ]
                    );
                    echo Form::formInput(
                      'パスワード',
                      'password',
                      [
                        'type' => 'password',
                        'value' => $data['password']['value'] ?? '',
                        'error' => $data['password']['error'] ?? '',
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
