<?php

namespace ellsif\WelCMS;


/**
 * 管理者認証クラス
 */
class ManagerAuth extends Auth
{
    /**
     * 認証処理済みかどうかを判定します。
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['manager_id']) && $_SESSION['manager_id'];
    }

    public function getUserData(bool $secure = true)
    {

    }
}