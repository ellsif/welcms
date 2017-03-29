<?php

namespace ellsif\WelCMS;

/**
 * ユーザー選択ダイアログを表示するWeb部品
 */
class UserSelect extends WebPart
{

  /**
   * WebPartsを初期化する。
   */
  public function initialize($options)
  {
    parent::initialize($options);

    // js追加
    $config = Pocket::getInstance();
    $config->addVarFooterJsAfter('assets/js/userSelect.js');
  }

  /**
   * Viewの表示に必要なデータを取得する。
   * usersテーブルから表示可能なユーザーの一覧を取得。
   */
  public function getData(): array
  {
    $partData = [];

    $usersModel = \ellsif\getEntity('Users');
    $partData['users'] = $usersModel->getUsers($_SESSION['is_admin']);
    return $partData;
  }
}