<?php

namespace ellsif\WelCMS;


/**
 * システム管理者認証クラス
 */
class AdminAuth extends Auth
{
    /**
     * 認証処理を行います。
     *
     * ## 説明
     * システム管理ユーザー以外のみ許可します。
     */
    protected function doAuthenticate()
    {
        $config = Pocket::getInstance();

        if (!$config->isAdmin()) {
            throw new \RuntimeException('Not Authorized', 401);
        }
    }
}