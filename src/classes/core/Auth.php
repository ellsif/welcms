<?php

namespace ellsif\WelCMS;

class Auth
{
    /**
     * 個別ページの認証を行う。
     *
     * ## 説明
     * 以下の場合に例外をthrowします。
     *
     * - ページが存在しない（404）
     * - ページが非公開かつ管理者ログインしていない（404）
     * - ページに認証設定されており認証されたグループでログインしていない（401）
     */
    public static function authenticatePage($page)
    {
        if (is_string($page)) {
            $pageEntity = Util::getRepository('Page');
            $pages = $pageEntity->list(['path' => trim($page, '/')]);
            $page = $pages[0] ?? null;
        }
        if ($page) {
            $userEntity = Util::getRepository('User');
            if (intval($page['published']) === 0 && $userEntity->) {
                throw new \InvalidArgumentException('Unauthorized', 401);
            }
        } else {
            throw new \InvalidArgumentException('Not Found', 404);
        }
    }

}