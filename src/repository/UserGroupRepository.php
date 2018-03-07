<?php

namespace ellsif\WelCMS;

class UserGroupRepository extends Repository
{
    /**
     * カラムの定義
     *
     * ## 説明
     * リポジトリへの初回アクセス時にテーブルが存在しない場合、
     * 自動的にテーブルが作成されます。
     */
    protected $columns = [
        'name' => [
            'label'       => 'グループ名',
            'type'        => 'string',
            'description' => 'グループ名です。後から変更可能です。',
            'validation'  => [
                ['rule' => 'required'],
            ],
        ],
        'userIDs' => [
            'label'       => '所属ユーザーのID',
            'type'        => 'string',
            'description' => 'パイプ区切りで指定します（例: "|1|2|3|"）。',
            'onSave'      => '',  // 登録時にソートした上でパイプ繋ぎ
            'validation'  => [
                ['rule' => 'required'],
            ],
        ],
    ];


    /**
     * UserGroupテーブルからデータを取得する。
     */
    public function getUserGroups($userId = null): array
    {
        if ($userId && intval($userId) > 0) {
            $pocket = Pocket::getInstance();
            $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
            return $dataAccess->selectQuery(
                "SELECT * FROM UserGroup WHERE userIds LIKE :userIDs",
                ['userIDs' => "%|" . intval($userId) . "|%"]
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