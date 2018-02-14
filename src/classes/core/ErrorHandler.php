<?php


namespace ellsif\WelCMS;


class ErrorHandler
{
    public function __construct()
    {
    }

    public function onError(\Error $e)
    {
        $this->log($e);
        $this->onCriticalError($e);
    }

    public function onException(Exception $e)
    {
        $this->log($e);
        if ($e->getCode() == ERR_PRINTABLE) {
            $this->onPrintableException($e);
        } elseif ($e->getCode() == ERR_INVALID) {
            $this->onInvalidException($e);
        } else {
            $this->onCriticalException($e);
        }
    }

    protected function log(\Throwable $e)
    {
        welLog(
            'error', 'WelCMS',
            $e->getCode() . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString()
        );
    }

    protected function onCriticalException(Exception $e)
    {
        WelUtil::loadView(dirname(__FILE__, 3) . '/views/error.php', ['e' => $e]);
    }

    protected function onPrintableException(Exception $e)
    {
        WelUtil::loadView(dirname(__FILE__, 3) . '/views/error.php', ['e' => $e]);
    }

    protected function onInvalidException(Exception $e)
    {
        WelUtil::loadView(dirname(__FILE__, 3) . '/views/error.php', ['e' => $e]);
    }

    protected function onCriticalError(\Error $e)
    {
        WelUtil::loadView(dirname(__FILE__, 3) . '/views/error.php', ['e' => $e]);
    }
}