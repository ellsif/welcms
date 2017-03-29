<?php

namespace ellsif\WelCMS;


class PagePrinter extends Printer
{
  /**
   * 個別ページ表示
   */
  public function html(ServiceResult $result = null)
  {
    $config = Pocket::getInstance();
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
      $pocket = Pocket::getInstance();
    $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
    $templateData = $dataAccess->get('Template', $page['template_id']);

    $htmlTemplate = new HtmlTemplate();
    $contents = $htmlTemplate->getPageContents($page['id']);
    $contents = WelUtil::getMap($contents, 'name');
    $templateData = json_decode($templateData['body_template'], true);
    return $htmlTemplate->getString($templateData, $contents);
  }
}