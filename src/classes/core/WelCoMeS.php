<?php

namespace ellsif\WelCMS;

use ellsif\Logger;
use ellsif\util\FileUtil;
use ellsif\util\StringUtil;

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

        $logger = Logger::getInstance();
        $logger->setLogLevel($pocket->logLevel());
        $logger->setLogDir($pocket->dirLog());
        $obStarted = false;
        try {
            // Settingテーブルから設定値をロード
            $this->loadSettings();

            // セッション開始
            $sessionHandler = new SessionHandler();
            session_set_save_handler($sessionHandler, true);
            session_start();
            register_shutdown_function('session_write_close');

            // Routerの初期化、ルーティング処理
            $router = new Router();
            $router->routing();

            // 認証を行う
            Auth::setLoginUsers();
            if ($pocket->varAuth()) {
                $authClass = FileUtil::getFqClassName(
                    $pocket->varAuth() . 'Auth',
                    [ $pocket->dirApp(), $pocket->dirSystem() ]
                );
                $auth = new $authClass();
                $auth->authenticate();
            }

            // フォーマットに対応するPrinterを初期化
            if (!$pocket->varPrinterFormat()) {
                // フォーマットの判定失敗時
                header("HTTP/1.1 404 Not Found");
                exit(0);
            }

            // 該当のServiceがあれば実行
            $serviceClass = $pocket->varServiceClass();
            $action = $pocket->varActionMethod();
            $params = $pocket->varActionParams();
            $result = null;

            $logger->putLog('debug', 'WelCMS', "${serviceClass}::${action} called");
            $service = new $serviceClass();
            $actionParams = WelUtil::getParamMap($params);
            if (strcasecmp($pocket->varRequestMethod(), 'GET') === 0) {
                $actionParams = array_merge($actionParams, $_GET);
            }
            $result = $service->$action($actionParams);

            $printerClass = $pocket->varPrinter();
            $printMethod = $pocket->varPrinterFormat();
            $printer = new $printerClass();
            $obStarted = ob_start();
            $printer->$printMethod($result);
            ob_end_flush();

        } catch(\Throwable $e) {

            Logger::log(
                'error',
                'WelCMS',
                $e->getMessage() . PHP_EOL . $e->getCode() . PHP_EOL . $e->getTraceAsString()
            );

            // エラーを表示
            if ($obStarted) ob_end_clean();
            if (!$this->errorPage($e)) {
                // TODO 次バージョンではExceptionをthrowする。
                header("HTTP/1.1 " . $e->getCode());
                exit(0);
            }
        }
    }

    /**
     * 設定ファイルをロードする
     */
    protected function loadConfig($confPath)
    {
        if (!file_exists($confPath)) {
            throw new \LogicException('設定ファイルの読み込みに失敗しました。' . $confPath);
        }
        include_once $confPath;
    }

    /**
     * Settingテープルから設定をロードします。
     */
    protected function loadSettings()
    {
        $pocket = Pocket::getInstance();

        $settingRepo = WelUtil::getRepository('Setting');
        $list = $settingRepo->list(['name' => 'activate']);
        $activated = count($list) > 0 && intval($list[0]['value']) == 1;
        $pocket->settingActivated($activated);
        if ($pocket->settingActivated()) {
            $settings = $settingRepo->list(['name' => ['urlHome', 'siteName']]);
            foreach($settings as $setting) {
                if ($setting['name'] === 'urlHome') {
                    $pocket->settingUrlHome($setting['value']);
                } elseif ($setting['name'] === 'siteName') {
                    $pocket->settingSiteName($setting['value']);
                }
            }
        }
    }

    /**
     * エラーページを表示します。
     */
    protected  function errorPage(\Exception $e) :bool
    {
        $printerClass = Pocket::getInstance()->varPrinter();
        if (!$printerClass) {
            $printerClass = FileUtil::getFqClassName(
                'Printer', [Pocket::getInstance()->dirApp(), Pocket::getInstance()->dirSystem()]
            );
        }
        $printMethod = Pocket::getInstance()->varPrinterFormat() ?? 'html';
        $printer = new $printerClass();
        $result = new ServiceResult();
        $result->error($e->getMessage());
        try {
            $result->setView(Router::getViewPath($e->getCode() . '.php'));
        } catch(\LogicException $e) {
            $result->setView(Router::getViewPath('error.php'));
        }

        if ($printMethod) {
            Logger::log('error', 'WelCMS', "$printerClass::$printMethod called");
            $printer->$printMethod($result);
            return true;
        }
        return false;
    }

}