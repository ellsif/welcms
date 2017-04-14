<?php

namespace ellsif\WelCMS;
use ellsif\Form;

/**
 * ユーザー管理
 */
trait AdminPageUsers
{
  protected function users($viewPath, $data)
  {
    $config = Pocket::getInstance();
    $action = $data[0] ?? 'index';
    if ($action === 'regist') {
      $viewPath = $config->dirView() . '/admin/api/json.php';
      $result = ['success' => false, 'message' => 'APIの呼び出しに失敗しました。'];

      $salt = \ellsif\getSalt();
      $hash = \ellsif\getHashed($_POST['password'], $salt, 1);
      $user = [
        'name' => $_POST['name'] ?? '',
        'hashed' => $hash ?? '',
        'userId' => $_POST['userId'] ?? '',
        'email' => $_POST['email'] ?? '',
      ];
      $usersModel = \ellsif\getEntity('Users');
      if ($usersModel->save([$user])) {
        $result['success'] = true;
        $result['message'] = '適用しました。';
      } else {
        // エラー
      }
      WelUtil::loadView($viewPath, ['data' => ['result' => $result, 'data' => ['name' => $name]]]);
      return true;
    } else if ($action === 'index') {
      return $this->usersShowIndex($viewPath);
    }
    return false;
  }

  /**
   * ユーザー一覧を表示
   */
  private function usersShowIndex($viewPath): bool
  {
    $usersModel = \ellsif\getEntity('Users');
    $pageData = [];
    if ($_SESSION['is_admin']) {
      $pageData['users'] = $usersModel->list();
    } else {
      // TODO likeの呼び方は現状使えない
      $userId = intval('');
      $pageData['users'] = $usersModel->list(); // TODO 見せる範囲は同じグループのユーザー？？？？
    }
    WelUtil::loadView($viewPath, $pageData);
    return true;
  }
}