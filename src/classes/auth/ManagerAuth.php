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
        return !(Pocket::getInstance()->loginManager());
    }
}