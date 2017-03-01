<?php

namespace ellsif\WelCMS;

/**
 * Class AdminPageGroups
 * @package ellsif\WelCMS
 */
trait AdminPageGroups
{
  /**
   * ユーザーグループ管理
   *
   * @param $viewPath viewファイルのpath（ただしindex）
   * @param array $data URLのgroups/以降が入る（groups/edit/1の場合、[1]）
   */
  protected function groups($viewPath, $data)
  {
    $config = Config::getInstance();

    $action = $data[0] ?? 'index';
    if ($action === 'regist') {
      $viewPath = $config->dirView() . '/admin/api/json.php';
      $result = ['success' => false, 'message' => 'APIの呼び出しに失敗しました。'];

      $group = [
        'id' => $_POST['id'] ?? null,
        'name' => $_POST['name'],
        'userIds' => '|' . implode('|', $_POST['user_id']) . '|',
      ];

      $userGroupsModel = \ellsif\getEntity('UserGroups');
      if ($userGroupsModel->save([$group])) {
        $result['success'] = true;
        $result['message'] = '適用しました。';
      } else {
        // エラー
      }
      $this->loadView($viewPath, ['data' => ['result' => $result, 'data' => ['name' => $_POST['name']]]]);
      return true;
    } else if ($action === 'index') {
      return $this->groupsShowIndex($viewPath);
    }
    return false;
  }

  /**
   * ユーザーグループ一覧を表示
   */
  private function groupsShowIndex($viewPath): bool
  {
    // $config = Config::getInstance(); // TODO isAdminはここで取れなきゃダメ
    $userGroupsModel = \ellsif\getEntity('UserGroups');
    $usersModel = \ellsif\getEntity('Users');
    $pageData = [];
    if ($_SESSION['is_admin']) {
      $pageData['groups'] = $userGroupsModel->list();
      $pageData['users'] = $usersModel->list();
    } else {
      // TODO likeの呼び方は現状使えない
      $userId = intval('');
      $pageData['groups'] = $userGroupsModel->list(['userIds LIKE' => "%|${userId}|%"]);
      $pageData['users'] = $usersModel->list(); // TODO 見せる範囲は同じグループのユーザー？？？？
    }

    // userIdsは名前に変換
    foreach($pageData['groups'] as &$group) {
      $group['userLoginIds'] = array_column($usersModel->getUsersByIds($group['userIds']), 'userId');
    }
    $this->loadView($viewPath, $pageData);
    return true;
  }
}