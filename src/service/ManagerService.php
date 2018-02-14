<?php

namespace ellsif\WelCMS;

/**
 * 管理画面表示用コントローラ
 */
class ManagerService extends Service
{

    /**
     * ログイン画面を表示します。
     */
    public function getLogin($params)
    {
        return new ServiceResult();
    }

    /**
     * ログイン処理を行います。
     */
    public function postLogin($params)
    {
        $result = new ServiceResult();

        $data = $_POST;
        $pocket = Pocket::getInstance();

        $managerRepo = WelUtil::getRepository('Manager');
        $managers = $managerRepo->list(['managerId' => $data['managerId']]);

        // TODO バリデーションがいる
        if (count($managers) > 0) {

            // ログイン処理を行う
            $manager = $managers[0];
            $hash = $manager['password'];

            if (Auth::checkHash($data['password'], $hash)) {
                $_SESSION['manager_id'] = $data['managerId'];
                $pocket->loginManager($manager);
                WelUtil::redirect('/manager');
            }
        }
        // TODO エラーの処理方法は・・・？
        $pocket->varFormError(['認証に失敗しました。']);
        return $result;
    }

    /**
     * 管理画面ダッシュボード。
     */
    public function getIndexManager($params)
    {
        return new ServiceResult();
    }

    /**
     * マネージャー画面用の404ページを表示
     */
    public function show404($data = [])
    {
        $config = Pocket::getInstance();
        WelUtil::loadView($config->dirView() . 'manager/404.php', $data);
    }

    /**
     * ログアウトします。
     */
    public function getLogoutManager($params)
    {
        $_SESSION['manager_id'] = null;
        WelUtil::redirect('/');
    }
}