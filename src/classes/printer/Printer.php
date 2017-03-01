<?php

namespace ellsif\WelCMS;

/**
 * 汎用Printerクラス。
 *
 * ## 説明
 *
 */
class Printer
{

  /**
   * Htmlを表示する。
   *
   *
   */
  public function html(ServiceResult $result = null)
  {
    // viewの指定があれば表示
    $viewPath = $result->view('html') ?? Router::getViewPath();
    $this->loadView($viewPath, $result->resultData());
  }

  /**
   * jsonを表示する。
   */
  public function json(ServiceResult $result)
  {
    header("Content-Type: application/json; charset=utf-8");
    if ($result->isError()) {
      http_response_code(500);
    }
    if ($result->view('html')) {
      $this->loadView($result->view('html'), $result->resultData());
    } else {
      echo json_encode(["result" =>$result->resultData()]);
    }
  }

  /**
   * CSVを表示する。
   */
  public function csv(ServiceResult $result)
  {
    if ($result->isError()) {
      http_response_code(500);
      exit;
    }
    $resultData = $result->resultData();
    $fileName = $resultData['fileName'] ?? 'sample';
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=${fileName}.csv");
    header("Content-Transfer-Encoding: binary");

    $output = fopen('php://output', 'w');
    $indent = [];
    $recursive = function($out, $data) use(&$recursive, &$indent) {
      if (\ellsif\isArray($data)) { // 連番の配列
        foreach($data as $row) {
          if (\ellsif\isObjectArray($row)) {
            $indent[] = '';
            $recursive($out, $row);
          } elseif(is_array($row)) {
            fputcsv($out, array_merge($indent, $row));
          } else {
            fputcsv($out, array_merge($indent, [$row]));
          }
        }
      } elseif (is_array($data)) {  // 連想配列
        foreach($data as $key => $row) {
          if (\ellsif\isObjectArray($row)) {
            fputcsv($out, array_merge($indent, [$key]));
            $indent[] = '';
            $recursive($out, array_merge($indent, $row));
          } elseif(\ellsif\isArray($row)) {
            fputcsv($out, array_merge($indent, $row));
          } elseif (is_object($row)) {
            $indent[] = '';
            $recursive($out, $row);
          } else {
            fputcsv($out, array_merge($indent, [$key, $row]));
          }
        }
      } elseif (is_object($data)) {
        $recursive($out, json_decode(json_encode($data), true));
      }
    };
    $recursive($output, $resultData);
    fclose($output);
  }

  /**
   * XMLを表示する。
   */
  public function xml($result)
  {

  }

  /**
   * SVGを表示する。
   */
  public function svg($result)
  {

  }

  /**
   * Viewを読み込む。
   */
  protected function loadView(string $viewPath, array $data = [])
  {
    if (!file_exists($viewPath)) {
      throw new \Error('View File Not Found', 404);
    }
    extract($data);
    include $viewPath;
  }
}