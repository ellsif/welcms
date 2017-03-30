<?php

namespace ellsif\WelCMS;


class PageRepository extends Repository
{
    /**
     * カラムの定義
     *
     * ## 説明
     * リポジトリへの初回アクセス時にテーブルが存在しない場合、
     * 自動的にテーブルが作成されます。
     */
    protected $columns = [
        'templateID' => [
            'label'       => 'テンプレートID',
            'type'        => 'int',
            'validation'  => [
                ['rule' => 'required'],
            ],
        ],
        'name' => [
            'label'       => 'ページ名',
            'type'        => 'string',
        ],
        'path' => [
            'label'       => 'ページのパス',
            'type'        => 'string',
            'description' => '相対URLを指定します。',
        ],
        'bodyCache' => [
            'label'       => 'キャッシュ',
            'type'        => 'text',
        ],
        'published' => [
            'label'       => '公開フラグ',
            'type'        => 'text',
            'description' => '0:非公開、1:公開、2:制限付き',
        ],
        'allowedUserGroupIds' => [
            'label'       => '許可ユーザー',
            'type'        => 'text',
            'description' => '公開フラグを「制限付き」に設定した場合、'.
                '閲覧を許可するユーザーグループのIDです。パイプ区切りで指定します。（例: "|1|2|3|"）',
        ],
        'options' => [
            'label'       => 'オプション設定',
            'type'        => 'text',
            'description' => 'JSON文字列として設定されます。',
        ],
    ];
}