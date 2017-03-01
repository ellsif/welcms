<?php

namespace ellsif\WelCMS;

class ContentRepository extends Repository
{
  /**
   * Page.idからContentのリストを取得する。
   */
  public function getByPageId(int $pageId): array
  {
    $sql =<<<EOL
SELECT * FROM Content
  LEFT JOIN Page_Content ON Content.id = Page_Content.content_id
  LEFT JOIN Page ON Page_Content.page_id = Page.id
WHERE Page.id = :pageId
EOL;

    $dataAccess = getDataAccess();
    return $dataAccess->selectQuery($sql, ['pageId' => $pageId]);
  }
}