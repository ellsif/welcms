<?php

namespace ellsif\WelCMS;

use ellsif\Logger;

/**
 * 管理画面表示用コントローラ
 */
class AdminService extends Service
{

    /**
     * 管理者認証処理を行う。
     * 基底クラスのshow()メソッドを経由した場合のみ、ここの処理を通る。
     * ログイン画面などはshow()を経由せず直接login()を呼ぶ。
     *
     * @param $param
     * @return bool
     */
    public function authenticate($param) :bool
    {
        //
        $config = Pocket::getInstance();
        $auth = $_SESSION['is_admin'] === TRUE;

        return $auth;
    }

    /**
     * アクティベーションページを表示します。
     */
    public function activate($params)
    {
        if (Pocket::getInstance()->settingActivated()) {
            WelUtil::redirect('admin/');
        }
        return new ServiceResult();
    }

    /**
     * アクティベーションを行います。
     */
    public function postActivate($params)
    {
        if (Pocket::getInstance()->settingActivated()) {
            WelUtil::redirect('admin/');
        }

        $result = new ServiceResult();

        $data = $_POST;
        $settingRepo = WelUtil::getRepository('Setting');
        $settingRepo->validateActivation($data);
        Logger::getInstance()->log('debug', 'activate',
            Pocket::getInstance()->varValid() ? 'valid' : 'invalid ' . json_encode(Pocket::getInstance()->varFormData()));
        if (Pocket::getInstance()->varValid()) {
            // アクティベーション処理
            $settingRepo->activation($data['urlHome'], $data['siteName'], $data['adminID'], $data['adminPass']);
            // ログインして管理画面へ
            $_SESSION['is_admin'] = true;
            WelUtil::redirect('admin/');
        }
        return $result;
    }

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

        $settingRepo = WelUtil::getRepository('Setting');
        $settings = $settingRepo->list(['name' => 'adminPass']);

        // TODO バリデーションがいる
        if (count($settings) > 0 && isset($settings[0]['value'])) {

            // ログイン処理を行う
            $hash = $settings[0]['value'];

            if (Auth::checkHash($data['adminPass'], $hash)) {
                $_SESSION['is_admin'] = TRUE;
                WelUtil::redirect('/admin');
            } else {
                $pocket->varFormError(['認証に失敗しました。']);
            }
        } else {
            throw new Exception('認証情報の取得に失敗しました。設定を見直して下さい。');
        }
        return $result;
    }

    /**
     * 管理画面用の404ページを表示
     */
    public function show404($data = [])
    {
        $config = Pocket::getInstance();
        WelUtil::loadView($config->dirView() . 'admin/404.php', $data);
    }

    /**
     * 管理画面ダッシュボードを表示する。
     */
    public function getIndexAdmin($params)
    {
        return new ServiceResult();
    }

    /**
     * 関数リファレンスを表示する。
     */
    public function getDocumentsAdmin($param)
    {
        $result = new ServiceResult();
        if ($param) {
            $docPath = implode('/', $param);
            $result->resultData(['docPath' => $docPath]);
            $result->setView(Router::getViewPath('admin/documents/detail.php'));
        }
        return $result;
    }

    /**
     * 管理者アカウント管理
     */
    public function getManagerAdmin($param)
    {
        $result = new ServiceResult();
        $managerRepo = WelUtil::getRepository('Manager');
        $result->resultData([
            'managers' => $managerRepo->list()
        ]);
        return $result;
    }

    use AdminPageService, AdminPageTemplates, AdminPageFiles, AdminPluginService, AdminPageGroups, AdminDatabaseService;
}