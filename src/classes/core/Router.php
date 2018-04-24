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
    protected $route;

    protected $defaultService;

    public function __construct($defaultService = 'site')
    {
        $this->defaultService = $defaultService;
    }

    /**
     * ルーティングを行います。
     */
    public function routing($requestUri): Route
    {
        $route = new Route($requestUri);
        welLog('debug', 'Router', 'routing start: ' . json_encode($route->getPaths()));

        $this->setServiceAndAction($route);

        welLog('debug', 'Router',
            'routing end: ' . $route->getService() . '.' . $route->getAction()
        );
        $this->route = $route;
        return $this->route;
    }

    /**
     * Routeを取得します。
     */
    public function getRoute(): ?Route
    {
        return $this->route;
    }

    /**
     * Viewファイルのパスを判定し、取得する。
     *
     * ## 説明
     * アプリケーションディレクトリとシステムディレクトリに同名のファイルが存在する場合は、
     * アプリケーションディレクトリのViewファイルを優先的に利用します。
     *
     * TODO リクエストメソッドによるVIEWの振り分けを対応するか？
     */
    public function getViewPath()
    {
        $prefix = '';
        if ($this->getRoute()->getType() !== 'html') {
            $prefix = '_' . $this->getRoute()->getType() . '/';
        }
        $actionName = $this->getRoute()->getActionName();
        $viewPath = $prefix . $this->getRoute()->getServicePath() . $actionName . '.php';
        $fullViewPath = RoutingUtil::getViewPath($viewPath);
        if ($fullViewPath) {
            return $fullViewPath;
        }
        throw new Exception('view file ' . $viewPath . ' Not Found');
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
        for($i = count($paths); $i >= 0; $i--) {
            $dir = implode('/', array_slice($paths, 0, $i));
            $service = $paths[$i - 1] ?? $paths[$i];
            $action = ($i > 0) ? $paths[$i] : 'index';
            $actionName = WelUtil::safeFunction(pathinfo($action, PATHINFO_FILENAME));
            $actionExt = pathinfo($action, PATHINFO_EXTENSION);
            if (!ctype_alnum($actionName)) {
                break;
            }
            if ($this->setCallable($route, $service, $actionName, $dir)) {
                $route->setType($actionExt);
                $route->setServicePath($dir . $service . '/');
                $route->setParams(RoutingUtil::getParamMap(array_slice($paths, $i + 1)));
                return $route;
            }
        }

        // デフォルトを利用
        $service = $this->defaultService;
        $action = $paths[0] ?? 'index';
        $actionName = WelUtil::safeFunction(pathinfo($action, PATHINFO_FILENAME));
        $actionExt = pathinfo($action, PATHINFO_EXTENSION);
        if ($this->setCallable($route, $service, $actionName, '')) {
            $route->setType($actionExt);
            $route->setServicePath('');
            $route->setParams(RoutingUtil::getParamMap(array_slice($paths, 1)));
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
    protected function setCallable(Route &$route, string $service, string $actionName, string $dir = ''): bool
    {
        $serviceClassName = StringUtil::toCamel($service) . 'Service';
        welLog('debug', 'Router', 'search: service/' . $dir . '/' . $serviceClassName);

        $fqClassName = FileUtil::getFqClassName(
            $serviceClassName,
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
        $route->setServiceName($service);
        foreach($authList as $auth) {
            $_auth = ($auth) ? "_${auth}" : '';
            $actionMethod = StringUtil::toCamel("${httpMethod}_${actionName}${_auth}", true);
            if(is_callable([$fqClassName, $actionMethod])) {
                $route->setAction($actionMethod);
                $route->setActionName($actionName);
                $route->setAuth($auth);
                return true;
            }
            $actionMethodAny = StringUtil::toCamel("${actionName}${_auth}", true);
            if(is_callable([$fqClassName, $actionMethodAny])) {
                $route->setAction($actionMethodAny);
                $route->setActionName($actionName);
                $route->setAuth($auth);
                return true;
            }
        }
        return false;
    }
}