<?php

namespace ellsif;

use ellsif\WelCMS\AdminService;
use ellsif\WelCMS\HtmlTemplate;
use ellsif\WelCMS\Plugin;
use ellsif\WelCMS\PluginHelper;
use ellsif\WelCMS\Router;
use ellsif\WelCMS\Config;

/**
 * システムのコアクラス。
 *
 * ## 説明
 * システムの動作全体を統括するSingletonクラスです。
 */
class WelCMS
{
  use Singleton;

  /**
   * インスタンスを取得する。
   */
  public static function getInstance() : WelCMS
  {
    return self::instance();
  }

  /**
   * システムのメイン処理を行う。
   *
   * ## 説明
   * 本メソッドを起点にシステムが動作します。
   * システムはおおまかに下記の順に動作し、ユーザーに結果を表示します。
   *
   * ### １．設定ファイルのロード
   * welcms/conf/conf.phpファイルをロードし、Configクラスが管理するデフォルトの設定値を上書きします。<br>
   *
   * ### ２．データベースからCMSの設定値を取得する
   * conf.phpから取得したDB接続情報を利用しデータベース上のsettingテーブルからCMSの設定値を取得します。
   * 取得した結果でConfigクラスが管理する設定値を更新します。<br>
   * プラグインの初期化などもこの時に実行します。<br>
   * ※CMS初回アクセス時など、DBが初期化されていない場合はアクティベーションページを表示し、以下の処理は行いません。
   *
   * ### ３．リクエストされたURLからServiceとactionを決定する
   * リクエストされたURLとHTTPのリクエストメソッドから対象のServiceクラスとactionメソッドを特定します。<br>
   * URLの形式は基本的に以下に従います。
   *
   *     //hostname{:port}/{format}/{service}/{action}/{parameters}
   *
   * - format: json, xmlなどを指定します。（未指定の場合はhtmlになります）
   * - service: サービスクラスを指定します（UserServiceの場合はuserとなります）
   * - action: サービスクラス内のメソッド名を指定します（getListの場合はlistとなります）
   * - parameters: actionに渡すパラメータを指定します。"/"区切りで複数指定可能です。
   *
   * ユーザー一覧をJSONで取得する場合は下記の様なURLになるかもしれません。
   *
   *     //localhost:8080/json/user/list
   *
   * ユーザーyamadaの詳細をHTMLで表示する場合は下記のようなURLになるかもしれません。
   *
   *     //localhost:8080/user/info/yamada
   *
   * ※ ルーティングの詳細なルールに関してはRouterクラスのマニュアルを参照してください。<br>
   *
   * ### ４．Serviceをインスタンス化し、actionメソッドを実行する
   * actionメソッドは実行結果をResultクラスのインスタンスに入れて返します。（返すように実装する必要があります）
   *
   * ### ５．ResultインスタンスをPrinterクラスを利用して出力する
   * URLで指定されたフォーマットに対応するPrinterクラスをインスタンス化し、Resultインスタンスを渡します。<br>
   * PrinterクラスはResultインスタンスとviewファイルから出力を生成します。
   * viewファイルの格納先は基本的にURLと一致している必要があります。
   *
   *     http://localhost:8080/user/info/yamada
   *
   * 上記URLの場合は下記のPHPが利用されます。
   *
   *     welcms/views/html/user/info.php
   */
  public function main()
  {
    $config = Config::getInstance();

    // Loggerを起動
    $logger = Logger::getInstance();
    $logger->setLogLevel('trace');
    $logger->setLogDir($config->dirLog());

    // 設定ファイルをロード
    $this->loadConfig();

    // セッション開始
    $sessionHandler = new SessionHandler();
    session_set_save_handler($sessionHandler, true);
    session_start();
    $session = getSession();
    $config->session($session);

    // Settingテーブルから設定値をロード
    $this->initializeSettings();

    // 初期化完了後、ログレベルを設定値に合わせる
    $logger->setLogLevel($config->logLevel());
    $logger->setLogDir($config->dirLog());

    // プラグインの初期化
    $this->initPlugins();

    // Routerの初期化、ルーティング処理
    $router = Router::getInstance();
    $router->initialize();
    $router->routing();

    // TODO   $this->auth();

    // 該当のServiceがあれば実行
    $serviceClass = $config->varServiceClass();
    $action = $config->varActionMethod();
    $params = $config->varActionParams();
    $result = null;
    if ($serviceClass) {
      $service = new $serviceClass();
      $result = $service->$action($params);
    }

    // フォーマットに対応するPrinterを初期化
    $printerClass = $config->varPrinter();
    $printMethod = $config->varPrinterFormat();
    $printer = new $printerClass();

    // 結果を表示
    $printer->$printMethod($result);
  }

  /**
   * Config関連の初期化を行う
   */
  protected function initialize()
  {
    $config = Config::getInstance();
    $logger = Logger::getInstance();
    $logger->log('trace', 'Initialize', 'WelCMS initialize start');

    // トークン付きでPOSTされた場合はバリデーションを実行
    if (isPost() && isset($_POST['sp_token'])) {
      $form = Form::getReservedForm($_POST['sp_token']);
      if ($form) {
        $validationRules = json_decode($form['validation'], true);
        $results = Validator::validAll($_POST, $validationRules);
        $config->varValidated(true);
        $config->varValid($results['valid']);
        $config->varFormData($results['results']);
        $config->varFormTargetId(intval($_POST['id']));
        $config->varFormToken($form['token']);
      } else {
        throw new \Error("複数ウィンドウを起動した等の理由により、フォームが無効になりました。再度お試しください。");
      }
    }
  }

  public function showPage()
  {
    $config = Config::getInstance();
    $url = Router::getInstance();

    if ($url->isShowActivate()) {
      require_once $config->dirWelCMS() . '/classes/admin/AdminPage.php';
      $adminPage = new AdminService();
      if (isPost()) {
        $activated = $this->execActivation();
      }
      if ($activated) {
        // アクティベーション完了時、管理画面に遷移
        $_SESSION['is_admin'] = TRUE;
        $url->redirect('/admin');
      } else {
        $adminPage->activate();
      }
    } else {
      $url->showPage();
    }
  }

  /**
   * SettingテーブルからCMSの設定を取得し、Configに反映する。
   * Settingテーブルが存在しない（DBが初期化されていない）場合はDBを初期化する。
   *
   * @return bool
   * @throws \Exception
   */
  protected function initializeSettings() :bool
  {
    try {
      // Settingテーブルから設定値を取得
      $this->loadSettings();
    } catch(\Exception $e) {
      if ($e->getCode() == -1) {
        $this->initDatabase();
      } else {
        throw $e;
      }
    }
    return true;
  }

  /**
   * 各種テーブルを作成しCMSを有効化する
   * （初期設定が行われていない場合のみ実行）
   */
  protected function initDatabase()
  {
    $logger = Logger::getInstance();
    $logger->log('trace', 'initialize', 'init database start.');

    $config = Config::getInstance();
    include($config->dirMigration() . 'sqlite_init.php');  // TODO sqlite以外の場合も

    $logger->log('trace', 'initialize', 'init database success.');
  }

  /**
   * Settingテープルから設定をロード
   */
  private function loadSettings()
  {
    $config = Config::getInstance();
    $driver = $config->dbDriver();
    if ($driver !== 'sqlite') {
      throw new \Exception("${driver}はサポートされていません。");
    }
    $dataAccess = getDataAccess();
    $settings = $dataAccess->select('Setting');
    $hash = getMap($settings, 'name', 'value');

    $config->settingUrlHome($hash['UrlHome']);
    $config->settingSiteName($hash['SiteName']);
    $config->settingActivated($hash['Activated']);
  }

  /**
   * ページを表示
   */
  protected function _showPage()
  {
    $config = Config::getInstance();

    // ページ表示用のConfigを初期化

    // templatesを取得
    require_once $config->dirWelCMS() . '/classes/Template.php';
    require_once $config->dirWelCMS() . '/classes/HtmlTemplate.php';

    // contentsを取得

    // temptalesとcontentsからoutputを生成

    // output出力
    $html = <<< EOT
EOT;
    $template = new HtmlTemplate();
    $data = $template->parse($html);

    echo $template->getString($data, [
      'aboutLink' => ['path' => '/about.html'],
      'varTest1' => ['body_type'=>'text', 'text'=>'テストだよ'],
      'pageTitle' => ['body_type'=>'text', 'text'=>'WelCMSへようこそ！'],
      'leadText' => ['body_type'=>'text', 'text'=>'WelCMSはWebサイト制作者向けの簡単CMSです。'],
      'context' => ['body_type'=>'text', 'text'=>"WelCMSを使えば、静的なHTMLで作られたホームページを存外簡単にCMS化することができます。\n実際にやってみると存外大変かもしれません。"],
    ]);
  }


  private function execActivation() :bool
  {
    $config = Config::getInstance();
    $formData = $config->varFormData();
    if (!$config->varValid()) {
      return false;
    }

    // 有効化
    $salt = getSalt();
    $adminPass = $formData['AdminPass'][1];
    $hashed = getHashed($adminPass, $salt, 1);  // TODO 暗号化のバージョン管理は未実装

    $dataAccess = getDataAccess();
    $dataAccess->insert('Setting', array('label' => 'サイトURL', 'name' => 'UrlHome', 'value' => $formData['UrlHome'][1], 'use_in_page' => 1));
    $dataAccess->insert('Setting', array('label' => 'サイト名', 'name' => 'SiteName', 'value' => $formData['SiteName'][1], 'use_in_page' => 1));
    $dataAccess->insert('Setting', array('label' => '管理者ID', 'name' => 'AdminID', 'value' => $formData['AdminID'][1], 'use_in_page' => 1));
    $dataAccess->insert('Setting', array('label' => 'Hash', 'name' => 'Hash', 'value' => $hashed, 'use_in_page' => 0));

    // アクティベート済み
    $activate = $dataAccess->updateAll('Setting', ['value' => 1], ['name' => 'Activated']);

    // Configを更新
    $config->settingUrlHome($formData['UrlHome'][1]);

    return $activate > 0;
  }

  /**
   * conf.phpをロードする
   */
  private function loadConfig()
  {
    $config = Config::getInstance();
    $confPath = $config->dirWelCMS() . 'conf/conf.php';
    if (!file_exists($confPath)) {
      throwError('設定ファイルの読み込みに失敗しました。');
    }
    include_once $confPath;
  }

  /**
   * プラグインの初期化を行う。
   */
  private function initPlugins()
  {
    $config = Config::getInstance();
    $plugins = PluginHelper::getPlugins();
    $varPlugins = [];
    foreach($plugins as $key => $plugin) {
      if (isset($plugin['current'])) {
        $plugin = $plugin['current'];
        $plugin = PluginHelper::loadPlugin(PluginHelper::getClassPath($plugin['name'], $plugin['version']));
        if (isset($plugin['object'])) {
          $plugin['object']->init();
        }
        $varPlugins[] = $plugin;
      }
    }
    $config->varPlugins($varPlugins);
  }
}