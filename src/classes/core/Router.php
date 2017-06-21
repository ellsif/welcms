<?php

namespace ellsif\WelCMS;

use ellsif\Logger;
use ellsif\util\FileUtil;
use ellsif\util\StringUtil;

/**
 * ルータクラス
 */
class Router
{
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
        $action = pathinfo($pocket->varAction(), PATHINFO_FILENAME);
        $viewPath = $path ?? (lcfirst($pocket->varService() . '/' . $action) . '.php');
        if (file_exists($pocket->dirViewApp() . $viewPath)) {
            return $pocket->dirViewApp() . $viewPath;
        } elseif (file_exists($pocket->dirView() . $viewPath)) {
            return $pocket->dirView() . $viewPath;
        } elseif (file_exists($viewPath)) {
            return $viewPath;
        }
        throw new \DomainException($viewPath . ' Not Found', 404);
    }

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

        $urlInfo = $pocket->varUrlInfo();
        $paths = $urlInfo['paths'];
        Logger::log('debug', 'WelCMS', 'routing start' . PHP_EOL. json_encode($paths));

        // サービスとアクションを取得
        if (!$this->setServiceAndAction($paths)) {
            throw new \DomainException('Not Found', 404);
        }
        $pocket->varUrlInfo($urlInfo);

        // プリンタを選択
        $this->routingSetPrinter();
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
        $config->varRequestMethod($_SERVER['REQUEST_METHOD']);
        $config->varCurrentUrl($_SERVER['REQUEST_URI']);
        $config->varCurrentPath(implode('/', $urlInfo['paths']));
    }

    /**
     * ServiceとActionを決定する。
     *
     * ## 説明
     * URLからServiceとAcrionを判定し、ConfingのvarService、varAction、varActionParamsに設定します。
     */
    protected function setServiceAndAction($paths)
    {
        Logger::getInstance()->putLog('debug', 'routing', "setServiceAndAction paths: " . json_encode($paths));

        $pocket = Pocket::getInstance();
        $dir = '';
        for($i = 0; $i < count($paths); $i++) {
            $service = $paths[$i];
            $action = $paths[$i + 1] ?? 'index';

            $actionMethod = WelUtil::safeFunction(pathinfo($action, PATHINFO_FILENAME));
            $actionExt = pathinfo($action, PATHINFO_EXTENSION);
            $callable = $this->getCallableAction($service, $actionMethod, $dir);
            if ($callable) {
                $pocket->varService($dir . $service);
                $pocket->varAction($actionExt ? ($actionMethod . '.' . $actionExt) : $actionMethod);
                $pocket->varActionParams(array_map('urldecode', array_splice($paths, $i + 2)));
                $pocket->varActionMethod($callable[0]);
                $pocket->varAuth($callable[1]);
                $pocket->varPrinterFormat($this->getPrinterFormat($actionExt));
                return true;
            }
            $dir .= $service . '/';
        }

        // 上記に無い場合はSiteServiceを利用
        $service = 'site';
        $action = $paths[0] ?? 'index';
        $actionMethod = WelUtil::safeFunction(pathinfo($action, PATHINFO_FILENAME));
        $actionExt = pathinfo($action, PATHINFO_EXTENSION);
        $callable = $this->getCallableAction($service, $actionMethod, '');
        if ($callable) {
            $pocket->varService($service);
            $pocket->varAction($actionExt ? ($actionMethod . '.' . $actionExt) : $actionMethod);
            $pocket->varActionParams(array_map('urldecode', array_splice($paths, 1)));
            $pocket->varActionMethod($callable[0]);
            $pocket->varAuth($callable[1]);
            $pocket->varPrinterFormat($this->getPrinterFormat($actionExt));
            return true;
        }
        $pocket->varPrinterFormat($this->getPrinterFormat(''));
        return false;
    }

    /**
     * 呼び出し可能なアクションを取得する。
     *
     * ## 説明
     * 命名規則に従い、Serviceファイルをrequireし、該当のActionが実行可能かチェックします。
     *
     * ## 返り値
     * メソッド名と認証方法の配列を返します。
     */
    protected function getCallableAction(string $service, string $action, string $dir = '')
    {
        $pocket = Pocket::getInstance();
        Logger::getInstance()->putLog('debug', 'routing', "service: ${service} action: ${action}");
        Logger::getInstance()->putLog('debug', 'routing', "search: ".$pocket->dirApp() . 'classes/service/' . $dir);

        $className = FileUtil::getFqClassName(
            StringUtil::toCamel($service) . 'Service',
            [$pocket->dirApp() . 'classes/service/' . $dir, $pocket->dirSystem() . 'classes/service/' . $dir]
        );

        if ($className) {
            $pocket->varServiceClass($className);

            $authList = [];
            $authClassFileList = FileUtil::getFileList([$pocket->dirApp() . '/classes/auth', $pocket->dirSystem() . '/classes/auth']);
            foreach($authClassFileList as $authClassFilePath) {
                $authClassName = pathinfo($authClassFilePath, PATHINFO_FILENAME);
                if (StringUtil::endsWith($authClassName, 'Auth') && $authClassName !== 'Auth') {
                    $authList[] = StringUtil::rightRemove($authClassName, 'Auth');
                }
            }
            $authList[] = '';   // 認証不要ページ
            $httpMethodList = [strtolower($pocket->varRequestMethod()), ''];
            foreach($authList as $auth) {
                foreach($httpMethodList as $httpMethod) {
                    $methodName = StringUtil::toCamel("${httpMethod}_${action}_${auth}", true);
                    if(is_callable([$className, $methodName])) {
                        return [$methodName, $auth];
                    }
                }
            }
        }
        return null;
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


    protected function getPrinterFormat($extension)
    {
        $extension = mb_strtolower($extension);
        if ($extension) {
            if (in_array($extension, Pocket::getInstance()->printFormats())) {
                return $extension;
            } elseif (!in_array($extension, ['php', 'html', 'htm'])) {
                throw new \InvalidArgumentException('Invalid file type ' . $extension, 404);
            }
        }
        return 'html';
    }
}