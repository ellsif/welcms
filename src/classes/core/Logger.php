<?php
namespace ellsif;

use ellsif\WelCMS\Config;
use ellsif\WelCMS\Util;

class Logger
{
    use Singleton;
    public static function getInstance() : Logger
    {
        return self::instance();
    }

    private $logLevel = 'debug';
    private $logDir = null;
    private $delim = '    ';

    const LOG_LEVELS = ['fatal', 'error', 'warn', 'info', 'debug', 'trace'];

    public function setLogDir($path)
    {
        if ($path) {
            $this->logDir = toDir($path);
        }
    }

    public function setLogLevel($logLevel)
    {
        if ($this->isValidLogLevel($logLevel)) {
            $this->logLevel = $logLevel;
        } else {
            throw new \Exception('ログレベルを更新できません。' . $logLevel . 'は無効な値です。');
        }
    }

    public function log($level, $label, $message)
    {
        if ($level == null) {
            $level = $this->logLevel;
        }
        $logLevel = array_search($level, Logger::LOG_LEVELS);
        if ($logLevel !== FALSE &&  $logLevel <= array_search($this->logLevel, Logger::LOG_LEVELS)) {
            // ログ出力
            $log = Util::getDateTime() . $this->delim . $level . $this->delim . $label . $this->delim . $message;
            if ($this->logDir) {
                $path = $this->logDir . Util::getDate('Y-m') . '.log';
                Util::writeFile($path, $log);
            } else {
                // echo $log;
            }
        }
    }

    private function isValidLogLevel($logLevel): bool
    {
        return in_array($logLevel, Logger::LOG_LEVELS);
    }
}