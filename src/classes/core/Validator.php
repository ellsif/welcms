<?php

namespace ellsif\WelCMS;

// バリデーション用のクラス
class Validator {

    const ENCODE = 'utf-8';

    const DEFAULT_ERROR_MESSAGE = '入力が不正です。';

    const PREG_HIRAGANA = '/^[ぁ-ゞ]+$/u';
    const PREG_HIRAGANA_SPACE = '/^[ぁ-ゞ 　]+$/u';
    const PREG_KATAKANA = '/^[ァ-ヾ]+$/u';
    const PREG_KATAKANA_SPACE = '/^[ァ-ヾ 　]+$/u';
    const PREG_ROMAN = '/^[A-Z]+$/u';
    const PREG_ROMAN_SPACE = '/^[A-Z ]+$/u';
    const PREG_EMAIL = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i';

    /**
     * 全項目に対してバリデーションを行う
     *
     * @param array $data
     * @param array $rules
     * @param bool $isCheckAll
     * @return array
     */
    static function validAll(array $data, array $rules, bool $isCheckAll = true) :array
    {
        $results = [];
        $valid = TRUE;
        foreach($data as $name => $value) {
            if (isset($rules[$name])) {
                $_res = Validator::valid($value, $rules[$name], $isCheckAll);
                $valid = $valid && $_res['valid'];
                $results[$name] = $_res;
            } else {
                // バリデーションの設定が無い場合はOKとする
                $results[$name] = [
                    'valid' => true,
                    'value' => $value,
                    'error' => null
                ];
            }
        }
        return [
            'valid' => $valid,
            'results' => $results
        ];
    }

    /**
     * 1項目分のバリデーションを行う
     *
     * @param string $val
     * @param array $rules
     * @param bool $isCheckAll
     * @return array
     */
    static function valid($val, array $rules, bool $isCheckAll = false) :array
    {
        $result = true;
        $error = null;
        if (isset($rules['rule'])) {
            $rules = [$rules];
        }
        foreach($rules as $rule) {
            if (is_object($rule) && $rule instanceof Closure) {
                list($result, $error) = $rule($val);
            } else {
                list($result, $error) = Validator::_valid($val, $rule);
            }
            if (!$result && !$isCheckAll) {
                break;
            }
        }
        return [
            'valid' => $result,
            'value' => $val,
            'error' => $error
        ];
    }

    /**
     * 必須入力チェック
     *
     * @param string $val
     * @return bool
     */
    static function required(string $val) :bool
    {
        return Validator::isValidStr($val);
    }

    /**
     * 文字列長チェック
     *
     * @param string $val
     * @param int $max
     * @param int $min
     * @return bool
     */
    static function length(string $val, int $max = 9999, int $min = 0) :bool
    {
        return Validator::isValidStr($val) &&
        Validator::len($val) <= $max &&
        Validator::len($val) >= $min;
    }

    /**
     * 正規表現によるチェック
     *
     * @param string $val
     * @param string $preg
     * @return bool
     */
    public static function preg(string $val, string $preg) :bool
    {
        return preg_match($preg, $val);
    }

    /**
     * 正の整数かチェック（0は許可）
     */
    public static function unsignedInt($val) :bool
    {
        return is_numeric($val) && intval($val) == $val && intval($val) >= 0;
    }

    // 郵便番号
    public static function zipcode($zip1, $zip2) {
        if (!preg_match('/^\d{3}$/', $zip1)) {
            return self::err('郵便番号の前半部分は数字3桁で入力してください。');
        } else if (!preg_match('/^\d{4}$/', $zip2)) {
            return self::err('郵便番号の後半部分は数字4桁で入力してください。');
        }
        return TRUE;
    }

    // 電話番号
    static function phone($val) {

        // ハイフン無し
        if (preg_match('/^0\d{9,10}$/', $val)) {
            return TRUE;
        } else {
            $nums = explode('-', $val);
            if (count($nums) == 3) {
                if (preg_match('/^0\d{1,3}$/', $nums[0]) &&
                    preg_match('/^\d{1,4}$/', $nums[1]) &&
                    preg_match('/^\d{4}$/', $nums[2])) {

                    if (preg_match('/^0\d{9,10}$/', str_replace('-', '', $val))) {
                        return TRUE;
                    }
                }
            }
        }
        return self::err('正しい番号を入力してください。');
    }

    /**
     * １文字以上の文字列か判定
     *
     * @param string $val
     * @return bool
     */
    public static function isValidStr(string $val) :bool
    {
        return $val && is_string($val);
    }

    /**
     * validAllの結果（第2要素）からエラーメッセージの配列を取得
     *
     * @param array $data
     * @param array $errors
     * @return array
     */
    public static function getErrorMessages(array $data, array $errors = []) :array
    {
        foreach($data as $key => $tmp) {
            if (isset($tmp['error']) && $tmp['error']) {
                $errors[] = $tmp['error'];
            }
        }
        return $errors;
    }

    /**
     * 文字列の長さを取得
     *
     * @param string $str
     * @return int
     */
    private static function len(string $str) :int
    {
        $len = mb_strlen($str, Validator::ENCODE);
        if ($len !== FALSE) {
            return $len;
        }
        return -1;
    }

    /**
     * バリデーションを行う
     *
     * @param string $val
     * @param $rule
     * @return array
     * @throws \Exception
     */
    private static function _valid(string $val, $rule) :array
    {
        if (is_string($rule) && method_exists('Validator', $rule)) {
            return [Validator::$rule($val), Validator::DEFAULT_ERROR_MESSAGE];
        }
        if (is_array($rule) && isset($rule['rule'])) {
            $func = $rule['rule'];
            if (!method_exists(__NAMESPACE__ . '\Validator', $func)) {
                throw new \Exception("${func}は無効なバリデーションルールです。");
            }
            $args = [$val];
            if (isset($rule['args']) && is_array($rule['args'])) {
                $args = array_merge($args, $rule['args']);
            }
            $result = call_user_func_array(__NAMESPACE__ . '\Validator::' . $func, $args);
            if ($result) {
                return [$result, ''];
            } else {
                $error = isset($rule['msg']) ? $rule['msg'] : Validator::DEFAULT_ERROR_MESSAGE;
                return [$result, $error];
            }
        }
        throw new \Exception("バリデーションの設定が無効です。ruleが設定されていない可能性があります。");
    }

    // エラーオブジェクト生成
    private static function err($msg) {
        return array('css_class'=>self::ERROR_CSS, 'message'=>$msg);
    }

}