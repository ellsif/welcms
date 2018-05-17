<?php

namespace ellsif\WelCMS;

use ellsif\util\StringUtil;

abstract class Auth
{
    /**
     * 認証が完了しているかどうかを判定します。
     */
    public abstract function isAuthenticated(): bool;

    /**
     * 認証済みの場合、ユーザー情報を取得します。
     */
    public abstract function getUserData(bool $secure = true, Repository $repo = null);

    /**
     * ログイン情報を初期化します。
     */
    public static function setLoginUsers()
    {
        if (($_SESSION['is_admin'] ?? false) === true) {
            Pocket::getInstance()->isAdmin(true);
        }
        if (isset($_SESSION['manager_id']) && $_SESSION['manager_id']) {
            $managerRepo = WelUtil::getRepository('Manager');
            $manager = $managerRepo->list(['managerId' => $_SESSION['manager_id']]);
            if (count($manager) == 1) {
                Pocket::getInstance()->loginManager($manager[0]);
            }
        }
        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
            $userRepo = WelUtil::getRepository('User');
            $user = $userRepo->get($_SESSION['user_id']);
            if ($user) {
                Pocket::getInstance()->loginUser($user);
            }
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

    /**
     * 一時トークンを発行します。
     *
     * REST APIなどに利用する予定。
     */
    public static function getToken(int $expire = 3600, int $version = 0): string
    {
        // TODO 未実装
        throw new \BadMethodCallException('getToken() is Unimplemented');
    }

    /**
     * 一時トークンのチェックを行います。
     *
     * REST APIなどに利用する予定。
     */
    public static function checkToken(string $token): bool
    {
        // TODO 未実装
        throw new \BadMethodCallException('checkToken() is Unimplemented');
    }

    /**
     * Authの名称を取得します。
     */
    public function getName(): string
    {
        $class = get_class($this);
        return StringUtil::rightRemove(substr($class, strrpos($class, '\\') + 1), 'Auth');
    }

    /**
     * 認証に失敗した場合のアクションを記述します。
     *
     * ## 説明
     * htmlとして要求された場合、ログインURLへのリダイレクトになります。<br>
     * 例）AdminAuthの場合はadmin/login
     *
     * それ以外のフォーマットの場合、401例外をthrowする。
     */
    public function onAuthError($printerFormat)
    {
        if ($printerFormat === 'html') {
            WelUtil::redirect(strtolower($this->getName()) . '/login', 302);
        } else {

        }
    }
}