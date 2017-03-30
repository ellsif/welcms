<?php
namespace ellsif\WelCMS;

class SessionRepository extends Repository
{
    /**
     * カラムの定義
     *
     * ## 説明
     * リポジトリへの初回アクセス時にテーブルが存在しない場合、
     * 自動的にテーブルが作成されます。
     */
    protected $columns = [
        'sessid' => [
            'label'       => 'SESSID',
            'type'        => 'string',
            'validation'  => [
                ['rule' => 'required'],
            ],
        ],
        'data' => [
            'label'       => 'セッションデータ',
            'type'        => 'string',
        ],
    ];
}
