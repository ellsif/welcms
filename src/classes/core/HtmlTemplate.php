<?php

namespace ellsif\WelCMS;

require_once dirname(__FILE__) . '/Template.php';

class HtmlTemplate implements Template
{

  private $style = '';
  private $script = '';

  public function __construct()
  {
    $config = Config::getInstance();
    $config->dbDriver();
    require_once $config->dirSystem() . '/functions/helper.php';
  }

  /**
   * テキストをパースしてテンプレートデータに変換する
   *
   * @param string $text
   * @return array
   */
  public function parse(string $text): array
  {
    libxml_use_internal_errors(true);
    $doc = new \DOMDocument();
    if (!$doc->loadHTML($text, LIBXML_NOBLANKS)) {
      foreach (libxml_get_errors() as $error) {
        // TODO エラー処理（このへん）
        // http://php.net/manual/ja/function.libxml-get-errors.php
      }
      libxml_clear_errors();
      return ['error' => 'パースエラー'];
    }

    $doc->documentElement;
    $this->script = '';
    $this->style = '';
    $result = [
      'encoding' => $doc->encoding,
      'doctype' => $doc->doctype->name,
      'dom' => $this->parseElement($doc->documentElement),
    ];
    $result['script'] = $this->script;
    $result['style'] = $this->style;
    return $result;
  }

  /**
   * テンプレートとコンテンツデータを合成した文字列を取得する
   *
   * @param array $templateData
   * @param array $contents
   * @param array $options
   * @return string
   */
  public function getString(array $templateData, array $contents, array $options = []): string
  {
    $doctype = $templateData['doctype'];
    $html = "<doctype ${doctype}>";
    $html .= $this->buildHtml($templateData['dom'], $contents, $options);
    return $html;
  }

  /**
   * テンプレートに対して設定可能なコンテンツ名の一覧を取得する
   *
   * @return array
   */
  public function getContentNames(array $templateData): array {
    $contents = [];

    foreach($templateData as $tagName => $data) {
      $contents = $this->getContentsName($contents, $data['attributes'], $data['childs']);
    }

    return $contents;
  }

  /**
   * テンプレートの一覧を取得する
   *
   * @param array $options
   * @return array
   */
  public static function getTemplates(array $options = []) :array
  {
    $dataAccess = \ellsif\getDataAccess();
    return $dataAccess->select('Template', 0, -1, 'name', $options);
  }

  /**
   * DOMをパースしてarrayに格納
   *
   * @param \DOMElement $elem
   * @return array
   */
  protected function parseElement(\DOMElement $elem) :array
  {
    $tagName = strtolower($elem->tagName);
    if ($tagName === 'script') {
      $this->script .= $elem->textContent;
      return [];
    } elseif ($tagName === 'style') {
      $this->style .= $elem->textContent;
      return [];
    } else {
      $attributes = [];
      foreach($elem->attributes as $attribute) {
        $attributes[$attribute->name] = $attribute->value;
      }

      $childs = [];
      foreach($elem->childNodes as $child) {
        if ($child instanceof \DOMElement) {
          $childs[] = $this->parseElement($child);
        } else if ($child instanceof \DOMText) {
          $childs[] = $child->wholeText;
        }
      }
      return [$tagName => ['attributes' => $attributes, 'childs' => $childs]];
    }
  }


  /**
   * ページに関連するコンテンツを取得
   *
   * @param $page_id
   * @return array
   */
  public function getPageContents($page_id): array
  {
    $dataAccess = \ellsif\getDataAccess();
    $registeredContents = $dataAccess->selectQuery(
      'SELECT Content.* FROM Content LEFT JOIN Page_Content ON (Content.id = Page_Content.content_id) WHERE Page_Content.page_id = :id',
      ['id' => $page_id]
    );
    return $registeredContents;
  }

  /**
   * テンプレートからHTMLを組み立てる
   *
   * @param array $templateData stringも可
   * @param array $contents
   * @param array $options
   * @return string
   */
  protected function buildHtml($templateData, array $contents, array $options) :string
  {
    if (is_string($templateData)) {
      return $templateData;
    }
    $html = '';
    foreach($templateData as $tagName => $data) {
      $html .= $this->getTagHtml($tagName, $data['attributes'], $contents, $data['childs'], $options);
    }
    return $html;
  }

  /**
   * タグにコンテンツを埋め込んで返す
   *
   * @param string $tagName
   * @param array $attributes
   * @param array $contents
   * @param array $childs
   * @param array $options
   * @return string
   */
  protected function getTagHtml(string $tagName, array $attributes,
                                array $contents, array $childs, array $options) :string
  {
    $html = '';

    // コンテンツの設定がある場合
    $contentsData = $this->getContentsData($attributes, $contents);
    if (count($contentsData) > 0 && isset($contentsData['body_type'])) {
      $bodyType = $contentsData['body_type'];

      if ($bodyType === 'path') {
        if (strcasecmp($tagName, 'img') == 0) {
          // 画像の場合、attributeのsrcを更新
          $attributes['src'] = $contentsData['body'];
        } elseif (strcasecmp($tagName, 'a') == 0) {
          // リンクURLの場合、attributeのhrefを更新
          $attributes['href'] = $contentsData['path'];
        }
      }

      // optionsの処理
      if (is_array($contentsData['options'])) {
        $option = $contentsData['options'];

        // attributesの上書き
        if (isset($option['attributes'])) {
          foreach($option['attributes'] as $name => $val) {
            $attributes[$name] = $val;
          }
        }

        // カスタム（子ノードは破棄する）
        if (isset($option['custom']) && function_exists($option['custom'])) {
          return $option['custom']($tagName, $attributes, $childs, $contentsData);
        }
      }

      // textの処理
      if ($bodyType === 'text') {
        $html .= \ellsif\tagged($tagName, $attributes, $contentsData['body']);
        return $html; // テキストの場合も子ノードを破棄する
      }
    }

    // 子ノードの処理
    $childHtml = '';
    foreach($childs as $child) {
      $childHtml .= $this->buildHtml($child, $contents, $options);
    }
    $html .= \ellsif\tagged($tagName, $attributes, $childHtml);

    return $html;
  }

  protected function getContentsData($attributes, $contents) :array
  {
    if (isset($attributes['data-name']) && isset($contents[$attributes['data-name']])) {
      return $contents[$attributes['data-name']];
    }
    return [];
  }

  protected function getContentsName($contents, $attributes, $childs)
  {
    if (isset($attributes['data-name'])) {
      $contents[] = $attributes['data-name'];
    }
    foreach($childs as $child) {
      if (is_array($child)) {
        foreach ($child as $tagName => $data) {
          $contents = $this->getContentsName($contents, $data['attributes'], $data['childs']);
        }
      }
    }
    return $contents;
  }
}