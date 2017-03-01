<?php

namespace ellsif\WelCMS;

/**
 * ユーザーグループの管理用関数
 * 管理画面、APIでの利用を想定。
 * モデルか・・？
 */
class User extends Repository
{
  /**
   * usersテーブルからデータを取得する。
   */
  public function getUsers($isAdmin = false, $userId = null): array
  {
    $users = [];
    if ($isAdmin) {
      $users = $this->list(); // 管理者の場合は全件
    } else if (intval($userId) > 0) {
      // TODO likeの呼び方は現状使えない
      $userId = intval($userId);
      $users = $this->list(); // TODO 見せる範囲は同じグループのユーザー？？？？
    }
    return $users;
  }

  /**
   * ユーザーIDのリストを元にusersテーブルからデータを取得する。
   *
   * ## 引数
   * - userIds ユーザーIDの配列、またはユーザーIDのパイプ区切りの文字列（|1|2|3|）
   */
  public function getUsersByIds($userIds): array
  {
    if (!is_array($userIds)) {
      $userIds = explode('|', trim($userIds, '|'));
    }
    return $this->list(['id' => $userIds], 'id ASC');
  }

}