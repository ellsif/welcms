<?php

namespace ellsif\WelCMS;

class ManagerRepository extends Repository
{
    public function __construct($name)
    {
        /**
         * カラムの定義
         *
         * ## 説明
         * リポジトリへの初回アクセス時にテーブルが存在しない場合、
         * 自動的にテーブルが作成されます。
         *
         *
         */
        $scheme = new ManagerScheme();
        $this->columns = $scheme->getDefinition();
        parent::__construct($name);
    }

    protected function validateUniqueManagerId($value, $id)
    {
        $managerId = $value ?? '';
        $managerRepo = WelUtil::getRepository('Manager');
        $managers = $managerRepo->list(['managerId' => $managerId]);
        return count($managers) == 0 || $managers[0]['id'] == $id;
    }

    protected function validateUniqueManagerEmail($value, $id)
    {
        $email = $value ?? '';
        $managerRepo = WelUtil::getRepository('Manager');
        $managers = $managerRepo->list(['email' => $email]);
        return count($managers) == 0 || $managers[0]['id'] == $id;
    }
}