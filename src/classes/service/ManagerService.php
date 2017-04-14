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
    public function login($params)
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

        $userRepo = WelUtil::getRepository('User');
        $users = $userRepo->list(['userId' => $data['userId']]);

        // TODO バリデーションがいる
        if (count($users) > 0) {

            // ログイン処理を行う
            $user = $users[0];
            $hash = $user['password'];

            if (Auth::checkHash($data['password'], $hash)) {
                $_SESSION['user_id'] = $data['userId'];
                WelUtil::redirect('/manager');
            }
        }
        // TODO エラーの処理方法は・・・？
        $pocket->varFormError(['認証に失敗しました。']);
        return $result;
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
     * 管理画面ダッシュボード。
     */
    public function index($params)
    {
        return new ServiceResult();
    }
}