<?php

namespace ellsif\WelCMS;

/**
 * ユーザー認証用コントローラ
 */
class UserService extends Service
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

        // TODO バリデーションがいる、メールアドレスでも入れるように
        if (count($users) > 0) {

            // ログイン処理を行う
            $user = $users[0];
            $hash = $user['password'];

            if (Auth::checkHash($data['password'], $hash)) {
                $_SESSION['user_id'] = $data['userId'];
                $pocket->loginUser($user);
                WelUtil::redirect('/user');
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
        WelUtil::loadView($config->dirView() . 'user/404.php', $data);
    }

    /**
     * マイページ。
     */
    public function indexUser($params)
    {
        return new ServiceResult();
    }
}