<?php

namespace ellsif\WelCMS;

use ellsif\Logger;

/**
 * 個別ページ表示コントローラ
 */
class PageService extends Service
{

  /**
   * 個別ページを表示する。
   */
  function show($params)
  {
    $path = '/' . implode('/', $params);
    $page = $this->getPage($path);
    if ($page) {

    }
  }

  /**
   * URLを元に表示するページを取得する。
   */
  private function getPage($path)
  {
    $pageEntity = \ellsif\getEntity('Pages');
    $page = $pageEntity->list(['path' => $path], 0, 1);
    if (count($page) > 0) {
      return $page[0];
    }
    return false;
  }

  /**
   * テンプレートにコンテンツを合成したHTMLを取得
   *
   * @param $page
   * @return string
   */
  private function getHtml($page): string
  {
    $dataAccess = \ellsif\getDataAccess();
    $templateData = $dataAccess->get('templates', $page['template_id']);

    $htmlTemplate = new HtmlTemplate();
    $contents = $htmlTemplate->getPageContents($page['id']);
    $contents = \ellsif\getMap($contents, 'name');
    $templateData = json_decode($templateData['body_template'], true);
    return $htmlTemplate->getString($templateData, $contents);
  }
}