<?php

namespace ellsif\WelCMS;

use ellsif\Logger;
use ellsif\Singleton;

/**
 * WelCMSプラグイン基底クラス
 *
 * @package ellsif\WelCMS
 */
abstract class Plugin
{

  /**
   * プラグインの作者を返す。
   *
   * @return string
   */
  abstract function getAuthor(): string;

  /**
   * プラグインがサポートするWelCMSのバージョンを返す。
   *
   * @return string
   */
  abstract function getSupported(): string;

  /**
   * プラグインのリリース日を返す。
   *
   * @return string
   */
  abstract function getReleased(): string;

  /**
   * プラグインの初期化処理
   */
  function init() {
    $logger = Logger::getInstance();
    $logger->putLog('debug', 'plugin', 'initialize ' . $this->getName() . ' start');
    $this->initialize();
    $logger->putLog('debug', 'plugin', 'initialize ' . $this->getName() . ' end');
  }

  /**
   * プラグインの初期化処理を行う
   *
   * @return void
   */
  abstract function initialize();

  /**
   * プラグインの表示名を返す。
   *
   * @return string
   */
  abstract function getLabel(): string;

  /**
   * プラグインの名前（名前空間を含まないclass名）を返す。
   *
   * @return string
   */
  function getName() :string
  {
    $ary = explode('\\', get_class($this));
    return $ary[count($ary) - 1];
  }

  /**
   * プラグインのクラス名を返す。
   *
   * @return string
   */
  function getClass() :string
  {
    return get_class($this);
  }
}