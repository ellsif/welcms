<?php

namespace ellsif\WelCMS;

class UserGroups extends Repository
{
  /**
   * userGroupsテーブルからデータを取得する。
   */
  private function _getUserGroups($isAdmin = false, $userId = null): array
  {
    if ($isAdmin) {
      // 全件取得
      $dataAccess = \ellsif\getDataAccess();
      return $dataAccess->select('userGroups');
    }
    if ($userId && intval($userId) > 0) {
      // 自分の所属のみ取得
      $dataAccess = \ellsif\getDataAccess();
      return $dataAccess->selectQuery("SELECT * FROM userGroups WHERE userIds LIKE '%|:userId|%'", ['userId' => intval($userId)]);
    }
    return [];
  }

  /**
   * userGroupsテーブルにデータを登録または更新する。
   * TODO トランザクションが必要かな。。。
   * TODO バリデーションも必要だな。。。
   */
  private function _saveUserGroups($groups): bool
  {
    $saved = false;
    if (is_array($groups)) {
      foreach($groups as $group) {
        $dataAccess = \ellsif\getDataAccess();
        if (isset($group['id']) && is_numeric($group['id'])) {
          // 更新
          if ($dataAccess->update('userGroups', $group['id'], $group)) {
            $saved = true;
          }
        } else {
          // 登録
          $id = $dataAccess->insert('userGroups', $group);
          if ($id > 0) {
            $saved = true;
          }
        }
      }
    }
    return $saved;
  }
}