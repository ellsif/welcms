<?php

namespace ellsif\WelCMS;

use ellsif\Logger;
use Valitron\Validator;

/**
 * 管理画面表示用コントローラ
 */
class AdminService extends Service
{
    /**
     * アクティベーションページを表示します。
     */
    public function getActivate($params)
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
        $settingRepo = WelUtil::getRepository('Setting');
        $settingPass = $settingRepo->list(['name' => 'adminPass']);
        $settingId = $settingRepo->list(['name' => 'adminID']);

        if (count($settingPass) > 0 && isset($settingPass[0]['value']) &&
            count($settingId) > 0 && isset($settingId[0]['value'])) {
            $validator = new Validator($data);
            $validator->labels([
                'adminID' => '管理者ID',
                'adminPass' => '管理者パスワード',
            ]);
            $validator->rule('required', ['adminID', 'adminPass'])->message('{field} : 必須入力です。');
            if (!$validator->validate()) {
                $result->error($validator->errors());
                $result->resultData([
                    'adminID' => $data['adminID'],
                    'adminPass' => $data['adminPass'],
                ]);
                return $result;
            }
            // ログイン処理を行う
            $hash = $settingPass[0]['value'];
            if ($data['adminID'] == $settingId[0]['value'] && Auth::checkHash($data['adminPass'], $hash)) {
                $_SESSION['is_admin'] = TRUE;
                WelUtil::redirect('/admin');
            } else {
                Pocket::getInstance()->addFlash('認証に失敗しました。', 'danger');
            }
        } else {
            throw new \RuntimeException('認証情報の取得に失敗しました。設定を見直して下さい。');
        }
        return $result;
    }

    /**
     * ログアウトします。
     */
    public function getLogoutManager($params)
    {
        $_SESSION['is_admin'] = null;
        WelUtil::redirect('/');
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
    // TODO 修正が必要
    /*
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
    */

    /**
     * マネージャーアカウント管理画面を表示します。
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

    /**
     * マネージャーアカウント登録を行います。
     */
    public function postManagerAdmin($param)
    {
        $result = new ServiceResult();
        $manager = $_POST['Manager'] ?? null;
        $managerRepo = WelUtil::getRepository('Manager');
        if (!$manager) {
            throw new \InvalidArgumentException('パラメータが不正です。', 404);
        }
        $validator = ValitronUtil::getValidator(
            $manager,
            $managerRepo->getValidationRules(),
            $managerRepo->getLabels(),
            'ja'
        );
        if ($validator->validate()) {
            $manager['password'] = Auth::getHashed($manager['password']);
            $managerRepo->save([$manager]);
            WelUtil::redirect('admin/manager');
        } else {
            $result->error($validator->errors());
            $result->resultData([
                'managers' => $managerRepo->list(),
                'manager' => $manager,
            ]);
        }
        return $result;
    }

    // use AdminPageService, AdminPageTemplates, AdminPageFiles, AdminPluginService, AdminPageGroups, AdminDatabaseService;
}