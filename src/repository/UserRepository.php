<?php

namespace ellsif\WelCMS;

class UserRepository extends Repository
{

    public function __construct(Scheme $scheme = null, DataAccess $dataAccess = null)
    {
        $this->scheme = $scheme ? $scheme : new UserScheme();
        $this->columns = $this->scheme->getDefinition();
        $this
            ->addModifier('info',        // TODO パスワードの変換？
                function($val) {
                    return json_encode($val, true);
                },
                function($val) {
                    return json_decode($val, true);
                }
            );
        parent::__construct($this->scheme, $dataAccess);
    }

    /**
     * usersテーブルからデータを取得する。
     */
    public function getUsers($isAdmin = false, $userId = null): array
    {
        $users = [];
        if ($isAdmin) {
            $users = $this->list(); // 管理者の場合は全件
        } else if (intval($userId) > 0) {
            // TODO likeの呼び方は現状使えない
            $userId = intval($userId);
            $users = $this->list(); // TODO 見せる範囲は同じグループのユーザー？？？？
        }
        return $users;
    }

    /**
     * ユーザーIDのリストを元にusersテーブルからデータを取得する。
     *
     * ## 引数
     * - userIds ユーザーIDの配列、またはユーザーIDのパイプ区切りの文字列（|1|2|3|）
     */
    public function getUsersByIds($userIds): array
    {
        if (!is_array($userIds)) {
            $userIds = explode('|', trim($userIds, '|'));
        }
        return $this->list(['id' => $userIds], 'id ASC');
    }

    protected function validateUniqueLoginId($value, $id)
    {
        $userId = $value ?? '';
        $userRepo = WelUtil::getRepository('User');
        $users = $userRepo->list(['userId' => $value]);
        return count($users) == 0 || $users[0]['id'] == $id;
    }

    protected function validateUniqueManagerEmail($value, $id)
    {
        $email = $value ?? '';
        $userRepo = WelUtil::getRepository('User');
        $users = $userRepo->list(['email' => $email]);
        return count($users) == 0 || $users[0]['id'] == $id;
    }

}