<?php
namespace ellsif\WelCMS;

use ellsif\util\FileUtil;
use ellsif\util\StringUtil;
use ellsif\WelCMS\WelUtil;

class Logger
{
    private $logLevel = 'debug';
    private $logDir = null;
    private $delim = '    ';

    public function __construct(string $logDir)
    {
        $this->logDir = $logDir;
    }

    const LOG_LEVELS = ['fatal', 'error', 'warn', 'info', 'debug', 'trace'];

    public static function log($level, $label, $message)
    {
        Logger::getInstance()->putLog($level, $label, $message);
    }

    public function setLogDir($path): Logger
    {
        $this->logDir = StringUtil::suffix($path, '/');
        return self::instance();
    }

    public function setLogLevel($logLevel)
    {
        if ($this->isValidLogLevel($logLevel)) {
            $this->logLevel = $logLevel;
        } else {
            throw new \Exception('ログレベルを更新できません。' . $logLevel . 'は無効な値です。');
        }
    }

    /**
     * ログ出力を行います。
     *
     * ## 説明
     *
     */
    public function putLog($level, $label, $message)
    {
        if ($level == null) {
            $level = $this->logLevel;
        }
        $logLevel = array_search($level, Logger::LOG_LEVELS);

        if ($logLevel !== FALSE &&  $logLevel <= array_search($this->logLevel, Logger::LOG_LEVELS)) {
            // ログ出力
            $log = WelUtil::getDateTime() . $this->delim . $level . $this->delim . $label . $this->delim . $message;
            if ($level === 'debug' || $level === 'trace' || $level === 'error') {
                $debug = debug_backtrace();
                $log .= $this->delim . "file: " . $debug[0]['file'] . "(line: " . $debug[0]['line'] . ")";
            }
            if ($this->logDir) {
                $path = $this->logDir . WelUtil::getDate('Y-m') . '.log';
                FileUtil::writeFile($path, $log);
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