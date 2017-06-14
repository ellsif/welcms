<?php
namespace ellsif\WelCMS;


use Valitron\Validator;

class ValitronUtil
{
    // 日本語メッセージの上書き
    const MESSAGES = [
        'required' => '{field} : 必須入力です。',
        'equals' => '',
        'different' => '',
        'accepted' => '',
        'numeric' => '',
        'integer' => '',
        'boolean' => '',
        'array' => '',
        'length' => '',
        'lengthBetween' => '{field} : %d〜%d文字で入力してください。',
        'lengthMin' => '',
        'lengthMax' => '',
        'min' => '',
        'max' => '',
        'in' => '',
        'notIn' => '',
        'ip' => '',
        'email' => '{field} : 正しいメールアドレスを入力してください。',
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
    public static function getValidator($param, $validations, $labels = null, $lang = null)
    {
        $validator = new Validator($param, [], $lang);

        if ($labels) {
            $validator->labels($labels);
        }
        foreach ($validations as $name => $_validations) {
            foreach ($_validations as $_validation) {
                if (!isset($_validation['rule'])) {
                    continue;
                }
                $rule = $_validation['rule'];
                $func = $_validation['function'] ?? null;
                $message = $_validation['message'] ?? null;
                if (!$message) {
                    $message = ValitronUtil::MESSAGES[$rule] ?? null;
                }
                if (isset($_validation['function'])) {
                    if (WelUtil::isClosure($func)) {
                        Validator::addRule(
                            $rule,
                            $func,
                            $message
                        );
                    }
                }
                if (isset($_validation['option'])) {
                    $_rule = call_user_func_array([$validator, 'rule'], array_merge([$rule, $name], $_validation['option']));
                } else {
                    $_rule = $validator->rule($rule, $name);
                }
                if ($message) {
                    $_rule->message($message);
                }
            }
        }
        return $validator;
    }
}