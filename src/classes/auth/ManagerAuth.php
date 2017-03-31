<?php

namespace ellsif\WelCMS;


/**
 * 管理者認証クラス
 */
class ManagerAuth extends Auth
{
    /**
     * 認証処理を行う。
     *
     * ## 説明
     * 管理ユーザー、システム管理ユーザー以外の場合は例外をThrowする。
     */
    protected function doAuthenticate(): bool
    {
        $config = Pocket::getInstance();

        if (!$config->loginManager() && !$config->isAdmin()) {
            throw new \RuntimeException('Not Authorized', 401);
        }
    }
}