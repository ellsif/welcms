<?php


namespace ellsif\WelCMS;


class ErrorHandler
{
    public function __construct()
    {
    }

    public function onError(Exception $e)
    {
        if ($e->getCode() == ERR_PRINTABLE) {
            $this->onPrintableError($e);
        } elseif ($e->getCode() == ERR_INVALID) {
            $this->onInvalidError($e);
        } else {
            $this->onCriticalError($e);
        }
    }

    protected function onCriticalError(Exception $e)
    {
        WelUtil::loadView(dirname(__FILE__, 3) . '/views/error.php', ['e' => $e]);
    }

    protected function onPrintableError(Exception $e)
    {
        WelUtil::loadView(dirname(__FILE__, 3) . '/views/error.php', ['e' => $e]);
    }

    protected function onInvalidError(Exception $e)
    {
        WelUtil::loadView(dirname(__FILE__, 3) . '/views/error.php', ['e' => $e]);
    }
}