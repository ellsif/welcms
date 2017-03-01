<?php
namespace ellsif\WelCMS;

/**
 * Webページで利用する部品の基底クラス。
 *
 * ## 説明
 * Webページで利用する部品を表示するために利用するActionの基底クラスです。
 * ユーザー選択ダイアログ、新着情報一覧など複数の画面で共通利用する部品を
 * WebPartとしてまとめると便利な場合があります。
 *
 * ## 使い方
 * ViewファイルからloadPart()関数を経由して利用します。
 * loadPart()の第二引数がinitialize()の$optionsに引数として渡ります。
 * Viewファイルはviews/partsフォルダ以下にまとめてください。
 *
 *     <?php
 *       $myPart = loadWebPart('MyPart', ['user_id' => $userId]);
 *       $partData = $myPart->getData();
 *     ?>
 *     <p><?php echo $partData['userName'] ?? 'ゲスト' ?>さんようこそ！</p>
 */
abstract class WebPart
{
  private $items;

  protected $options;

  public function __construct($options = [])
  {
    if ($this->authenticate()) {
      $this->initialize($options);
    }
  }

  /**
   * Web部品をロードし、初期化する。
   */
  public static function load(string $name)
  {

  }

  /**
   * Web部品を組み込む
   */
  public static function mount(string $name, array $options)
  {

  }

  /**
   * Web部品のHTMLを取得する
   */
  public static function html(string $name, array $options): string
  {

  }

    /**
   * 認証処理を行う。
   *
   * 認証処理が必要となる場合は、本メソッドをオーバーライドしてください。
   *
   * ## 戻り値
   *
   *
   */
  public function authenticate(): bool
  {
    return true;
  }

  /**
   * WebPartsを初期化する。
   */
  public function initialize($options) {
    $this->options = $options;
  }

  /**
   * Viewの表示に必要なデータを取得する。
   */
  public abstract function getData(): array;

}