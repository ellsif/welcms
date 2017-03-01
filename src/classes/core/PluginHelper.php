<?php

namespace ellsif\WelCMS;

/**
 * プラグイン管理用の関数をまとめたもの
 */
class PluginHelper
{
  /**
   * プラグイン情報の一覧を取得する。（pluginsテーブルに登録のあるもの）
   *
   * @param bool $isActive
   * @return array
   */
  public static function getPlugins(bool $isActive = true) :array
  {
    // pluginsテーブルから設定を取得する
    $dataAccess = \ellsif\getDataAccess();
    if ($isActive) {
      $plugins = $dataAccess->select('Plugin', 0, -1, 'name ASC, version DESC', ['active' => 1]);
    } else {
      $plugins = $dataAccess->select('Plugin', 0, -1, 'name ASC, version DESC');
    }
    $results = [];
    foreach ($plugins as $plugin) {
      $key = $plugin['name'];
      if (!isset($results[$key])) {
        $results[$key] = ['current' => null, 'versions' => []];
      }
      $results[$key]['latest'] = $plugin;
      if (intval($plugin['active']) === 1) {
        $results[$key]['current'] = $plugin;
      }
      $results[$key]['versions'][] = $plugin['version'];
    }
    return $results;
  }

  /**
   * プラグインディレクトリからプラグインの一覧を取得する。
   * （取得する情報はプラグイン名とバージョンのみ）
   *
   * @return array
   */
  public static function getPluginsByDir() :array
  {
    $results = [];
    $config = Config::getInstance();
    $it = new \RecursiveDirectoryIterator($config->dirPlugins(), \FilesystemIterator::SKIP_DOTS);
    foreach($it as $fileInfo) {
      if (!$fileInfo->isDir()) {
        continue;
      }
      $name = $fileInfo->getFilename();

      // バージョンチェック
      $versions = [];
      $verIt = new \RecursiveDirectoryIterator($fileInfo, \FilesystemIterator::SKIP_DOTS);
      foreach($verIt as $fileInfo) {
        if (!$fileInfo->isDir()) {
          continue;
        }
        $version = $fileInfo->getFilename();
        $classPath = PluginHelper::getClassPath($name, $version);
        if (file_exists($classPath)) {
          $versions[] = $version;
        }
      }
      if (count($versions) > 0) {
        $results[$name] = ['versions' => $versions];
      }
    }
    return $results;
  }

  /**
   * phpファイルからプラグインをロードする。
   *
   * @param string $classPath
   */
  public static function loadPlugin(string $classPath)
  {
    $className = basename($classPath, '.php');
    $nameSpace = \ellsif\getNameSpace($classPath);
    if ($nameSpace) {
      $fullClassName = "\\${nameSpace}\\${className}";
    } else {
      $fullClassName = "\\${className}";
    }
    require_once $classPath;
    $obj = $fullClassName::getInstance();
    $tmp = explode('/', $classPath);
    $version = $tmp[count($tmp)-2];
    return [
      'name' => $className,
      'version' => $version,
      'author' => $obj->getAuthor(),
      'supported' => $obj->getSupported(),
      'released' => $obj->getReleased(),
      'active' => 0,
      'object' => $obj,
    ];
  }

  /**
   * プラグインのClassファイルパスを取得する。
   *
   * @param $name
   * @param $version
   * @return string
   */
  public static function getClassPath($name, $version): string
  {
    $config = Config::getInstance();
    return $config->dirPlugins() . "${name}/${version}/${name}.php";
  }
}