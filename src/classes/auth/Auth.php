<?php

namespace ellsif\WelCMS;

interface Auth
{
    /**
     * 認証処理を行う。
     */
    public function authenticate();
}