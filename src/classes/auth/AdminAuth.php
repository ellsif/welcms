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
     * システム管理ユーザーログインしていない場合はログイン画面にリダイレクトします。
     */
    protected function doAuthenticate()
    {
        if (!Pocket::getInstance()->isAdmin()) {
            throw new \RuntimeException('Not Authorized', 401);
        }
    }
}