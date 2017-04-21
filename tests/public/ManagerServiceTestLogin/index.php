<?php
/**
 *
 */

namespace ellsif\WelCMS;

require_once \dirname(__FILE__, 4) . '/vendor/autoload.php';

// テスト用の設定ファイル
$pocket = Pocket::getInstance();

$pocket->dbDatabase(dirname(__FILE__, 3) . '/data/ManagerServiceTestLogin.sqlite');
$pocket->dirSystem(dirname(__FILE__, 4) . '/src/');
$pocket->dirWelCMS('ManagerServiceTestLogin/');
$pocket->dirRoot('ManagerServiceTestLogin/');
$pocket->dirLog(dirname(__FILE__, 3) . '/logs/');
$pocket->dirInitialize($pocket->dirSystem() . 'init/');


$welCMS = new WelCoMeS();
$welCMS->main();