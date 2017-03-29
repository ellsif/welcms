<?php

namespace ellsif\WelCMS;


/**
 * システム管理者認証クラス
 */
class AdminAuth implements Auth
{
    /**
     * 認証処理を行う。
     *
     * ## 説明
     * システム管理ユーザー以外の場合は例外をThrowする。
     */
    public function authenticate() {
        $config = Pocket::getInstance();

        if (!$config->isAdmin()) {
            throw new \RuntimeException('Not Authorized', 401);
        }
    }
}