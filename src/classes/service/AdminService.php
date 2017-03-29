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
     * アクティベーションページ用。
     */
    public function activate($params)
    {
        return new ServiceResult();
/*
        $this->requireHelpers();

        $logger = Logger::getInstance();
        $logger->log('debug', 'Activate', 'ShowActivationPage Start');

        /*
        $pocket = Pocket::getInstance();
        $this->loadView(
            $config->dirView() . 'admin/activate.php',
            [
                'config' => $config,
                'data' => $config->varFormData(),
                'urlInfo' => $config->varUrlInfo(),
            ]
        );
        $logger->log('debug', 'Activate', 'ShowActivationPage End');
*/
    }

    /**
     * ログイン処理を行う。
     *
     * @param $viewPath
     * @param $data
     */
    public function login($viewPath, $data)
    {
        $config = Pocket::getInstance();
        if ($config->varValidated() && $config->varValid()) {

            // ログイン処理を行う
            $params = $config->varFormData();
            $dataAccess = \ellsif\getDataAccess();
            $settings = $dataAccess->select('Setting');
            $hash = \ellsif\getMap($settings, 'name', 'value');

            if (\ellsif\checkHash($params['AdminPass']['value'], $hash['Hash'])) {
                $urlManager = Router::getInstance();
                $_SESSION['is_admin'] = TRUE;
                $urlManager->redirect('admin');
            } else {
                $config->varFormError(['認証に失敗しました。']);
                $this->loadView($viewPath, $data);
            }
        } else {
            $this->loadView($viewPath, $data);
        }
    }

    /**
     * 管理画面用の404ページを表示
     */
    public function show404($data = [])
    {
        $config = Pocket::getInstance();
        $this->loadView($config->dirView() . 'admin/404.php', $data);
    }

    /**
     * 管理画面ダッシュボード。
     */
    protected function index($viewPath, $data)
    {
        $this->loadView($viewPath, $data);
    }

    /**
     * 関数リファレンスを表示する。
     */
    public function getDocumentsAdmin($param)
    {
        $result = new ServiceResult();
        if (empty($param)) {
            // インデックスページを表示
            $result->view('html', Router::getViewPath('admin/documents.php'));
        } else {
            $docPath = implode('/', $param);
            $result->resultData(['docPath' => $docPath]);
            $result->view('html', Router::getViewPath('admin/documents/detail.php'));
        }
        return $result;
    }

    use AdminPageService, AdminPageTemplates, AdminPageFiles, AdminPluginService, AdminPageGroups, AdminPageUsers, AdminDatabaseService;
}