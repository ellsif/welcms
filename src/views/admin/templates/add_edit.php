<?php
namespace ellsif;
use ellsif\WelCMS\HtmlTemplate;$config = WelCMS\Config::getInstance();
$url = WelCMS\Router::getInstance();
$template = $template ?? null;
$data = $config->varFormData();
$formError = $config->varFormError();
$template = $template ?? null;
$urlInfo = $config->varUrlInfo();
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php include $config->dirView() . 'admin/head.php' ?>
  </head>
  <body>
    <div id="wrapper">
      <?php include $config->dirView() . 'admin/nav.php' ?>

      <div id="page-wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">
              <?php if($template == null) { ?>
                新規テンプレート登録
              <?php } else { ?>
                テンプレート更新
              <?php } ?>
            </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                テンプレート一覧
              </div>
              <div class="panel-body">
                <?php echo Form::formAlert(Validator::getErrorMessages($data, $formError)) ?>
                <?php
                  $_action = ($template == null) ? $url->getUrl('admin/templates/add') : $url->getUrl('admin/templates/edit');
                  $_id = ($template == null) ? null : $template['id'];
                  echo Form::form(
                    $_action, $_id, [],
                    [
                      'Name' => [
                        'label' => 'テンプレート名',
                        'attributes' => [
                          'value' => $data['Name']['value'] ?? $template['name'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['Name']['error'] ?? '',
                        ],
                        'validation' => [
                          'rule' => 'required',
                          'msg' => 'テンプレート名 : 入力必須です。',
                        ],
                      ],
                      'IsParts' => [
                        'label' => 'テンプレート種別',
                        'attributes' => [
                          'value' => $data['IsParts']['value'] ?? $template['is_part'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['IsParts']['error'] ?? '',
                        ],
                        'select' => [
                          '0' => 'ページ',
                          '1' => '部品'
                        ],
                      ],
                      'MediaType' => [
                        'label' => 'メディアタイプ',
                        'attributes' => [
                          'value' => $data['MediaType']['value'] ?? $template['media_type'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['MediaType']['error'] ?? '',
                        ],
                        'select' => [
                          'text/html' => 'text/html',
                        ],
                      ],
                      'Body' => [
                        'label' => 'テンプレート本文',
                        'options' => [
                          'input' => [
                            'type' => 'textarea',
                            'rows' => 15,
                            'value' => $data['Body']['value'] ?? $template['body'] ?? '',
                          ],
                          'error' => $data['Body']['error'] ?? '',
                        ],
                      ]
                    ]
                  );
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include $config->dirView() . 'admin/foot_js.php' ?>
  </body>
</html>
