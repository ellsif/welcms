<?php

namespace ellsif\WelCMS;


/**
 * ユーザー認証クラス
 */
class UserAuth implements Auth
{
    /**
     * 認証処理を行う。
     *
     * ## 説明
     * ログインユーザー、または管理ユーザー、システム管理ユーザー以外の場合は例外をThrowする。
     */
    public function authenticate() {
        $config = Pocket::getInstance();

        if (!$config->loginUser() && !$config->loginManager() && !$config->isAdmin()) {
            throw new \RuntimeException('Not Authorized', 401);
        }
    }
}