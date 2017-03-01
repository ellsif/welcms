<?php

namespace ellsif\WelCMS;


class PageRepository extends Repository
{
    /**
     * ユーザーに閲覧権限があるか判定する。
     */
    public function isAllowed($userId): bool
    {
        if ($userId) {

        }
        return false;
    }
}