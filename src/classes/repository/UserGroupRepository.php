<?php

namespace ellsif\WelCMS;

class UserGroupRepository extends Repository
{
    /**
     * UserGroupテーブルからデータを取得する。
     */
    public function getUserGroups($userId = null): array
    {
        if ($userId && intval($userId) > 0) {
            $pocket = Pocket::getInstance();
            $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
            return $dataAccess->selectQuery(
                "SELECT * FROM UserGroup WHERE userIds LIKE :userId",
                ['userId' => "%|" . intval($userId) . "|%"]
            );
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