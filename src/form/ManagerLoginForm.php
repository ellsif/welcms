<?php

namespace ellsif\WelCMS;

use Valitron\Validator;

class ManagerLoginForm extends Form
{
    /**
     * フォームの送信内容の受け付け処理を行います。
     */
    protected function processSubmit(array $data): array
    {
        $managerRepo = new ManagerRepository();
        $manager = $managerRepo->first(
            'SELECT * FROM manager WHERE managerId = ?', [$data['managerId']]
        );
        if (!$manager) {
            $this->addError(null, '認証に失敗しました。ログインIDかパスワードが間違っています。');
            return $data;
        }

        $hash = $manager['password'];
        if (Auth::checkHash($data['password'], $hash)) {
            $_SESSION['manager_id'] = $data['managerId'];
            unset($manager['password']);
            welPocket()->setLoginManager($manager);
        }
        return $data;
    }

    /**
     * フォームの送信内容のバリデーションを行います。
     */
    protected function processValidate(array $data): bool
    {
        $validator = new Validator($data);

        $validator->labels([
            'managerId' => 'ログインID',
            'password' => 'パスワード',
        ]);

        $validator
            ->rule('required', ['managerId', 'password'])
            ->message('「{field}」を入力してください');

        if (!$validator->validate()) {
            foreach($validator->errors() as $name => $errors) {
                $this->setErrors($name, $errors);
            }
            return false;
        }
        return true;
    }
}