<?php

namespace ellsif\WelCMS;
use Valitron\Validator;

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

        $validator = new Validator($data);
        $validator->rule('required', 'userId');
        $validator->rule('required', 'password');
        if ($validator->validate()) {
            $userRepo = WelUtil::getRepository('User');
            $users = $userRepo->list(['userId' => $data['userId']]);

            if (count($users) == 0) {
                $users = $userRepo->list(['email' => $data['userId']]);
            }
            if (count($users) == 0) {
                // ログインエラー
                $result->error('Invalid Login ID');
            } else {

                // ログイン処理を行う
                $user = $users[0];
                $hash = $user['password'];

                if (Auth::checkHash($data['password'], $hash)) {
                    $_SESSION['user_id'] = $user['id'];
                    $pocket->loginUser($user);
                    WelUtil::redirect('/user');
                } else {
                    $result->error('Invalid password');
                }
            }
        } else {
            $result->error($validator->errors());
        }
        return $result;
    }

    /**
     * ログアウトします。
     */
    public function logoutUser($params)
    {
        $_SESSION['user_id'] = null;
        WelUtil::redirect('/');
    }

    /**
     * マイページを表示します。
     */
    public function indexUser($params)
    {
        return new ServiceResult();
    }
}