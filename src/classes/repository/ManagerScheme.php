<?php


namespace ellsif\WelCMS;


class ManagerScheme extends Scheme
{
    /**
     * 画面項目定義 + DB項目定義
     *
     * 項目名
     * 表示名
     * 説明
     * データ型
     * 初期値
     */
    public function getDefinition(): array
    {
        return [
            'managerId' => [
                'label'       => 'ログインID',
                'type'        => 'string',
                'description' => 'ログイン時に利用するidです。後からの変更は出来ません。',
                'null'        => false,
            ],
            'password' => [
                'label'       => 'パスワード',
                'type'        => 'string',
                'description' => '半角英数記号を入力してください。',
                'null'        => false,
            ],
            'name' => [
                'label'       => '名前',
                'type'        => 'string',
                'description' => '表示用の名前です。後から変更できます。',
                'null'        => false,
            ],
            'email' => [
                'label'       => 'メールアドレス',
                'type'        => 'string',
                'description' => 'メールアドレスです。idの代わりにログインに利用できます。',
                'null'        => false,
            ],
            'info' => [
                'label'       => '管理者情報',
                'type'        => 'string',
                'description' => '任意のユーザー情報をJSON形式で保存します。',
            ],
            'token' => [
                'label'       => 'APIトークン',
                'type'        => 'string',
                'description' => 'API呼び出し時に利用するトークンです。',
            ]
        ];

        /*
        return [
            'managerId' => [
                'label'       => 'ログインID',
                'type'        => 'string',
                'description' => 'ログイン時に利用するidです。後からの変更は出来ません。',
                'validation'  => [
                    ['rule' => 'required'],
                    [
                        'rule' => 'uniqueManagerId',
                        'function' => function($field, $value, array $params, array $fields) {
                            $id = $fields['id'] ?? null;
                            return $this->validateUniqueManagerId($value, $id);
                        },
                        'message' => '{field} : 既に利用されています。',
                    ],
                ],
            ],
            'password' => [
                'label'       => 'パスワード',
                'type'        => 'string',
                'description' => '半角英数記号を入力してください。',
                'onSave'      => '',  // 登録更新時に自動的にハッシュ化
                'validation'  => [
                    ['rule' => 'required'],
                    [
                        'rule' => 'lengthBetween',
                        'option' => [6, 32],
                    ],
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
                        'message' => '{field} : 既に利用されています。',
                    ],
                    ['rule' => 'email'],
                ],
            ],
            'info' => [
                'label'       => '管理者情報',
                'type'        => 'string',
                'onSave'      => 'json_encode',
                'onRead'      => 'json_decode',
                'description' => '任意のユーザー情報をJSON形式で保存します。',
            ],
            'token' => [
                'label'       => 'APIトークン',
                'type'        => 'string',
                'description' => 'API呼び出し時に利用するトークンです。',
            ]
        ];
        */
    }
}