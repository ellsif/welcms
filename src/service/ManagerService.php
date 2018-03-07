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
    public function getLogin(ActionParams $params)
    {
        $result = new ServiceResult();
        return $result->addForm(new ManagerLoginForm());
    }

    /**
     * ログイン処理を行います。
     */
    public function postLogin(ActionParams $params)
    {
        $form = new ManagerLoginForm();
        $form->submit($params->post());

        if ($form->isAccepted()) {
            if (welPocket()->getRouter()->getRoute()->getType() === 'html') {
                WelUtil::redirect('/manager');
            }
            $result = new ServiceResult();
        } else {
            $result = new ServiceResult([], $form->getErrors());
        }
        return $result->addForm($form);
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