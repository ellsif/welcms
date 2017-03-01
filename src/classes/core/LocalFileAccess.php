<?php
namespace ellsif;


class LocalFileAccess implements FileAccess
{
  use Singleton;
  public static function getInstance() : FileAccess
  {
    return self::instance();
  }

  /**
   * クラス名を取得する。
   * ここで返却される値を管理画面の表示に利用する。
   *
   * @return string
   */
  public function getName(): string
  {
    return 'ローカルファイル';
  }

  /**
   * ファイルの一覧を取得する。
   *
   * @param string $dir
   * @return array
   */
  public function list(string $dir): array {
    return [];
  }

  /**
   * ファイルのメタ情報を取得する。
   *
   * @param array $options
   * @return mixed ファイルが存在しない場合はnull、存在する場合は連想配列を返す。
   */
  public function meta(string $filePath, array $options = [])
  {
    // TODO: Implement meta() method.
  }

  /**
   * ファイルを作成する。（既に存在している場合は削除する）
   *
   * @param $fp
   * @param string $savePath
   * @param array $options
   * @return string
   */
  public function create($fp, string $savePath, array $options = []): string
  {
    // TODO: Implement create() method.
  }

  /**
   * ファイルを削除する。
   *
   * @param string $filePath
   * @param array $options
   * @return bool
   */
  public function delete(string $filePath, array $options = []): bool
  {
    // TODO: Implement delete() method.
  }
}