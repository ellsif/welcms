<!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php include dirname(__FILE__) . '/head.php' ?>
  </head>
  <body>
    <div id="wrapper">
      <div id="page-wrapper" style="margin:0">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">WelCMS初期設定</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-body">
                <?php
                  echo \ellsif\formAlert(\ellsif\Validator::getErrorMessages($data));
                ?>
                <?php
                  echo \ellsif\formStart(
                    '/welcms/activate', [],
                    [
                      'UrlHome' => ['rule' => 'required', 'msg' => 'サイトURL : 必須入力です。'],
                      'SiteName' => ['rule' => 'required', 'msg' => 'サイト名 : 必須入力です。'],
                      'AdminID' => ['rule' => 'required', 'msg' => '管理者ID : 必須入力です。'],
                      'AdminPass' => [
                        ['rule' => 'required', 'msg' => '管理者パスワード : 必須入力です。'],
                        ['rule' => 'length', 'args' => [12, 4], 'msg' => '管理者パスワード : 4文字以上、12文字以内で入力してください。']
                      ],
                    ]
                  );
                ?>
                  <input type="hidden" name="Activated" value="1">
                  <?php
                    $port = intval($urlInfo['port']) !== 80 ? ':'.$urlInfo['port'] : '';
                    echo \ellsif\formInput(
                      'サイトURL',
                      'UrlHome',
                      [
                        'value' => $data['UrlHome'][1] ?? $urlInfo['scheme'] . '://' . $urlInfo['host'] . $port . '/',
                        'placeholder' => 'https://example.com/',
                        'error' => $data['UrlHome'][2] ?? '',
                      ]
                    );
                    echo \ellsif\formInput(
                      'サイト名',
                      'SiteName',
                      [
                        'placeholder' => 'WelCMS',
                        'value' => $data['SiteName'][1] ?? '',
                        'error' => $data['SiteName'][2] ?? '',
                      ]
                    );
                    echo \ellsif\formInput(
                      '管理者ID',
                      'AdminID',
                      [
                        'placeholder' => 'admin@example.com',
                        'help' => 'メールアドレス以外も設定可能です。',
                        'value' => $data['AdminID'][1] ?? '',
                        'error' => $data['AdminID'][2] ?? '',
                      ]
                    );
                    echo \ellsif\formInput(
                      '管理者パスワード',
                      'AdminPass',
                      [
                        'type' => 'password',
                        'help' => '4文字以上の半角英数記号のみ設定可能です。',
                        'value' => $data['AdminPass'][1] ?? '',
                        'error' => $data['AdminPass'][2] ?? '',
                      ]
                    );
                  ?>
                  <button type="submit" class="btn btn-default">これで設定する</button>
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
