<?php

namespace ellsif\WelCMS;

use ellsif\util\StringUtil;

abstract class Auth
{

    /**
     * 認証に必要な情報を初期化します。
     */
    public function __construct()
    {
        if (isset($_SESSION['manager_id']) && $_SESSION['manager_id']) {
            $managerRepo = WelUtil::getRepository('Manager');
            $manager = $managerRepo->list(['managerId' => $_SESSION['manager_id']]);
            if (count($manager) == 1) {
                Pocket::getInstance()->loginManager($manager[0]);
            }
        }
        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
            $userRepo = WelUtil::getRepository('User');
            $user = $userRepo->list(['userId' => $_SESSION['user_id']]);
            if (count($user) == 1) {
                Pocket::getInstance()->loginUser($user[0]);
            }
        }
    }

    /**
     * 認証に失敗した場合のアクションを記述します。
     *
     * ## 説明
     * デフォルトでは所定のログインURLへのリダイレクトになります。<br>
     * 例）AdminAuthの場合はadmin/login
     */
    protected function onAuthError(\Throwable $error)
    {
        if ($error->getCode() == 401) {
            $class = get_class($this);
            $class = substr($class, strrpos($class, '\\') + 1);
            WelUtil::redirect(strtolower(StringUtil::rightRemove($class, 'Auth')) . '/login');
        } else {
            throw $error;
        }
    }

    protected abstract function doAuthenticate();

    /**
     * 認証処理を行う。
     */
    public function authenticate()
    {
        try {
            $this->doAuthenticate();
        } catch(\Throwable $e) {
            $this->onAuthError($e);
        }
    }

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