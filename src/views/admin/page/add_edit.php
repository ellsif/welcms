<?php
  namespace ellsif\WelCMS;
  $config = Pocket::getInstance();

  $id = $id ?? 0;

  $pageEntity = \ellsif\getEntity('Page');
  $contentEntity = \ellsif\getEntity('Content');
  $contents = $contentEntity->getByPageId($id);

  $page = null;
  if ($id) {
    $page = $pageEntity->get($id);
    if ($page) {
      //
      $templateEntity = \ellsif\getEntity('Template');
      $templateData = $templateEntity->get($page['template_id']);
      $templateData = json_decode($templateData['body_template'], true);
      $template = new HtmlTemplate();
      $contentNames = $template->getContentNames($templateData['dom']);
      $registedContents = $template->getPageContents($id);
      $registedContents = \ellsif\getMap($registedContents, 'name');
      $contents = [];
      foreach ($contentNames as $contentName) {
        if (array_key_exists($contentName, $registedContents)) {
          $contents[] = $registedContents[$contentName];
        } else {
          $contents[] = [
            'name' => $contentName,
            'body' => '',
          ];
        }
      }
    }
  }

  // POST結果取得（バリデーションエラー時のみ値が入る）
  $data = $config->varFormData();
  $formError = $config->varFormError();


  // Riotコンポーネントを利用
  // TODO 出来ればWebPartに置き換えたい
  $config->addRiotJs(Router::getUrl('admin/parts/ImageInput'));
  $config->addRiotJs(Router::getUrl('admin/parts/FileUpload'));
  $config->addRiotJs(Router::getUrl('admin/parts/Modal'));

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
          <?php if($id == 0) : ?>
            <div class="col-lg-12">
              <h1 class="page-header">新規ページ作成</h1>
            </div>
          <?php else : ?>
            <?php // 更新の場合のコンテンツが選択可能（テンプレートを決定しないと設定可能なコンテンツが確定しないため。 ?>
            <div class="col-lg-12">
              <h1 class="page-header">ページ更新</h1>
            </div>
            <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                  コンテンツ設定
                </div>
                <div class="panel-body">
                  <?php echo \ellsif\Form::formAlert(\ellsif\Validator::getErrorMessages($data, $formError)) ?>
                  <?php
                  $_formSettings = [];
                  foreach ($contents as $content) {
                    $_setting = [
                      'label' => $content['name'],
                      'attributes' => [
                        'value' => $content['body'],
                      ],
                      'options' => [
                        'error' => $data[$content['name']]['error'] ?? '',
                      ]
                    ];
                    if ($content['body_type'] === 'path') {  // TODO content_typeもチェックいる
                      $_setting['options']['riot'] = ['image-input', ['src' => $content['body'], 'title' => '画像を選択']];
                    }
                    $_formSettings[$content['name']] = $_setting;
                  }
                  echo \ellsif\Form::form(Router::getUrl('admin/page/contents'), $page['id'], [], $_formSettings);
                  ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                ページ情報
              </div>
              <div class="panel-body">
                <?php echo \ellsif\Form::formAlert(\ellsif\Validator::getErrorMessages($data, $formError)) ?>
                <?php
                  $_action = ($page == null) ? Router::getUrl('admin/page/add') : Router::getUrl('admin/page/edit');
                  $_id = ($page == null) ? null : $page['id'];
                  $_templates = HtmlTemplate::getTemplates(['is_parts' => 0]);
                  $_templates = ['' => '選択してください'] + \ellsif\getMap($_templates, 'id', 'name');
                  echo \ellsif\Form::form(
                    $_action, $_id, [],
                    [
                      'TemplateId' => [
                        'label' => 'テンプレート',
                        'attributes' => [
                          'value' => $data['TemplateId']['value'] ?? $page['template_id'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['TemplateId']['error'] ?? '',
                        ],
                        'validation' => [
                          'rule' => 'required',
                          'msg' => 'テンプレート : 選択必須です。',
                        ],
                        'select' => $_templates,
                      ],
                      'Name' => [
                        'label' => 'ページタイトル',
                        'attributes' => [
                          'value' => $data['Name']['value'] ?? $page['name'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['Name']['error'] ?? '',
                        ],
                        'validation' => [
                          'rule' => 'required',
                          'msg' => 'ページタイトル : 必須入力です。'
                        ],
                      ],
                      'Path' => [
                        'label' => 'ページURL',
                        'attributes' => [
                          'value' => $data['Path']['value'] ?? $page['path'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['Path']['error'] ?? '',
                        ],
                        'validation' => [
                          'rule' => 'required',
                          'msg' => 'ページURL : 必須入力です。'
                        ],
                      ],
                      'Published' => [
                        'label' => '公開',
                        'attributes' => [
                          'value' => $data['Published']['value'] ?? $page['published'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['Published']['error'] ?? '',
                        ],
                        'select' => [
                          '0' => '非公開',
                          '1' => '公開',
                        ]
                      ],
                      'UserIds' => [
                        'label' => '公開ユーザー',
                        'attributes' => [
                          'value' => $data['UserIds']['value'] ?? $page['UserIds'] ?? '',
                          'type' => 'hidden',
                          'data-part' => 'user-select',
                        ],
                        'options' => [
                          'error' => $data['UserIds']['error'] ?? '',
                          'part' => 'UserSelect',
                        ],
                      ],
                      'Options[seo][keywords]' => [
                        'label' => 'keywords',
                        'attributes' => [
                          'placeholder' => 'カンマ区切り（任意入力）',
                          'value' => $data['Options[seo][keywords]']['value'] ??  $page['options']['seo']['keywords'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['Options[seo][keywords]']['error'] ?? '',
                        ],
                      ],
                      'Options[seo][description]' => [
                        'label' => 'description',
                        'attributes' => [
                          'placeholder' => '（任意入力）',
                          'value' => $data['Options[seo][description]']['value'] ?? $page['options']['seo']['description'] ?? '',
                        ],
                        'options' => [
                          'error' => $data['Options[seo][description]']['error'] ?? '',
                        ],
                      ],
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
