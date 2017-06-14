<?php
namespace ellsif\WelCMS;


use Valitron\Validator;

class ValidationUtil
{

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
                            Validator::addRule(
                                $rule,
                                $func,
                                $_validation['message'] ?? null
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