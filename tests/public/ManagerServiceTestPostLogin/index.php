<?php
/**
 *
 */

namespace ellsif\WelCMS;

require_once \dirname(__FILE__, 4) . '/vendor/autoload.php';

// テスト用の設定ファイル
$pocket = Pocket::getInstance();

$pocket->dbDatabase(dirname(__FILE__, 3) . '/data/ManagerServiceTestPostLogin.sqlite');
$pocket->dirSystem(dirname(__FILE__, 4) . '/src/');
$pocket->dirWelCMS('ManagerServiceTestPostLogin/');
$pocket->varRoot('ManagerServiceTestPostLogin/');
$pocket->dirLog(dirname(__FILE__, 3) . '/logs/');
$pocket->dirInitialize($pocket->dirSystem() . 'init/');


$welCMS = new WelCoMeS();
$welCMS->main();