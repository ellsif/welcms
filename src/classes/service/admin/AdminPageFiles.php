<?php

namespace ellsif\WelCMS;
use ellsif\Form;

/**
 * Class AdminPageFiles
 * @package ellsif\WelCMS
 */
trait AdminPageFiles
{
  /**
   * ファイル管理
   *
   * @param $viewPath viewファイルのpath（ただしindex）
   * @param array $data URLのpages/以降が入る（pages/edit/1の場合、[1]）
   */
  protected function files($viewPath, $data)
  {
    $config = Config::getInstance();

    $config->varAction('admin/files');  // TODO いらないかも
    $action = $data[0] ?? 'index';

    if ($action === 'add'){
      return $this->filesAdd($viewPath);
    } else if ($action === 'index') {
      return $this->filesShowIndex($viewPath);
    }
    return false;
  }

  /**
   * 個別ページ一覧を表示
   *
   * @param $viewPath
   * @return bool
   */
  private function filesShowIndex($viewPath): bool
  {
    $fileAccess = \ellsif\getFileAccess();

    $containers = $fileAccess->list();
    $files = [];
    foreach($containers as $container) {
      $files[$container] = $fileAccess->list($container);
    }

    $data = [];
    $data['files'] = $files;
    $this->loadView($viewPath, $data);
    return true;
  }

  /**
   * ファイルをアップロードする
   *
   * @param $viewPath
   * @return bool
   */
  private function filesAdd($viewPath): bool
  {
    $fileAccess = \ellsif\getFileAccess();

    if(is_uploaded_file($_FILES['file']['tmp_name'])) {
      $fp = fopen($_FILES['file']['tmp_name'], 'r');
      if ($fp) {
        $savePath = $_FILES['file']['name'];

        $result = $fileAccess->create($fp, $savePath/*, ['prefix' => 'template/ellsif/']*/);
        echo $result;

        fclose($fp);
      }
    }
    return true;
  }
}