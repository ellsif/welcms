<?php

namespace ellsif\WelCMS;


interface Template
{
  /**
   * テキストをパースしてテンプレートデータに変換する
   *
   * @param string $text
   * @return array
   */
  public function parse(string $text) :array;

  /**
   * テンプレートとコンテンツデータを合成した文字列を取得する
   *
   * @param array $templateData
   * @param array $contents
   * @param array $options
   * @return string
   */
  public function getString(array $templateData, array $contents, array $options = []) :string;
}