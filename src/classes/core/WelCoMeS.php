<?php

namespace ellsif\WelCMS;

use ellsif\Logger;
use ellsif\util\FileUtil;

/**
 * システムのコアクラス。
 *
 * ## 説明
 * システムの動作全体を統括するクラスです。
 */
class WelCoMeS
{

    public function __construct($confPath = null)
    {
        if ($confPath) {
            $this->loadConfig($confPath);
        }
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
        $pocket = Pocket::getInstance();

        // Loggerを起動
        $logger = Logger::getInstance();
        $logger->setLogLevel('trace');
        $logger->setLogDir($pocket->dirLog());

        // Settingテーブルから設定値をロード
        $this->loadSettings();

        // 初期化完了後、ログレベルを設定値に合わせる
        $logger->setLogLevel($pocket->logLevel());
        $logger->setLogDir($pocket->dirLog());

        // セッション開始
        $sessionHandler = new SessionHandler();
        session_set_save_handler($sessionHandler, true);
        session_start();
        register_shutdown_function('session_write_close');

        // TODO プラグインの初期化は調整の余地あり
        // $this->initPlugins();

        // Routerの初期化、ルーティング処理
        try {
            $router = new Router();
            $router->routing();

            // 該当のServiceがあれば実行
            $serviceClass = $pocket->varServiceClass();
            $action = $pocket->varActionMethod();
            $params = $pocket->varActionParams();
            $result = null;
            $logger->log('debug', 'main', "${serviceClass}::${action} called");

            if ($serviceClass) {
                $service = new $serviceClass();
                $result = $service->$action($params);
            }

            // フォーマットに対応するPrinterを初期化
            if (!$pocket->varPrinterFormat()) {
                // フォーマットの判定失敗時
                header("HTTP/1.1 404 Not Found");
                exit(0);
            }
            $printerClass = $pocket->varPrinter();
            $printMethod = $pocket->varPrinterFormat();
            $printer = new $printerClass();
            $printer->$printMethod($result);

        } catch(\Throwable $e) {

            $logger->log('error', 'system', $e->getMessage() . PHP_EOL . $e->getTraceAsString());

            // エラーを表示
            if (!$pocket->varPrinter()) {
                $pocket->varPrinter(FileUtil::getFqClassName('Printer', [$pocket->dirApp(), $pocket->dirSystem()]));
            }
            $printerClass = $pocket->varPrinter();
            $printMethod = $pocket->varPrinterFormat();
            $printer = new $printerClass();
            $result = new ServiceResult();
            $result->setView($pocket->dirSystem() . 'views/404.php');
            $result->error($e->getMessage());
            $printer->$printMethod($result);
        }
    }

    protected function showPage()
    {
        $config = Pocket::getInstance();
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
     * Settingテープルから設定をロード
     */
    protected function loadSettings()
    {
        $pocket = Pocket::getInstance();

        $settingRepo = WelUtil::getRepository('Setting');
        $list = $settingRepo->list(['name' => 'Activated']);
        $activated = count($list) > 0 && intval($list[0]['value']) == 1;
        $pocket->settingActivated($activated);
        if ($pocket->settingActivated()) {
            $settings = $settingRepo->list(['name' => ['UrlHome', 'SiteName']]);
            foreach($settings as $setting) {
                if ($setting['name'] === 'UrlHome') {
                    $pocket->settingUrlHome($setting['value']);
                } elseif ($setting['name'] === 'SiteName') {
                    $pocket->settingSiteName($setting['value']);
                }
            }
        }
    }

    /**
     * ページを表示
     */
    protected function _showPage()
    {
        $config = Pocket::getInstance();

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
        $pocket = Pocket::getInstance();
        $formData = $pocket->varFormData();
        if (!$pocket->varValid()) {
            return false;
        }

        // 有効化
        $salt = getSalt();
        $adminPass = $formData['AdminPass'][1];
        $hashed = getHashed($adminPass, $salt, 1);  // TODO 暗号化のバージョン管理は未実装

        $dataAccess = getDataAccess($pocket->dbDriver());
        $dataAccess->insert('Setting', array('label' => 'サイトURL', 'name' => 'UrlHome', 'value' => $formData['UrlHome'][1], 'use_in_page' => 1));
        $dataAccess->insert('Setting', array('label' => 'サイト名', 'name' => 'SiteName', 'value' => $formData['SiteName'][1], 'use_in_page' => 1));
        $dataAccess->insert('Setting', array('label' => '管理者ID', 'name' => 'AdminID', 'value' => $formData['AdminID'][1], 'use_in_page' => 1));
        $dataAccess->insert('Setting', array('label' => 'Hash', 'name' => 'Hash', 'value' => $hashed, 'use_in_page' => 0));

        // アクティベート済み
        $activate = $dataAccess->updateAll('Setting', ['value' => 1], ['name' => 'Activated']);

        // Configを更新
        $pocket->settingUrlHome($formData['UrlHome'][1]);

        return $activate > 0;
    }

    /**
     * 設定ファイルをロードする
     */
    private function loadConfig($confPath)
    {
        if (!file_exists($confPath)) {
            throw new Exception('設定ファイルの読み込みに失敗しました。');
        }
        include_once $confPath;
    }

    /**
     * プラグインの初期化を行う。
     */
    private function initPlugins()
    {
        $config = Pocket::getInstance();
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