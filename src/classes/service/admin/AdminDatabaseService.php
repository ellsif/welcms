<?php

namespace ellsif\WelCMS;
use ellsif\Form;

/**
 * データベースの管理に関連するActionのTrait。
 *
 * ## 説明
 * AdminServiceにより利用されます。
 */
trait AdminDatabaseService
{
  /**
   * データベース情報を取得する。
   *
   * ## 説明
   * テーブルのリスト、または指定テーブルのデータを取得します。
   */
  public function getDatabaseAdmin($param = [])
  {
    $config = Pocket::getInstance();
    $result = new ServiceResult();
    $dataAccess = \ellsif\getDataAccess();
    $tableNames = array_filter($dataAccess->getTables(), function($tableName){
      return strpos($tableName, '_') === false;  // 関連テーブルはここでは除外
    });
    if (empty($param)) {

      $tables = [];
      $sysTables = $config->dbSystemTables();
      $appTables = $config->dbApplicationTables();
      foreach($tableNames as $tableName) {
        $entity = \ellsif\getEntity($tableName);
        $tables[$tableName] = [
          'type' => key_exists($tableName, $sysTables) ? 'system' : 'application',
          'count' => $entity->count(),
          'description' => $sysTables[$tableName] ?? $appTables[$tableName] ?? '',
        ];
      }
      $result->resultData(['tables' => $tables]);
      $result->view('html', Router::getViewPath('admin/database.php'));
    } else {

      if (in_array($param[0], $tableNames)) {
        $entity = \ellsif\getEntity($param[0]);
        $data = $entity->list();
        $this->updateDateFormat($data);
        if ($config->varPrinterFormat() === 'html') {
          $result->view('html', Router::getViewPath('admin/database/table.php'));
          $result->resultData([
            'table' => $param[0],
            'columns' => $dataAccess->getColumns($param[0]),
            'data' => $data,
          ]);
        } else {
          $resultData = [];
          $header = [];
          foreach($dataAccess->getColumns($param[0]) as $column){
            $header[] = $column->name;
          }
          $resultData[] = $header;
          foreach($data as $row) {
            $dataRow = [];
            foreach($header as $columnName) {
              $dataRow[] = $row[$columnName];
            }
            $resultData[] = $dataRow;
          }
          $result->resultData($resultData);
        }
      } else {
        \ellsif\throwError('Page Not Found', '', 404);
      }
    }
    return $result;
  }

  /**
   * created_at, updated_atを日付フォーマットに更新する。
   */
  private function updateDateFormat(&$data) {
    foreach($data as &$row) {
      if (key_exists('created_at', $row)) {
        $row['created_at'] = date('Y-m-d H:i:s', $row['created_at']);
      }
      if (key_exists('updated_at', $row)) {
        $row['updated_at'] = date('Y-m-d H:i:s', $row['updated_at']);
      }
    }
  }
}