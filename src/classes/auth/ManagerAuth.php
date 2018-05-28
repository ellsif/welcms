<?php

namespace ellsif\WelCMS;


/**
 * 管理者認証クラス
 */
class ManagerAuth extends Auth
{
    protected $manager = null;

    /**
     * 認証処理済みかどうかを判定します。
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['manager_id']) && $_SESSION['manager_id'];
    }

    public function getUserData(bool $secure = true, Repository $repo = null)
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        if (!$this->manager) {
            if (!$repo) {
                $repo = new ManagerRepository();
            }
            $this->user = $repo->first('SELECT * FROM manager WHERE managerId = ?', [$_SESSION['manager_id']]);
        }
        return $this->manager;
    }
}