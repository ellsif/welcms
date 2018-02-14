<?php

namespace ellsif\WelCMS;
use ellsif\Form;

/**
 * 個別ページの管理に関連するActionのTrait。
 *
 * ## 説明
 * AdminServiceにより利用されます。
 */
trait AdminPageService
{
  /**
   * 個別ページ情報を取得する。
   *
   * ## 説明
   * 個別ページのリスト、または指定idに対応するデータを取得します。
   * 個別ページ一覧画面、個別ページ登録・更新画面での利用を想定しています。
   */
  public function getPageAdmin($param = [])
  {
    $result = new ServiceResult();
    if (empty($param)) {

      // 一覧ページを表示
      $pageEntity = \ellsif\getEntity('Page');
      $pages = $pageEntity->list();
      $result->resultData(['pages' => $pages]);
      $result->view('html', Router::getViewPath('admin/page.php'));
    } else {

      // 詳細ページを表示
      $result->view('html', Router::getViewPath('admin/page/add_edit.php'));
      if (strcasecmp($param[0], 'add') === 0 || strcasecmp($param[0], 'edit') === 0) {
        $result->resultData(['id' => intval($param[1] ?? null)]);
      } else {
        \ellsif\throwError('Page Not Found', '', 404);
      }
    }
    return $result;
  }

  /**
   * 個別ページの更新、または削除を行う。
   *
   * ## 説明
   * 個別ページの登録・更新・削除処理を行います。
   */
  public function postPageAdmin($param)
  {
    $result = new ServiceResult();
    $config = Pocket::getInstance();
    $printHtml = $config->varPrinterFormat() === 'html';

    if (!$config->varValid()) {
      if ($printHtml) {
        $result = $this->getPageAdmin($param);
      } else {
        $result->error(['message' => 'バリデーションエラーです。']);
      }
      return $result;
    }

    if (!$this->pagesSubmitForm($config->varFormTargetId())) {
      if ($printHtml) {
        $result = $this->getPageAdmin($param);
        $config->appendAlert('登録しました。', 'error');
      } else {
        $result->error(['message' => '予期せぬエラーが発生しました。']);
      }
      return $result;
    }

    if ($printHtml) {
      $result = $this->getPageAdmin();
      if ($config->varFormTargetId()) {
        $config->appendAlert('登録しました。', 'success');
      } else {
        $config->appendAlert('更新しました。', 'success');
      }
    } else {
      $result->resultData(['result' => true]);
    }
    return $result;
  }

  public function _del(){
    $config = Pocket::getInstance();

    // 削除処理
    $execDelete = function() use ($viewPath) {
      return true;
    };

    $action = $data[0] ?? 'index';
    $id = $data[1] ?? -1;
    if ($action == 'add') {
      $config->varAction('admin/pages/add');
      if (\ellsif\isPost() && $config->varValid()) {
        if ($this->pagesSubmitForm()) {
          return $this->pagesShowIndex($viewPath);
        }
      } else {
        return $this->pagesShowForm();
      }
    } elseif ($action == 'edit') {
      $config->varAction('admin/pages/edit');
      if (\ellsif\isPost() && $config->varValid()) {
        if ($this->pagesSubmitForm($config->varFormTargetId())) {
          return $this->pagesShowIndex($viewPath);
        }
      } else {
        return $this->pagesShowForm($id);
      }
    } elseif ($action == 'delete') {
      return $execDelete();
    } else if ($action === 'contents' && \ellsif\isPost()) {
      // コンテンツの更新
      if ($this->pagesSubmitContents()) {
        return $this->pagesShowIndex($viewPath);
      }
    } else if ($action === 'index') {
      $config->varAction('admin/pages');
      return $this->pagesShowIndex($viewPath);
    }
    return false;
  }

  /**
   * 個別ページ一覧を表示
   *
   * @param $viewPath
   * @return bool
   */
  private function pagesShowIndex($viewPath): bool
  {
    $pageModel = \ellsif\getEntity('Pages');

    $pages = $pageModel->list();
    $pageData = [];
    $pageData['pages'] = $pages;
    WelUtil::loadView($viewPath, $pageData);
    return true;
  }

  /**
   * 登録・更新画面を表示
   *
   * @param $id
   * @return bool
   */
  private function pagesShowForm($id = null): bool
  {
    $config = Pocket::getInstance();
    $viewPath = $config->dirView() . '/admin/pages/add_edit.php';

    $page = null;
    $contents = null;
    if ($id) {
      $dataAccess = \ellsif\getDataAccess();
      $page = $dataAccess->get('pages', $id);
      if ($page) {
        $page['options'] = json_decode($page['options'], true);

        // テンプレートからdata指定された部分を取得
        $templateData = $dataAccess->get('templates', $page['template_id']);
        $templateData = json_decode($templateData['body_template'], true);
        $template = new HtmlTemplate();
        $contentNames = $template->getContentNames($templateData['dom']);
        $registedContents = $template->getPageContents($id);
        $registedContents = \ellsif\getMap($registedContents, 'name');
        $contents = [];
        foreach ($contentNames as $contentName) {
          if (array_key_exists($contentName, $registedContents)) {
            $contents[] = $registedContents[$contentName];
          } else {
            $contents[] = [
              'name' => $contentName,
              'body' => '',
            ];
          }
        }
      }
    }
    if ($id == null || $page){
      WelUtil::loadView($viewPath, ['page' => $page, 'contents' => $contents]);
      return true;
    }
    return false;
  }

  /**
   * 登録・更新処理
   *
   * @param int $id
   * @return bool
   */
  private function pagesSubmitForm($id = null): bool
  {
    $formData = Form::formInputData();
    $pageModel = \ellsif\getEntity('Pages');
    $formData['options'] = json_encode($formData['options']);
    if (intval($id) > 0) {
      $formData['id'] = intval($id);
    }
    $pageModel->save([$formData]);

    return true;
  }

  /**
   * contentsデータの登録
   *
   * @return bool
   */
  private function pagesSubmitContents() :bool
  {
    $dataAccess = \ellsif\getDataAccess();
    $config = Pocket::getInstance();
    $page_id = $config->varFormTargetId();

    $template = new HtmlTemplate();
    $contents = $template->getPageContents($page_id);
    $contents = \ellsif\getMap($contents, 'name');
    $formData = Form::formInputData();
    foreach($formData as $name => $value) {
      if ($name === 'id') {
        continue;
      }
      if (array_key_exists($name, $contents)) {
        // 更新
        $content = $contents[$name];
        $dataAccess->update('contents', $content['id'], ['body' => $value]);
      } else {
        // 登録
        $content_id = $dataAccess->insert('contents', ['name' => $name, 'body' => $value]);
        $dataAccess->insert('page_contents', ['page_id' => $page_id, 'content_id' => $content_id]);
      }
    }
    return true;
  }
}