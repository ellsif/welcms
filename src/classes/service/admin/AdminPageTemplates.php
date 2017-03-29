<?php

namespace ellsif\WelCMS;


use ellsif\Form;

trait AdminPageTemplates
{
  /**
   * テンプレート管理
   *
   * @param $viewPath viewファイルのpath（ただしindex）
   * @param array $data URLのpages/以降が入る（pages/edit/1の場合、[1]）
   */
  protected function templates($viewPath, $data)
  {
    $config = Pocket::getInstance();

    $action = $data[0] ?? 'index';
    $id = $data[1] ?? null;
    if ($action === 'add') {
      $config->varAction('admin/templates/add');
      if (\ellsif\isPost() && $config->varValid()) {
        if ($this->templatesSubmitForm()) return $this->templatesShowIndex($viewPath);
      } else {
        return $this->templatesShowForm();
      }
    } elseif ($action == 'edit') {
      $config->varAction('admin/templates/edit');
      if (\ellsif\isPost() && $config->varValid()) {
        if ($this->templatesSubmitForm($config->varFormTargetId())) return $this->templatesShowIndex($viewPath);
      } else {
        return $this->templatesShowForm($id ?? $config->varFormTargetId());
      }
    } elseif ($action == 'delete') {
      // return $execDelete();
    } else {
      $config->varAction('admin/templates');
      return $this->templatesShowIndex($viewPath);
    }
    return false;
  }

  /**
   * 個別ページ一覧を表示
   *
   * @param $viewPath
   * @return bool
   */
  private function templatesShowIndex($viewPath): bool {
    $dataAccess = \ellsif\getDataAccess();
    $templates = $dataAccess->select('templates');
    $pageData = [];
    $pageData['templates'] = $templates;
    $this->loadView($viewPath, $pageData);
    return true;
  }

  /**
   * 登録・更新画面を表示
   *
   * @param $id
   * @return bool
   */
  private function templatesShowForm($id = null): bool {
    $config = Pocket::getInstance();
    $viewPath = $config->dirView() . '/admin/templates/add_edit.php';

    $template = null;
    if ($id) {
      $dataAccess = \ellsif\getDataAccess();
      $template = $dataAccess->get('templates', $id);
    }
    if ($id == null || $template) {
      $this->loadView($viewPath, ['template' => $template]);
      return true;
    }
    return false;
  }


  /**
   * 登録・更新処理
   *
   * @return bool
   */
  private function templatesSubmitForm($id = null): bool {
    $dataAccess = \ellsif\getDataAccess();
    $formData = Form::formInputData();
    if (isset($formData['options'])) {
      $formData['options'] = json_encode($formData['options']);
    }

    // テンプレートの構造を取得
    $htmlTemplate = new HtmlTemplate();
    $formData['body_template'] = json_encode($htmlTemplate->parse($formData['body']));
    if (intval($id) > 0) {
      $id = intval($id);
      $dataAccess->update('templates', $id, $formData);
      // TODO 更新
    } else {
      $dataAccess->insert('templates', $formData);
    }
    return true;
  }
}