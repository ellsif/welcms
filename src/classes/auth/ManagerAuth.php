<?php

namespace ellsif\WelCMS;


/**
 * 管理者認証クラス
 */
class ManagerAuth extends Auth
{
    /**
     * 認証処理を行います。
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['manager_id']) && $_SESSION['manager_id'];
    }
}