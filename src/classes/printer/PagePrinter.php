<?php

namespace ellsif\WelCMS;


class PagePrinter extends Printer
{
  /**
   * 個別ページ表示
   */
  public function html(ServiceResult $result = null)
  {
    $config = Config::getInstance();
    $path = '/' . implode('/', $config->varActionParams());
    $page = $this->getPage($path);
    if ($page) {
      echo $this->getHtml($page);
    }
  }

  /**
   * URLを元に表示するページを取得する。
   */
  private function getPage($path)
  {
    $pageEntity = \ellsif\getEntity('Page');
    $page = $pageEntity->list(['path' => $path], 0, 1);
    if (count($page) > 0) {
      return $page[0];
    }
    return false;
  }

  /**
   * テンプレートにコンテンツを合成したHTMLを取得
   */
  private function getHtml($page): string
  {
    $dataAccess = \ellsif\getDataAccess();
    $templateData = $dataAccess->get('Template', $page['template_id']);

    $htmlTemplate = new HtmlTemplate();
    $contents = $htmlTemplate->getPageContents($page['id']);
    $contents = \ellsif\getMap($contents, 'name');
    $templateData = json_decode($templateData['body_template'], true);
    return $htmlTemplate->getString($templateData, $contents);
  }
}