<?php

namespace ellsif\WelCMS;

class ManagerRepository extends Repository
{
    /**
     * カラムの定義
     *
     * ## 説明
     * リポジトリへの初回アクセス時にテーブルが存在しない場合、
     * 自動的にテーブルが作成されます。
     */
    protected $columns = [
        'managerId' => [
            'label'       => '管理者ID',
            'type'        => 'string',
            'description' => 'ログインに利用するidです。後からの変更は出来ません。',
            'validation'  => [
                ['rule' => 'required'],
                ['rule' => 'unique'],
            ],
        ],
        'password' => [
            'label'       => 'パスワード',
            'type'        => 'string',
            'description' => '半角英数記号を入力してください。',
            'onSave'      => '',  // 登録更新時に自動的にハッシュ化
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
                ['rule' => 'unique'],
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

}