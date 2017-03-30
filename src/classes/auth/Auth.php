<?php

namespace ellsif\WelCMS;

abstract class Auth
{
    /**
     * 認証処理を行う。
     */
    public abstract function authenticate();

    /**
     * ハッシュ化に使うsaltを取得します。
     */
    public static function getSalt($length = 48) :string
    {
        return bin2hex(openssl_random_pseudo_bytes($length / 2));
    }

    /**
     * 一時トークンを発行します。
     */
    public static function getToken(int $expire = 3600, int $version = 0): string
    {
        // TODO 未実装
        return '';
    }

    /**
     * 一時トークンのチェックを行います。
     */
    public static function checkToken(string $token): bool
    {
        // TODO 未実装
        return false;
    }

    /**
     * ハッシュ化されたパスワードを取得します。
     */
    public static function getHashed(string $password, string $salt = null, int $version = 0) :string
    {
        if (!$salt) {
            $salt = Auth::getSalt();
        }
        $hash = hash('sha256', $password . $salt);
        return "${hash}:${salt}$${version}$";
    }

    /**
     * パスワードのチェックを行います。
     */
    public static function checkHash(string $password, string $hashstr) :bool
    {
        $ary = explode(':', $hashstr);
        if (count($ary) == 2) {
            $ary = explode('$', $ary[1]);
            if (count($ary) == 3) {
                $salt = $ary[0];
                $version = intval($ary[1]);
                $hashed = Auth::getHashed($password, $salt, $version);
                return $hashstr === $hashed;
            }
        }
        return false;
    }
}