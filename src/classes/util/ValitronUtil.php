<?php
namespace ellsif\WelCMS;


use Valitron\Validator;

class ValitronUtil
{
    // 日本語メッセージの対応
    protected const MESSAGES = [
        'required' => ' : 必須入力です。',
        'equals' => '',
        'different' => '',
        'accepted' => '',
        'numeric' => '',
        'integer' => '',
        'boolean' => '',
        'array' => '',
        'length' => '',
        'lengthBetween' => '',
        'lengthMin' => '',
        'lengthMax' => '',
        'min' => '',
        'max' => '',
        'in' => '',
        'notIn' => '',
        'ip' => '',
        'email' => '',
        'url' => '',
        'urlActive' => '',
        'alpha' => '',
        'alphaNum' => '',
        'slug' => '',
        'regex' => '',
        'date' => '',
        'dateFormat' => '',
        'dateBefore' => '',
        'dateAfter' => '',
        'contains' => '',
        'creditCard' => '',
        'instanceOf' => '',
        'optional' => '',
    ];

    /**
     * Valitronバリデータオブジェクトを取得します。
     */
    public static function getValidator($param, $validations, $labels = null)
    {
        $validator = new Validator($param);

        if ($labels) {
            $validator->labels($labels);
        }
        foreach ($validations as $name => $_validations) {
            foreach ($_validations as $_validation) {
                if (isset($_validation['rule'])) {
                    $rule = $_validation['rule'];
                    if (isset($_validation['function'])) {
                        $func = $_validation['function'];
                        if (WelUtil::isClosure($func)) {
                            $message = $_validation['message'] ?? null;
                            if (!$message) {
                                $message = ValitronUtil::MESSAGES[$rule] ?? null;
                            }
                            Validator::addRule(
                                $rule,
                                $func,
                                $message
                            );
                        }
                    }
                    if (isset($_validation['option'])) {
                        $validator->rule($rule, $name, $_validation['option']);
                    } else {
                        $validator->rule($rule, $name);
                    }
                }
            }
        }
        return $validator;
    }
}