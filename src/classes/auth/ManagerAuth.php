<?php

namespace ellsif\WelCMS;


/**
 * 管理者認証クラス
 */
class ManagerAuth extends Auth
{
    /**
     * 認証処理を行います。
     *
     * ## 説明
     * 管理ユーザーログインしていない場合はログイン画面にリダイレクトします。
     */
    protected function doAuthenticate()
    {
        if (!Pocket::getInstance()->loginManager()) {
            throw new \RuntimeException('Not Authorized', 401);
        }
    }
}