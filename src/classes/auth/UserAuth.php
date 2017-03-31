<?php

namespace ellsif\WelCMS;


/**
 * ユーザー認証クラス
 */
class UserAuth extends Auth
{
    /**
     * 認証処理を行う。
     *
     * ## 説明
     * ログインユーザー、または管理ユーザー、システム管理ユーザー以外の場合はログイン画面にリダイレクト。
     */
    protected function doAuthenticate(): bool
    {
        $config = Pocket::getInstance();

        return ($config->loginUser() || $config->loginManager() || $config->isAdmin());
    }
}