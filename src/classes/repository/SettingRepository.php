<?php
namespace ellsif\WelCMS;

use ellsif\Logger;

class SettingRepository extends Repository
{
    /**
     * カラムの定義
     *
     * ## 説明
     * リポジトリへの初回アクセス時にテーブルが存在しない場合、
     * 自動的にテーブルが作成されます。
     */
    protected $columns = [
        'contentType' => [
            'label'       => 'コンテンツタイプ',
            'type'        => 'string',
            'default'     => 'text/plain',
            'description' => "コンテンツのMIME-TYPEを指定します。デフォルトでは'text/plain'となります。" .
                             "'application/json'などを想定しています。",
            'validation'  => [
                ['rule' => 'required'],
            ],
        ],
        'valueType' => [
            'label'       => '値タイプ',
            'type'        => 'string',
            'description' => "コンテンツの設定値が意味する型を指定します。自由入力です。" .
                             "ファイルパスなどを指定する場合は'path'、テキストの場合は'text'などを設定します。",
        ],
        'label' => [
            'label'       => 'ラベル',
            'type'        => 'string',
            'description' => "項目の表示名です。",
            'validation'  => [
                ['rule' => 'required'],
            ],
        ],
        'name' => [
            'label'       => '項目名',
            'type'        => 'string',
            'description' => "項目の識別名です。inputタグのnameなどに指定します。半角英数が望ましいです。",
            'validation'  => [
                'rules' => 'required',
            ],
        ],
        'value' => [
            'label'       => '設定値',
            'type'        => 'text',
            'description' => "項目の設定値です。特に制限はありません。",
        ],
        'options' => [
            'label'       => 'オプション',
            'type'        => 'text',
            'onSave'      => 'json_encode',
            'onRead'      => 'json_decode',
            'description' => "オプション項目です。JSON形式の文字列として保存されます。",
        ],
    ];

    /**
     * アクティベーション時のバリデーションを行います。
     */
    public function validateActivation($data, $paramName = null)
    {
        $rules = [
            'urlHome' => ['rule' => 'required', 'msg' => 'サイトURL : 必須入力です。'],
            'siteName' => ['rule' => 'required', 'msg' => 'サイト名 : 必須入力です。'],
            'adminID' => ['rule' => 'required', 'msg' => '管理者ID : 必須入力です。'],
            'adminPass' => [
                ['rule' => 'required', 'msg' => '管理者パスワード : 必須入力です。'],
                ['rule' => 'length', 'args' => [12, 4], 'msg' => '管理者パスワード : 4文字以上、12文字以内で入力してください。'],
            ],
        ];
        $logger = Logger::getInstance();
        $logger->log('debug', 'activation', json_encode($data));
        $validationResult = Validator::validAll($data, $rules);
        Pocket::getInstance()->varValid($validationResult['valid']);
        Pocket::getInstance()->varFormData($validationResult['results']);
        // parent::validate(__METHOD__, $data, $rules, $paramName);
    }

    /**
     * アクティベーションを行います。
     *
     * ## 説明
     */
    public function activation($urlHome, $siteName, $adminID, $adminPass)
    {
        $this->save([
            [
                'conntentType' => 'text/plain',
                'valueType' => 'text',
                'label' => 'アクティベーション',
                'name' => 'activate',
                'value' => 1,
                'useInPage' => 0,
            ],
            [
                'conntentType' => 'text/plain',
                'valueType' => 'text',
                'label' => 'サイトURL',
                'name' => 'urlHome',
                'value' => $urlHome,
                'useInPage' => 1,
            ],
            [
                'conntentType' => 'text/plain',
                'valueType' => 'text',
                'label' => 'サイト名',
                'name' => 'siteName',
                'value' => $siteName,
                'useInPage' => 1,
            ],
            [
                'conntentType' => 'text/plain',
                'valueType' => 'text',
                'label' => '管理者ID',
                'name' => 'adminID',
                'value' => $adminID,
                'useInPage' => 1,
            ],
            [
                'conntentType' => 'text/plain',
                'valueType' => 'text',
                'label' => '管理者パスワード',
                'name' => 'adminPass',
                'value' => $adminPass,
                'useInPage' => 0,
            ]
        ]);
    }
}
