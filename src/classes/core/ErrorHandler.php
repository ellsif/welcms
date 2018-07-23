<?php


namespace ellsif\WelCMS;


class ErrorHandler
{
    public function __construct()
    {
    }

    public function onError(\Error $e)
    {
        try {
            $this->log($e);
            $this->onCriticalError($e, welPocket()->getRouter());
        } catch (\Throwable $t) {
            $this->log($t);
            echo '500 System Error';
            exit();
        }
    }

    public function onException(Exception $e)
    {
        $this->log($e);
        try {
            if ($e->getCode() == ERR_PRINTABLE) {
                $this->onPrintableException($e, welPocket()->getRouter());
            } elseif ($e->getCode() == ERR_INVALID) {
                $this->onInvalidException($e, welPocket()->getRouter());
            } else {
                $this->onCriticalException($e, welPocket()->getRouter());
            }
        } catch (\Throwable $t) {
            $this->log($t);
            echo '500 System Error';
            exit();
        }
    }

    protected function log(\Throwable $e)
    {
        welLog(
            'error', 'WelCMS',
            $e->getCode() . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString()
        );
        if ($e->getPrevious()) {
            $this->log($e->getPrevious());
        }
    }

    protected function onCriticalException(Exception $e, $router = null)
    {
        $this->onErrorCommon($e, $router);
    }

    protected function onPrintableException(Exception $e, $router = null)
    {
        $this->onErrorCommon($e, $router);
    }

    protected function onInvalidException(Exception $e, $router = null)
    {
        $this->onErrorCommon($e, $router);
    }

    protected function onCriticalError(\Error $e, $router = null)
    {
        $this->onErrorCommon($e, $router);
    }

    protected function getPrinterType($router)
    {
        $route = null;
        if ($router) {
            $route = $router->getRoute();
        }
        $printerType = null;
        if ($route && $route->getType()) {
            $printerType = $route->getType();
        }
        if (!$printerType) {
            $printerType = 'html';
        }
        return $printerType;
    }

    private function onErrorCommon(\Throwable $e, $router = null)
    {
        $printerType = $this->getPrinterType($router);
        $printer = welPocket()->getPrinter($printerType);
        $result = new ServiceResult([]);
        $viewPath = RoutingUtil::getViewPath($e->getCode() . '.php');
        if ($viewPath) {
            $result->setView($viewPath);
        } else {
            $result->setView(RoutingUtil::getViewPath('error.php'));
        }
        $printer->print($result);
    }
}