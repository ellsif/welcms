<?php

namespace ellsif\WelCMS;


/**
 * システム管理者認証クラス
 */
class AdminAuth extends Auth
{
    /**
     * 認証処理済みかどうかを判定します。
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    public function getUserData(bool $secure = true, Repository $repo = null)
    {

    }

}