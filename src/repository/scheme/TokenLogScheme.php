<?php

namespace ellsif\WelCMS;


class TokenLogScheme extends Scheme
{
    /**
     * 処理済みになったトークンを管理します。
     */
    public function getDefinition(): array
    {
        return [
            'token' => [
                'label'       => 'ワンタイムトークン',
                'type'        => 'string',
                'description' => 'ワンタイムトークンです。',
                'null'        => false,
            ],
            'sessid' => [
                'label'       => 'セッションID',
                'type'        => 'string',
                'description' => 'セッションIDです。',
                'null'        => false,
            ],
            'expired' => [
                'label'       => 'トークン有効期限',
                'type'        => 'string',
                'description' => 'トークンの有効期限です。',
                'null'        => false,
            ],
            'memo' => [
                'label'       => 'メモ',
                'type'        => 'text',
                'description' => '処理結果を保存します。',
                'null'        => true,
            ],
        ];
    }
}