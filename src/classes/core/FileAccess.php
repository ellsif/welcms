<?php

namespace ellsif;

interface FileAccess
{
  /**
   * クラス名を取得する。
   * ここで返却される値を管理画面の表示に利用する。
   *
   * @return string
   */
  public function getName(): string;


  /**
   * ファイルのメタ情報を取得する。
   *
   * @param array $options
   * @return mixed ファイルが存在しない場合はnull、存在する場合は連想配列を返す。
   */
  public function meta(string $filePath, array $options = []);


  /**
   * ファイルの一覧を取得する。
   *
   * @param string $dir
   * @return array
   */
  public function list(string $dir = null): array;

  /**
   * ファイルを作成する。（既に存在している場合は削除する）
   *
   * @param $fp
   * @param string $savePath
   * @param array $options
   * @return string
   */
  public function create($fp, string $savePath, array $options = []): string;


  /**
   * ファイルを削除する。
   *
   * @param string $filePath
   * @param array $options
   * @return bool
   */
  public function delete(string $filePath, array $options = []): bool;

}