<?php
namespace ellsif\WelCMS;

// テスト用の設定ファイル
$pocket = Pocket::getInstance();

$pocket->dbDatabase(dirname(__FILE__, 3) . '/data/WelCoMeSTest.sqlite');
$pocket->dirLog(dirname(__FILE__, 4) . '/logs/');
$pocket->dirInitialize($pocket->dirSystem() . 'init/');
