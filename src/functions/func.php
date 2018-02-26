<?php
namespace ellsif\WelCMS;

function welPocket(): Pocket
{
    return Pocket::getInstance();
}

function welDataAccess(string $type = 'default'): DataAccess
{
    return welPocket()->getDataAccess($type);
}

function welLoadView(string $path, array $data = [])
{
    Pocket::getInstance()->getPrinter()->loadView($path, $data);
}

function welLog(string $level, string $label, string $message, string $type = 'default')
{
    $logger = welPocket()->getLogger($type);
    if (!$logger) {
        throw new Exception('Logger ' . $type . ' not found');
    }
    $logger->putLog($level, $label, $message);
}

function text($text)
{
    echo htmlspecialchars($text);
}


/**
 * パイプ区切りの文字列を分割して配列にします。
 */
function pipeExplode(string $str): array
{
    $str = trim($str, '|');

    if ($str === '') {
        return [];
    }
    return explode('|', $str);
}

/**
 * 配列をパイプ区切りの文字列にします。
 */
function pipeImplode(array $array): string
{
    if (!is_array($array) || count($array) == 0) {
        return '';
    }
    return '|' . implode('|', $array) . '|';
}