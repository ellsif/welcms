<?php

namespace ellsif\WelCMS;

use ellsif\Logger;
use ellsif\util\FileUtil;
use ellsif\util\StringUtil;

/**
 * URLルーティング
 */
class Router
{

    /**
     * ルーティングを行う。
     *
     * ## 例外
     * 表示できるページが存在しない場合は例外をthrowします。
     */
    public function routing()
    {
        $pocket = Pocket::getInstance();
        $this->initialize();

        // アクティベーション処理
        $activated = (intval($pocket->settingActivated()) != 0);
        if (!$activated) {
            $this->routingActivation();
            return;
        }

        $urlInfo = $pocket->varUrlInfo();
        $paths = $urlInfo['paths'];
        Logger::getInstance()->log('debug', 'routing', json_encode($paths));

        // 出力フォーマットをチェック
        if (!$this->routingSetFormat($paths)) {
            // 不正なフォーマットが指定された場合は404
            throw new \InvalidArgumentException('Not Found', 404);
        }

        // 個別ページ処理
        $pageEntity = WelUtil::getRepository('Page');
        $pages = $pageEntity->list(['path' => $pocket->varCurrentPath()]);
        if (count($pages) > 0) {
            $page = $pages[0];

            WelUtil::authenticatePage($page);
            $pocket->varIsPage(true);
            $this->routingSetPrinter();
            return;
        }

        // プリンタを選択
        $this->routingSetPrinter();

        // サービスとアクションを決定
        $this->setServiceAndAction($paths);
        if (!$pocket->varIsPage()) {

            list($actionMethod, $auth) = $this->getCallableAction($pocket->varService(), $pocket->varAction());
            if ($actionMethod == null) {
                throw new \InvalidArgumentException('Not Found', 404);
            }
            $pocket->varActionMethod($actionMethod);
            $pocket->varAuth($auth);
        }

        // 認証を行う
        if ($pocket->varAuth()) {
            $authClass = FileUtil::getFqClassName($pocket->varAuth() . 'Auth', [$pocket->dirApp(), $pocket->dirSystem()]);
            $auth = new $authClass();
            $auth->authenticate();
        }
    }

    /**
     * 初期化処理を行う。
     *
     * ## 説明
     * Configの値を初期化します。
     */
    protected function initialize()
    {
        $config = Pocket::getInstance();

        $urlInfo = WelUtil::parseUrl($_SERVER['REQUEST_URI']);
        $config->varUrlInfo($urlInfo);
        $config->varRequestMethod(strtoupper($_SERVER['REQUEST_METHOD']));
        $config->varCurrentUrl($_SERVER['REQUEST_URI']);
        $config->varCurrentPath(implode('/', $urlInfo['paths']));
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
        $config = Pocket::getInstance();

        if (\strcmp($paths[0], 'admin') === 0) {
            $config->varIsAdminPage(true);

            // TODO プラグイン関連は未実装
            /*
            if ($this->isPlugin($paths[1])) {
                $service = $paths[1];
                $action = $paths[2] ?? 'index';
                $params = array_splice($paths, 3);
            } else {
            */
            $service = $paths[0];
            $action = $paths[1] ?? 'index';
            $params = array_splice($paths, 2);
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
     * 呼び出し可能なアクションを取得する。
     *
     * ## 説明
     * 命名規則に従い、Serviceファイルをrequireし、該当のActionが実行可能かチェックします。<br>
     * 同時にConfig.verActionMethodの値を更新します。
     */
    protected function getCallableAction(string $service, string $action)
    {
        Logger::getInstance()->log('debug', 'routing', "service: ${service} action: ${action}");
        $config = Pocket::getInstance();
        $className = FileUtil::getFqClassName(StringUtil::toCamel($service) . 'Service', [$config->dirApp(), $config->dirSystem()]);
        if ($className) {
            $config->varServiceClass($className);

            $authList = [];
            $authClassFileList = FileUtil::getFileList([$config->dirApp() . '/classes/auth', $config->dirSystem() . '/classes/auth']);
            foreach($authClassFileList as $authClassFilePath) {
                $authClassName = pathinfo($authClassFilePath, PATHINFO_FILENAME);
                if (StringUtil::endsWith($authClassName, 'Auth') && $authClassName !== 'Auth') {
                    $authList[] = StringUtil::rightRemove($authClassName, 'Auth');
                }
            }
            $authList[] = '';   // 認証不要ページ
            $httpMethodList = [strtolower($config->varRequestMethod()), ''];
            foreach($authList as $auth) {
                foreach($httpMethodList as $httpMethod) {
                    $methodName = StringUtil::toCamel("${httpMethod}_${action}_${auth}", true);
                    if(is_callable([$className, $methodName])) {
                        return [$methodName, $auth];
                    }
                }
            }
        }
        return [null, null];
    }

    /**
     * クラスをrequireする。
     */
    protected function requireClass(string $dir, string $className): string
    {
        $config = Pocket::getInstance();
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
            $nameSpace = FileUtil::getNameSpace($usePath);
            $fillClassName = $nameSpace . "\\" . $className;
            if (class_exists($fillClassName)) {
                return $fillClassName;
            }
        }
        return '';
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
        $pocket = Pocket::getInstance();
        $viewPath = $path ?? $pocket->dirView() . strtolower($pocket->varService() . '/' . $pocket->varAction()) . '.php';
        if (file_exists($pocket->dirViewApp() . $viewPath)) {
            return $pocket->dirViewApp() . $viewPath;
        } elseif (file_exists($pocket->dirView() . $viewPath)) {
            return $pocket->dirView() . $viewPath;
        } elseif (file_exists($viewPath)) {
            return $viewPath;
        }
        throw new \InvalidArgumentException($viewPath . ' Not Found', 404);
    }

    /**
     * アクティベーションページを表示するためのルーティング処理。
     */
    protected function routingActivation()
    {
        $pocket = Pocket::getInstance();
        $ext = pathinfo($pocket->varCurrentPath(), PATHINFO_EXTENSION);
        if ($ext && strcasecmp($ext, 'php') !== 0) {
            // faviconなどへのリクエストは無視（ファイルが存在しない場合のみ通る）
            throw new \InvalidArgumentException('Not Found', 404);
        }
        $pocket->varService('Admin');
        $pocket->varServiceClass(FileUtil::getFqClassName('AdminService', [$pocket->dirApp(), $pocket->dirSystem()]));
        $pocket->varAction('activate');

        list($actionMethod, $auth) = $this->getCallableAction($pocket->varService(), $pocket->varAction());
        $pocket->varActionMethod($actionMethod);
        $pocket->varAuth($auth);
        $pocket->varPrinter(FileUtil::getFqClassName('Printer', [$pocket->dirApp(), $pocket->dirSystem()]));
        $pocket->varPrinterFormat('html');
    }

    /**
     * リクエストから出力フォーマットを設定する。
     */
    private function routingSetFormat($paths): bool
    {
        $config = Pocket::getInstance();
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
                return false;
            }
        }
        $config->varPrinterFormat($format);
        return true;
    }

    /**
     * Printerを設定する。
     *
     * ## 説明
     * 下記の順にURLの判定を行い、対応するPrinterクラスを決定します。
     * Printerクラスの完全修飾名がvarPrinterに設定されます。
     *
     * - 個別ページURLが指定された場合はPagePrinterを利用します。
     * - サービス名に対応するPrinterクラスが存在する場合は該当のPrinterを利用します。
     * - 上記以外の場合はデフォルトのPrinterクラスを利用します。
     */
    protected function routingSetPrinter()
    {
        $pocket = Pocket::getInstance();
        if ($pocket->varIsPage()) {
            $printerClassName = 'PagePrinter';
        } else {
            $printerClassName = StringUtil::toCamel($pocket->varService(), true) . 'Printer';
        }

        $printerFqClassName = FileUtil::getFqClassName($printerClassName, [$pocket->dirApp(), $pocket->dirSystem()]);
        if (!$printerFqClassName) {
            $printerFqClassName = FileUtil::getFqClassName('Printer', [$pocket->dirApp(), $pocket->dirSystem()]);  // デフォルト
        }
        $pocket->varPrinter($printerFqClassName);
    }

}