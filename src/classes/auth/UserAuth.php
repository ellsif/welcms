<?php

namespace ellsif\WelCMS;


/**
 * ユーザー認証クラス
 */
class UserAuth extends Auth
{
    /**
     * 認証処理済みかどうかを判定します。
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'];
    }

    public function getUserData(bool $secure = true)
    {

    }
}