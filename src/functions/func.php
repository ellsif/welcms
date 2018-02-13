<?php
namespace ellsif\WelCMS;

function welPocket(): Pocket
{
    return Pocket::getInstance();
}

function welLog(string $level, string $label, string $message, string $type = 'default')
{
    $logger = welPocket()->getLogger($type);
    if (!$logger) {
        throw new Exception('Logger ' . $type . ' not found');
    }
    $logger->putLog($level, $label, $message);
}