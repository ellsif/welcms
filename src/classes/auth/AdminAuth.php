<?php

namespace ellsif\WelCMS;


/**
 * システム管理者認証クラス
 */
class AdminAuth extends Auth
{
    /**
     * 認証処理済みかどうか。
     *
     * ## 説明
     * システム管理ユーザーログインしていない場合はログイン画面にリダイレクトします。
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    public function getUserData()
    {

    }

}