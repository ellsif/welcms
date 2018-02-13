<?php

namespace ellsif\WelCMS;


/**
 * ユーザー認証クラス
 */
class UserAuth extends Auth
{
    /**
     * 認証処理を行います。
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'];
    }
}