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
    protected $defaultService;

    public function __construct($defaultService = 'site')
    {
        $this->defaultService = $defaultService;
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
     */
    public function routing($requestUri): Route
    {
        $route = new Route($requestUri);
        welLog('debug', 'Router', 'routing start: ' . json_encode($route->getPaths()));

        $this->setServiceAndAction($route);

        welLog('debug', 'Router',
            'routing end: ' . $route->getService() . '.' . $route->getAction()
        );
        return $route;
    }

    /**
     * ServiceとActionを決定する。
     *
     * ## 説明
     * URLからServiceとAcrionを判定し、ConfingのvarService、varAction、varActionParamsに設定します。
     */
    protected function setServiceAndAction(Route &$route): Route
    {
        $paths = $route->getPaths();
        $dir = '';
        for($i = 0; $i < count($paths); $i++) {
            $service = $paths[$i];
            $action = $paths[$i + 1] ?? 'index';
            $actionName = WelUtil::safeFunction(pathinfo($action, PATHINFO_FILENAME));
            $actionExt = pathinfo($action, PATHINFO_EXTENSION);
            if (!ctype_alnum($actionName)) {
                break;
            }
            if ($this->setCallable($route, $service, $actionName, $dir)) {
                $route->setType($actionExt);
                return $route;
            }
            $dir .= $service . '/';
        }

        // デフォルトを利用
        $service = $this->defaultService;
        $action = $paths[0] ?? 'index';
        $actionName = WelUtil::safeFunction(pathinfo($action, PATHINFO_FILENAME));
        $actionExt = pathinfo($action, PATHINFO_EXTENSION);
        if ($this->setCallable($route, $service, $actionName, '')) {
            $route->setType($actionExt);
        } else {
            throw new Exception(
                'no route was found for ' . $route->getRequestUri(),
                0, null, null, 404
            );
        }
        return $route;
    }

    /**
     * 呼び出し可能なサービスとアクションを判定しRouteに設定する。
     *
     * ## 説明
     * Routeに呼び出し可能なサービスクラスの完全修飾名と
     * 利用可能なアクションメソッドの設定を行います。
     */
    protected function setCallable(Route &$route, string $service, string $action, string $dir = ''): bool
    {
        welLog('debug', 'Router', "search: ".welPocket()->getAppPath() . 'service/' . $dir);

        $fqClassName = FileUtil::getFqClassName(
            StringUtil::toCamel($service) . 'Service',
            [
                welPocket()->getAppPath() . 'service/' . $dir,
                welPocket()->getSysPath() . 'service/' . $dir,
            ]
        );

        if (!$fqClassName) {
            return false;
        }
        $authList = [];
        foreach(welPocket()->getAuthObjects() as $auth) {
            $authList[] = $auth->getName();
        }
        $authList[] = '';
        $httpMethod = strtolower($route->getRequestMethod());

        $route->setService($fqClassName);
        foreach($authList as $auth) {
            $actionName = StringUtil::toCamel("${httpMethod}_${action}_${auth}", true);
            if(is_callable([$fqClassName, $actionName])) {
                $route->setAction($actionName);
                return true;
            }
            $actionNameAnyMethod = StringUtil::toCamel("${action}_${auth}", true);
            if(is_callable([$fqClassName, $actionNameAnyMethod])) {
                $route->setAction($actionNameAnyMethod);
                return true;
            }
        }
        return false;
    }
}