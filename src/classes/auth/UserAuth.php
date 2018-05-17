<?php

namespace ellsif\WelCMS;


/**
 * ユーザー認証クラス
 */
class UserAuth extends Auth
{
    protected $user = null;

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
        if (!$this->user) {
            if (!$repo) {
                $repo = new UserRepository();
            }
            $this->user = $repo->get($_SESSION['user_id']);
        }
        return $this->user;
    }
}