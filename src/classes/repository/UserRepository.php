<?php

namespace ellsif\WelCMS;

class UserRepository extends Repository
{

    public function __construct($name)
    {
        /**
         * カラムの定義
         *
         * ## 説明
         * リポジトリへの初回アクセス時にテーブルが存在しない場合、
         * 自動的にテーブルが作成されます。
         */
        $this->columns = [
            'userId' => [
                'label'       => 'ユーザーID',
                'type'        => 'string',
                'description' => 'ログインに利用するidです。後からの変更は出来ません。',
                'validation'  => [
                    ['rule' => 'required'],
                    'function' => function($field, $value, array $params, array $fields) {
                        $id = $fields['id'] ?? null;
                        return $this->validateUniqueLoginId($value, $id);
                    },
                ],
            ],
            'password' => [
                'label'       => 'パスワード',
                'type'        => 'string',
                'description' => '半角英数記号を入力してください。',
                'onSave'      => '',  // 登録更新時に自動的にハッシュ化する？
                'validation'  => [
                    ['rule' => 'required'],
                ],
            ],
            'name' => [
                'label'       => '名前',
                'type'        => 'string',
                'description' => '表示用の名前です。後から変更できます。',
                'validation'  => [
                    ['rule' => 'required'],
                ],
            ],
            'email' => [
                'label'       => 'メールアドレス',
                'type'        => 'string',
                'description' => 'メールアドレスです。idの代わりにログインに利用できます。',
                'validation'  => [
                    ['rule' => 'required'],
                    [
                        'rule' => 'uniqueManagerEmail',
                        'function' => function($field, $value, array $params, array $fields) {
                            $id = $fields['id'] ?? null;
                            return $this->validateUniqueManagerEmail($value, $id);
                        },
                        'message' => 'is already in use',
                    ],
                    ['rule' => 'email'],
                ],
            ],
            'info' => [
                'label'       => 'ユーザー情報',
                'type'        => 'string',
                'onSave'      => '\json_encode',
                'onLoad'      => '\ellsif\WelCMS\Repository::json_decode',
                'description' => '任意のユーザー情報をJSON形式で保存します。',
            ],
            'token' => [
                'label'       => 'APIトークン',
                'type'        => 'string',
                'description' => 'API呼び出し時に利用するトークンです。',
            ]
        ];
        parent::__construct($name);
    }

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

    protected function validateUniqueLoginId($value, $id)
    {
        $userId = $value ?? '';
        $userRepo = WelUtil::getRepository('User');
        $users = $userRepo->list(['userId' => $value]);
        return count($users) == 0 || $users[0]['id'] == $id;
    }

    protected function validateUniqueManagerEmail($value, $id)
    {
        $email = $value ?? '';
        $userRepo = WelUtil::getRepository('User');
        $users = $userRepo->list(['email' => $email]);
        return count($users) == 0 || $users[0]['id'] == $id;
    }

}