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
        if ($e->getPrevious()) {
            $this->log($e->getPrevious());
        }
    }

    protected function onCriticalException(Exception $e)
    {
        Pocket::getInstance()->getPrinter()->loadView('/error.php', ['e' => $e]);
    }

    protected function onPrintableException(Exception $e)
    {
        Pocket::getInstance()->getPrinter()->loadView('/error.php', ['e' => $e]);
    }

    protected function onInvalidException(Exception $e)
    {
        Pocket::getInstance()->getPrinter()->loadView('/error.php', ['e' => $e]);
    }

    protected function onCriticalError(\Error $e)
    {
        Pocket::getInstance()->getPrinter()->loadView('/error.php', ['e' => $e]);
    }
}