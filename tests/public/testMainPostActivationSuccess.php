<?php
/**
 *
 */

namespace ellsif\WelCMS;

require_once \dirname(__FILE__, 3) . '/vendor/autoload.php';

// テスト用の設定ファイル
$pocket = Pocket::getInstance();

$pocket->dbDatabase(dirname(__FILE__, 2) . '/data/WelCoMeSTestMainPostActivationSuccess.sqlite');
$pocket->dirSystem(dirname(__FILE__, 3) . '/src/');
$pocket->dirLog(dirname(__FILE__, 2) . '/logs/');
$pocket->logLevel('debug');
$pocket->dirInitialize($pocket->dirWelCMS() . 'init/');

$welCMS = new WelCoMeS();
$welCMS->main();