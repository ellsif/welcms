<?php

namespace ellsif\WelCMS;
use ellsif\Form;

/**
 * プラグインの管理に関連するActionのTrait。
 *
 * ## 説明
 * AdminServiceにより利用されます。
 */
trait AdminPluginService
{
  /**
   * プラグイン情報を取得する。
   *
   * ## 説明
   * プラグイン情報のリスト、または指定idに対応するデータを取得します。
   */
  public function getPluginAdmin($param = [])
  {
    // DB登録済みのプラグイン一覧を取得
    $plugins = PluginHelper::getPlugins(false);

    // DB未登録(plugins/以下から)のプラグイン一覧を取得
    $dirPlugins = PluginHelper::getPluginsByDir();

    // プラグイン一覧をマージする
    $pluginNames = array_keys($plugins) + array_keys($dirPlugins);
    $pluginData = [];
    foreach($pluginNames as $pluginName) {
      $existsDB = array_key_exists($pluginName, $plugins);
      $existsDir = array_key_exists($pluginName, $dirPlugins);
      if ($existsDB && $existsDir) {

        // DBとディレクトリ両方存在する場合、ディレクトリ側のバージョンリストを優先する
        $_plugin = $plugins[$pluginName];
        $_plugin['versions'] = $dirPlugins[$pluginName]['versions'];

        // currentに指定されたプラグインのディレクトリが存在しない場合はエラー
        if ($_plugin['current']) {
          if (!in_array($_plugin['current']['version'], $_plugin['versions'])) {
            $pluginData[$pluginName] = [
              'status' => false,
              'message' => 'プラグインディレクトリにプラグインが存在しません。',
              'plugin' => $plugins[$pluginName]
            ];
            continue;
          }
        }

        // latestに指定されたプラグインが存在しない場合は存在するものからlatestを更新
        if ($_plugin['latest']) {
          if ($_plugin['latest']['version'] !== $_plugin['versions'][0]) {
            $_plugin['latest'] = PluginHelper::loadPlugin(PluginHelper::getClassPath($pluginName, $_plugin['versions'][0]));
          }
        }
        $pluginData[$pluginName] = [
          'status' => true,
          'plugin' => $_plugin,
        ];
      } elseif ($existsDB && !$existsDir) {

        // DBには存在するが実体が存在しない場合はエラー（インストール後にファイルが削除された可能性）
        $pluginData[$pluginName] = [
          'status' => false,
          'message' => 'プラグインディレクトリにプラグインが存在しません。',
          'plugin' => $plugins[$pluginName]
        ];
      } elseif (!$existsDB && $existsDir) {

        // 実体しか存在しない場合はlatestをロード（インストール前
        $_plugin = $dirPlugins[$pluginName];
        $_plugin['current'] = null;
        $_plugin['latest'] = PluginHelper::loadPlugin(PluginHelper::getClassPath($pluginName, $_plugin['versions'][0]));
        $pluginData[$pluginName] = [
          'status' => true,
          'plugin' => $_plugin,
        ];
      }
    }

    $result = new ServiceResult();
    $result->resultData(['plugins' => $pluginData]);
    return $result;
  }

  /**
   * プラグインの状態を更新する。
   *
   * ## 説明
   * プラグインの有効/無効切り替え、バージョンの切り替えを行います。
   */
  public function postPluginAdmin($param = [])
  {
    $id = \ellsif\getPost('id');
    $name = \ellsif\getPost('name');
    $active = \ellsif\getPost('active');
    $version = \ellsif\getPost('version');

    if ($active == 1) {
      $activateResult = $this->pluginsSetActive($id, $name, $version);
    } else {
      $activateResult = $this->pluginsSetActive($id, $name, $version, false);
    }

    $result = new ServiceResult();
    $result->resultData($activateResult);
    return $result;
  }

  /**
   * プラグインを有効化
   *
   * @return bool
   */
  protected function pluginsSetActive($id, $name, $version, bool $active = true)
  {
    $intActive = $active ? 1 : 0;
    $pluginEntity = \ellsif\getEntity('Plugin');

    $result = [];

    if ($id) {
      // 更新
      $plugin = $pluginEntity->save([[
        'id' => $id,
        'name' => $name,
        'version' => $version,
        'active' => $intActive,
      ]]);
      $result['success'] = true;
      $result['message'] = $name . 'を更新しました。';
    } else {
      // 新規登録
      $classPath = PluginHelper::getClassPath($name, $version);
      $plugin = PluginHelper::loadPlugin($classPath);
      unset($plugin['object']);
      $plugin['active'] = $intActive;
      $id = $pluginEntity->save($plugin);
      if ($id) {
        $result['success'] = true;
        $result['message'] = $name . 'を有効にしました。';
      }
    }
    return $result;
  }
}