<?php

namespace ellsif\WelCMS;

use ellsif\util\FileUtil;
use ellsif\util\StringUtil;

/**
 * システムのコアクラス。
 *
 * ## 説明
 * システムの動作全体を統括するクラスです。
 */
class WelCms
{

    public function __construct()
    {
        require_once dirname(__FILE__, 3) . '/functions/const.php';
        require_once dirname(__FILE__, 3) . '/functions/func.php';
        require_once dirname(__FILE__, 3) . '/functions/helper.php';
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
    public function main($indexPath)
    {
        welPocket()->setSysPath(dirname(__FILE__, 3));
        welPocket()->setIndexPath(StringUtil::suffix($indexPath, '/'));
        if (!welPocket()->getAppPath()) {
            welPocket()->setAppPath(realpath($indexPath . '/../app/'));
        } else {
            welPocket()->setAppPath(StringUtil::suffix(welPocket()->getAppPath(), '/'));
        }
        if (!welPocket()->getViewPath()) {
            welPocket()->setViewPath(welPocket()->getAppPath() . 'views/');
        }

        $this->initLogger();
        $this->initErrorHandler();
        $this->initPrinter();
        $this->initAuth();
        $this->initRouter();

        $obStarted = false;
        try {
            session_start();

            // Routerの初期化、ルーティング
            $router = new Router();
            $route = $router->routing($_SERVER['REQUEST_URI']);
            welPocket()->setRouter($router);
            $printerType = $route->getType() ? $route->getType() : 'html';
            if (!welPocket()->getPrinter($printerType)) {
                throw new Exception($printerType . ' Printer Not Found', ERR_CRITICAL, null, null, 404);
            }

            if ($route->getAuth()) {
                $auth = welPocket()->getAuth($route->getAuth());
                if (!$auth) {
                    throw new Exception($auth . ' Auth Not Found');
                }
                if (!$auth->isAuthenticated()) {
                    $auth->onAuthError($route['printerFormat']);
                    exit(0);
                }
            }

            $serviceClass = $route->getService();
            $action = $route->getAction();
            if (!$serviceClass || !$action) {
                throw new Exception('Service or Action not found');
            }

            $service = new $serviceClass();
            $result = $service->$action(new ActionParams($route));

            // 結果を出力
            $printer = welPocket()->getPrinter($printerType);
            welLog('debug', 'WelCms', $router->getViewPath() . ' loadView start');
            $obStarted = ob_start();
            $printer->print($result);
            $obStarted = !ob_end_flush();
            welLog('debug', 'WelCms', $router->getViewPath() . ' loadView end');
            session_write_close();
        } catch(\Exception $e) {

            welLog(
                'error',
                'WelCMS',
                $e->getCode() . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString()
            );

            if ($obStarted) {
                ob_end_clean();
            }

            welPocket()->getErrorHandler()->onError($e);
        }
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

    /**
     * Loggerが設定されていない場合、デフォルトのLoggerで初期化します。
     *
     * デフォルトのログ出力先は/app/logs/になります。
     */
    protected function initLogger()
    {
        if (!welPocket()->getLogger()) {
            welPocket()->setLogger(
                new Logger(
                    StringUtil::suffix(welPocket()->getAppPath(), '/') . 'logs/'
                )
            );
        }
    }

    /**
     * ErrorHandlerが設定されていない場合、デフォルトのErrorHandlerで初期化します。
     */
    protected function initErrorHandler()
    {
        if (!welPocket()->getErrorHandler()) {
            welPocket()->setErrorHandler(new ErrorHandler());
        }
    }

    /**
     * Routerが設定されていない場合、デフォルトのRouterで初期化します。
     */
    protected function initRouter()
    {
        if (!welPocket()->getRouter()) {
            welPocket()->setRouter(new Router());
        }
    }

    /**
     * Printerが設定されていない場合、HtmlとJsonプリンタで初期化します。
     */
    protected function initPrinter()
    {
        if (!welPocket()->getPrinter()) {
            welPocket()
                ->addPrinter(new HtmlPrinter())
                ->addPrinter(new JsonPrinter());
        }
    }

    /**
     * Authが設定されていない場合、デフォルトのAuthで初期化します。
     */
    protected function initAuth()
    {
        if (!welPocket()->getAuthObjects()) {
            welPocket()
                ->addAuth(new AdminAuth())
                ->addAuth(new ManagerAuth())
                ->addAuth(new UserAuth());
        }
    }
}