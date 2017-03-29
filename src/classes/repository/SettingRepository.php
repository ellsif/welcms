<?php
namespace ellsif\WelCMS;

class SettingRepository extends Repository
{
    /**
     * アクティベーションのバリデーションを行います。
     */
    public function validActivation($data, $paramName = null)
    {
        $rules = [
            'UrlHome' => ['rule' => 'required', 'msg' => 'サイトURL : 必須入力です。'],
            'SiteName' => ['rule' => 'required', 'msg' => 'サイト名 : 必須入力です。'],
            'AdminID' => ['rule' => 'required', 'msg' => '管理者ID : 必須入力です。'],
            'AdminPass' => [
                ['rule' => 'required', 'msg' => '管理者パスワード : 必須入力です。'],
                ['rule' => 'length', 'args' => [12, 4], 'msg' => '管理者パスワード : 4文字以上、12文字以内で入力してください。']
            ],
        ];
        parent::validate(__METHOD__, $data, $rules, $paramName);
    }
}
