<?php

namespace ellsif\WelCMS;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use ellsif\Singleton;
use MongoDB\Driver\Exception\AuthenticationException;

/**
 * URLルーティング
 */
class Router
{
    use Singleton;
    public static function getInstance() : Router
    {
        return self::instance();
    }

    /**
     * 初期化処理を行う。
     *
     * ## 説明
     * Configの値を初期化します。
     */
    public function initialize()
    {
        $config = Config::getInstance();

        $urlInfo = Util::parseUrl($_SERVER['REQUEST_URI']);
        $config->varUrlInfo($urlInfo);
        $config->varRequestMethod(strtoupper($_SERVER['REQUEST_METHOD']));
        $config->varCurrentUrl($_SERVER['REQUEST_URI']);
        $config->varCurrentPath(implode('/', $urlInfo['paths']));
    }

    /**
     * ルーティングを行う。
     *
     * ## 例外
     * 表示できるページが存在しない場合は例外をthrowします。
     */
    public function routing()
    {
        $config = Config::getInstance();

        // アクティベーションされていない場合
        $activated = (intval($config->settingActivated()) != 0);
        if (!$activated) {
            $this->routingActivation();
            return;
        }

        $urlInfo = $config->varUrlInfo();
        $paths = $urlInfo['paths'];

        // 出力フォーマットをチェック
        $this->routingSetFormat($paths);

        // 個別ページ処理
        $pageEntity = Util::getRepository('Page');
        $pages = $pageEntity->list(['path' => $config->varCurrentPath()]);
        if (count($pages) > 0) {
            $page = $pages[0];
            $this->authenticatePage($page);
            $config->varIsPage(true);
            $this->routingSetPrinter();
            return;
        }

        // サービスとアクションを決定
        $this->setServiceAndAction($paths);

        if (!$config->varIsPage() &&
            !$this->isActionCallable($config->varService(), $config->varAction())) {
            throw new InvalidArgumentException('Not Found', 404);
        }

        // プリンタを選択
        $this->routingSetPrinter();
    }

    /**
     * ServiceとActionを決定する。
     *
     * ## 説明
     * URLからServiceとAcrionを判定し、ConfingのvarService、varAction、varActionParamsに設定します。
     *
     * ### "admin/"で始まる場合
     * 管理者向けページとして判定します。
     *
     * ### "admin/"以外で始まる場合
     * varServiceにURLの先頭要素、varActionに2番目の要素(無ければ"index")を設定します。
     * URLの先頭要素が無い場合、ConfigのvarIsTopPageにtrueを設定します。
     */
    protected function setServiceAndAction($paths)
    {
        $config = Config::getInstance();

        if (\strcmp($paths[0], 'admin') === 0) {
            $config->varIsAdminPage(true);
            if ($this->isPlugin($paths[1])) {
                $service = $paths[1];
                $action = $paths[2] ?? 'index';
                $params = array_splice($paths, 3);
            } else {
                $service = $paths[0];
                $action = $paths[1] ?? 'index';
                $params = array_splice($paths, 2);
            }
        } else {

            // その他のページの場合
            $service = $paths[0] ?? '';
            $action = $paths[1] ?? 'index';
            $params = array_splice($paths, 2);
            if (count($paths) === 0) {
                $config->varIsTopPage(true);
            }
        }

        $config->varService($service);
        $config->varAction(pathinfo($action, PATHINFO_FILENAME));
        $config->varActionParams($params);
    }


    /**
     * アクションの実行可否をチェックする。
     *
     * ## 説明
     * 命名規則に従い、Serviceファイルをrequireし、該当のActionが実行可能かチェックします。<br>
     * 同時にConfig.verActionMethodの値を更新します。
     */
    public function isActionCallable(string $service, string $action): bool
    {
        $config = Config::getInstance();
        $className = $this->getFqServiceName($service);
        if ($className) {
            $config->varServiceClass($className);

            $authList = ['User', 'Manager', 'Admin', '']; // TODO 本来はauthディレクトリ以下からリストを取得する
            $httpMethodList = [strtolower($config->varRequestMethod()), ''];
            foreach($authList as $auth) {
                foreach($httpMethodList as $httpMethod) {
                    $methodName = Util::toCamel("${httpMethod}_${action}_${auth}", true);
                    if(is_callable([$className, $methodName])) {
                        $config->varAuth($auth);
                        $config->varActionMethod($methodName);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * サービスクラスの完全修飾名を取得する。
     *
     * ## 返り値
     */
    protected function getFqServiceName(string $service)
    {
        $className = $this->className($service);
        $classFilePath = $this->getClassFilePath('service/' . $className . '.php');
        if ($classFilePath) {
            $nameSpace = Util::getNameSpace($classFilePath);
            if ($nameSpace) {
                return "\\" . $nameSpace . "\\" . $className;
            } else {
                return "\\" . $className;
            }
        }
        return false;
    }

    public function getClassFilePath($path)
    {
        $config = Config::getInstance();
        $appPath = $config->dirApp() . $path;
        $sysPath = $config->dirSystem() . $path;
        $usePath = false;

        if (file_exists($appPath)) {
            $usePath = $appPath;
        } elseif(file_exists($sysPath)) {
            $usePath = $sysPath;
        }
        return $usePath;
    }

    /**
     * クラスをrequireする。
     */
    protected function requireClass(string $dir, string $className): string
    {
        $config = Config::getInstance();
        $appPath = $config->dirApp() . $dir . $className . '.php';
        $sysPath = $config->dirSystem() . $dir . $className . '.php';
        $usePath = false;
        if (file_exists($appPath)) {
            $usePath = $appPath;
        } elseif(file_exists($sysPath)) {
            $usePath = $sysPath;
        }

        if ($usePath) {
            require_once $usePath;
            $nameSpace = Util::getNameSpace($usePath);
            $fillClassName = $nameSpace . "\\" . $className;
            if (class_exists($fillClassName)) {
                return $fillClassName;
            }
        }
        return '';
    }

    /**
     * サービス名からクラス名を取得する。
     */
    public function className(string $service): string
    {
        return Util::toCamel($service) . 'Service';
    }

    /**
     * Viewファイルのパスを判定し、取得する。
     *
     * ## 説明
     * アプリケーションディレクトリとシステムディレクトリに同名のファイルが存在する場合は、
     * アプリケーションディレクトリのViewファイルを優先的に利用します。
     */
    public static function getViewPath(string $path = null)
    {
        $config = Config::getInstance();
        $viewPath = $path ?? $config->varCurrentPath() . '.php';
        if (file_exists($config->dirViewApp() . $viewPath)) {
            return $config->dirViewApp() . $viewPath;
        } else if (file_exists($config->dirView() . $viewPath)) {
            return $config->dirView() . $viewPath;
        }
        return null;
    }

    private function routingActivation()
    {
        $config = Config::getInstance();
        if (pathinfo($config->varCurrentPath(), PATHINFO_EXTENSION)) {

            // faviconなどへのリクエストは無視（ファイルが存在しない場合のみ通る）
            throw new \InvalidArgumentException('Not Found', 404);
        }
        $config->varService('AdminService');
        $config->varAction('activate');
    }


    private function routingSetFormat($paths)
    {
        $config = Config::getInstance();
        $format = 'html'; // デフォルトはhtml
        $lastIndex = count($paths) - 1;
        $extension = ($lastIndex >= 0) ? pathinfo($paths[$lastIndex], PATHINFO_EXTENSION) : null;
        if ($extension !== null) {
            if (in_array($extension, $config->printFormats())) {
                $format = $extension;
                $paths[$lastIndex] = pathinfo($paths[$lastIndex], PATHINFO_FILENAME);
                $urlInfo['paths'] = $paths;
                $config->varUrlInfo($urlInfo);
            } elseif (!empty($extension) && !in_array($extension, ['php', 'html', 'htm'])) {
                // サポートしない拡張子の場合はNot Foundにする
                throw new \InvalidArgumentException('Not Found', 404);
            }
        }
        $config->varPrinterFormat($format);
    }

    protected function routingSetPrinter()
    {
        $config = Config::getInstance();
        $printer = 'ellsif\WelCMS\Printer';
        if ($config->varService()) {
            $servicePrinter = Util::toCamel($config->varService(), true) . 'Printer';
        } else {
            $servicePrinter = 'PagePrinter';
        }
        $this->requireClass('classes/printer/', 'Printer'); // 基底クラス
        $servicePrinterClass = $this->requireClass('classes/printer/', $servicePrinter);
        if ($servicePrinterClass) {
            if (is_callable([$servicePrinterClass, $config->varPrinterFormat()])) {
                $printer = $servicePrinterClass;
            }
        }
        $config->varPrinter($printer);
    }

    ///////////////////////////////////// 以下は消す予定

    /**
     * アクティベーションページを表示するか判定。
     * CMSの初期設定がされていない場合のみTrueを返す。
     *
     * @return bool
     */
    public function isShowActivate() :bool
    {
        $config = Config::getInstance();
        $activated = (intval($config->settingActivated()) != 0);
        if (!$activated) {  // アクティベーションされていない場合はactivateページのみ表示可能

            if ($this->isPage('welcms/activate')) {
                return true;
            }
            throw new \Error('Page Not Found', 404);
        } else {
            return false;
        }
    }

    /**
     * 有効なURLの一覧を取得する。
     *
     * @return array
     */
    public function getRoutes() :array
    {
        // TODO 未実装
    }

    /**
     * ページを表示する。
     *
     * 下記の優先順位となる
     * ・エイリアス
     * ・Admin関連のページ
     * ・Pagesに登録されているページ
     * ・プラグイン関連のページ
     */
    public function showPage()
    {
        $config = Config::getInstance();
        $urlInfo = $config->varUrlInfo();

        /*
        if (!isset($urlInfo['paths']) || count($urlInfo['paths']) == 0) {
          // indexページを表示
          \ellsif\throwError("Page Not Found.", "indexページ未実装", 404);
        }
        */

        // TODO エイリアスに一致した場合、URLを置換


        $paths = $urlInfo['paths'];

        // 管理画面系（ログインしている場合のみ）
        if ($this->isPage('admin', true)){

            // Riot.jsのコンポーネント
            if ($paths[1] === 'parts' && $this->showParts()) {
                return;
            } else if ($this->showAdmin()) {
                return;
            }
        }

        // Pagesを利用
        $page = $this->getPage();
        if ($page) {
            echo $this->getHtml($page);
            return;
        }

        // TODO プラグインがあれば利用

        // パスが有効かチェック
        if (!file_exists($config->dirRoot() . $urlInfo['path'])) {
            // TODO ここは通らない
        }
        throw new \Error('Page Not Found', 404);
    }

    /**
     * URLを判定する
     *
     * @param string $url
     * @return bool
     */
    public function isPage(string $url, bool $isPref = false) :bool
    {
        $config = Config::getInstance();
        $urlInfo = $config->varUrlInfo();
        if ($isPref) {
            return strpos(implode('/', $urlInfo['paths']), $url) === 0;
        } else {
            return strcasecmp(implode('/', $urlInfo['paths']), $url) === 0;
        }
    }

    /**
     * リダイレクトする。同時にexitする。
     *
     * @param string $url URLまたは相対パス
     * @throws \Exception
     */
    public function redirect(string $url)
    {
        if (headers_sent()) {
            throw new \Exception("ヘッダー再送エラー。");
        }

        $config = Config::getInstance();
        $ext = strtolower(pathinfo($config->varCurrentUrl(), PATHINFO_EXTENSION));
        if ($ext === '' || in_array($ext, Router::EXTENTIONS_REDIRECT)) {
            if (\ellsif\isUrl($url)) {
                header('Location: ' . $url);
            } else {
                header('Location: ' . $this->getUrl($url));
            }
            exit;
        }
        throw new \Error("File Not Found", 404);
    }

    /**
     * URLを取得する。
     * TODO 調整が必要
     *
     * @param string $path
     * @return string
     */
    public static function getUrl($path = '') :string
    {
        $config = Config::getInstance();
        if (strpos($path, '/') === 0) {
            $path = mb_substr($path, 1);
        }
        $urlInfo = $config->varUrlInfo();
        $urlHost = '//' . $urlInfo['host'];
        if ($urlInfo['port'] && intval($urlInfo['port']) != 80) {
            $urlHost .= ':' . $urlInfo['port'];
        }
        // TODO settingsからURLを取得するよう修正する必要がある。
        // 最終的に、URL毎に異なるDBを参照するようにしたいので、sitesテーブルから取得か・・・？
        // システム管理画面からはURLベースで良い。
        // となると、システム管理画面とサイト管理画面の両方が必要になる。またはサイト管理者にシステム管理権限を設ける？
        return $urlHost . '/' . $path;
        //return $config->settingUrlHome() . $path;
    }


    /**
     * URLからパラメータを取得する。
     * 例）下記のようなURLの場合（index=2）
     * http://hostname/class/action/arg1/val1/arg2/val2?get1=val1&get2=val2&arg1=val3
     *
     * ['arg1' => 'val3', 'arg2' => 'val2', 'get1' => 'val1', 'get2' => 'val2']
     *
     * @param int $index pathの切り出し開始位置
     * @return array
     */
    private function getParams(int $index) :array
    {
        $config = Config::getInstance();
        $urlInfo = $config->varUrlInfo();
        $paths = $urlInfo['paths'];
        $params = [];

        // Pathからパラメータをパース
        if (count($paths) > $index) {
            if (count($paths) % 2 == 1) {
                $paths[] = '';
            }
            for($i = $index; $i < count($paths); $i+=2) {
                $params[$paths[$i]] = $paths[$i+1];
            }
        }

        // GETパラメータを追加
        if (isset($urlInfo['query']) && $urlInfo['query']) {
            $_gets = [];
            parse_str($urlInfo['query'], $_gets);
            $params = array_merge($params, $_gets);
        }

        return $params;
    }

    /**
     * 管理者向けページを表示する
     * @return bool
     */
    private function showAdmin() :bool
    {
        $config = Config::getInstance();
        $urlInfo = $config->varUrlInfo();
        $paths = $urlInfo['paths'];

        $action  = 'index';
        $i = 1;
        if (count($paths) >= 2) {
            $action = strtolower(pathinfo($paths[1], PATHINFO_FILENAME));
            $i = 2;
        }

        if ($action === 'login') {
            // ログインページは認証を通らない
            //$config->varParams($this->getParams($i));
            $config->varAction('admin/' . $action);
            $adminPage = new AdminService();
            $adminPage->login($config->dirView() . 'admin/login.php', array_slice($paths, $i));
            return true;
        }
        try {
            if ($this->isPlugin($action)) {
                // プラグイン管理画面
                $this->showPluginAdmin($action, array_slice($paths, $i));
            } else {
                // 管理画面を表示
                //$config->varParams($this->getParams($i)); // TODO これか？
                $adminPage = new AdminService();
                $adminPage->show($action, '', array_slice($paths, $i));
            }
            return true;
        } catch(\Error $e) {
            if ($e->getCode() === 404) {
                // viewがあれば静的ページとみなして表示
                $viewPath = $config->dirView() . "admin/${action}.php";
                if (file_exists($viewPath)) {
                    // TODO 権限チェックを通らないか？
                    $adminPage = new AdminService();
                    $adminPage->loadView($viewPath);
                } else {
                    $this->show404();
                }
                return true;
            } else if ($e->getCode() === 401) {
                // 権限エラーの場合はログイン画面を表示
                $urlManager = Router::getInstance();
                $urlManager->redirect('admin/login');
            } else {
                \ellsif\throwError('システムエラーが発生しました', $e->getMessage(), 500, $e);
            }
        }
        return false;
    }

    protected function showParts($isAdmin = true)
    {
        $config = Config::getInstance();
        $urlInfo = $config->varUrlInfo();
        $paths = $urlInfo['paths'];
        $partsPath = $config->dirView() . 'admin/parts/' . basename($paths[2]) . '.php';
        if ($isAdmin && file_exists($partsPath)) {
            include $partsPath;
            return true;
        }
        return false;
    }

    /**
     * プラグインかどうか判定する
     *
     * @param string $name
     * @return bool
     */
    private function isPlugin(string $name) :bool
    {
        $config = Config::getInstance();
        $plugins = $config->varPlugins();
        return isset($plugins[strtolower($name)]);
    }

    /**
     * プラグインの管理画面を表示（プラグインを管理する画面ではなく、プラグイン自体の管理画面（あれば））
     *
     * @param string $pluginName
     * @param array $params
     */
    private function showPluginAdmin(string $pluginName, array $params)
    {
        $plugin = $this->getPlugin($pluginName);
        if ($plugin) {
            var_dump($pluginName);
            echo $plugin->getName();
        } else {
            \ellsif\throwError('Page Not Found', 404);
        }
    }

    /**
     * プラグインを取得
     *
     * @param string $pluginName
     * @return Plugin
     */
    private function getPlugin(string $pluginName) :Plugin
    {
        $config = Config::getInstance();
        $dir = strtolower($pluginName);
        require_once $config->dirWelCMS() . 'classes/Plugin.php';
        require_once $config->dirWelCMS() . "plugins/${dir}/${pluginName}.php";

        $config = Config::getInstance();
        $plugins = $config->varPlugins();
        $class = $plugins[$pluginName]['class'];
        return $class::getInstance();
    }

    /**
     * URLを元に表示するページを取得
     */
    private function getPage()
    {
        $config = Config::getInstance();
        $model = \ellsif\getEntity('Pages');
        $urlInfo = $config->varUrlInfo();
        $path = rtrim($urlInfo['path'], '/');
        $path = ($path) ? $path : '/';
        $page = $model->list(['path' => $path], 0, 1);
        if (count($page) > 0) {
            return $page[0];
        }
        return false;
    }

    /**
     * テンプレートにコンテンツを合成したHTMLを取得
     *
     * @param $page
     * @return string
     */
    private function getHtml($page): string
    {
        $dataAccess = \ellsif\getDataAccess();
        $templateData = $dataAccess->get('templates', $page['template_id']);

        $htmlTemplate = new HtmlTemplate();
        $contents = $htmlTemplate->getPageContents($page['id']);
        $contents = \ellsif\getMap($contents, 'name');
        $templateData = json_decode($templateData['body_template'], true);
        return $htmlTemplate->getString($templateData, $contents);
    }

    /**
     * 404ページを表示
     */
    private function show404()
    {
        $adminPage = new AdminService();
        $adminPage->show404();
    }
}