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

    /**
     * ユーザー情報を取得します。
     */
    public function getUserData(bool $secure = true, Repository $repo = null)
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        if (!$repo) {
            $repo = new UserRepository();
        }
        return $repo->get($_SESSION['user_id']);
    }
}