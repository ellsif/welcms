<?php

namespace ellsif\WelCMS;


/**
 * ユーザー認証クラス
 */
class UserAuth extends Auth
{
    /**
     * 認証処理を行います。
     *
     * ## 説明
     * ユーザーログインしていない場合はログイン画面にリダイレクトします。
     */
    protected function doAuthenticate()
    {
        if (!Pocket::getInstance()->loginUser()) {
            throw new \RuntimeException('Not Authorized', 401);
        }
    }
}