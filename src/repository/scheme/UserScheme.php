<?php


namespace ellsif\WelCMS;


class UserScheme extends Scheme
{
    public function getDefinition(): array
    {
        return [
            'userId' => [
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
                'label'       => 'ユーザー情報',
                'type'        => 'text',
                'description' => '任意のユーザー情報をJSON形式で保存します。',
            ],
            'token' => [
                'label'       => 'APIトークン',
                'type'        => 'string',
                'description' => 'API呼び出し時に利用するトークンです。',
            ]
        ];
    }

}