<?php

namespace ellsif\WelCMS;

use \PDO;

class MysqlAccess extends DataAccess
{

    /**
     * コンストラクタ。
     *
     * ## 説明
     * PDOの初期化を行います。
     *
     * ## 例外/エラー
     * PDOの初期化に失敗した場合、PDOExceptionをthrowします。
     */
    public function __construct(string $dsn, string $username = null, string $password = null, array $options = [])
    {
        parent::__construct($dsn, $username, $password, $options);
    }

    /**
     * テーブルを作成します。
     *
     * @param string $name
     * @param array $columns
     * @return bool
     */
    protected function processCreateTable(Scheme $scheme) :bool
    {
        $columns = $scheme->getDefinition();
        if (count($columns) == 0) {
            throw new Exception('テーブルの作成に失敗しました。カラムが指定されていません。');
        } elseif (isset($columns['id']) || isset($columns['created']) || isset($columns['updated'])) {
            throw new Exception('テーブルの作成に失敗しました。id, created, updated は自動的に追加されるため指定できません。');
        }
        $columnDefs = ['id INT NOT NULL AUTO_INCREMENT'];
        foreach($columns as $columnName => $sc) {
            $columnName = $columnName;
            $type = $this->convertType($sc['type']);
            $default = isset($sc['default']) ? "DEFAULT " . $this->pdo->quote($sc['default']) : '';
            $nullable = (isset($sc['null']) && $sc['null'] === false) ? ' NOT NULL ' : '';
            $comment = $this->pdo->quote(($sc['label'] ?? '') . ':' . ($sc['description'] ?? ''));
            $columnDefs[] = "${columnName} ${type} ${default} ${nullable} COMMENT ${comment}";
        }
        $columnDefs[] = 'created DATETIME';
        $columnDefs[] = 'updated DATETIME';
        $columnDefs[] = 'PRIMARY KEY (id)';
        $columnSql = ' (' . implode(',', $columnDefs) . ')';
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $scheme->getName() . $columnSql;
        welLog('debug', 'MySQL', 'create table: ' . $sql);
        $stmt = $this->pdo->prepare($sql);
        if (!$stmt) {
            throw new Exception(
                'MySQL ERROR ' . implode(':', $this->pdo->errorInfo())
            );
        }
        if (!$stmt->execute()) {
            throw new Exception(
                'MySQL ERROR ' . implode(':', $stmt->errorInfo())
            );
        }
        return true;
    }

    /**
     * テーブルを削除します。
     *
     * @param string $name
     * @param bool $force trueの場合、WelCMS標準のテーブルも削除する
     * @return bool
     */
    public function deleteTable(string $name, bool $force = false) :bool
    {
        // TODO: Implement deleteTable() method.
    }

    /**
     * 件数を取得する。
     *
     * TODO filter未実装
     */
    public function count(string $name, array $filter = [])
    {
        if (!in_array($name, $this->getTables())) {
            throw new \Exception("${name}テーブルは存在しません。", -1);
        }
        $sql = "SELECT COUNT(*) FROM " . $this->pdo->quote($name);
        list($whereSql, $values) = $this->createWhereSql($filter);
        if ($whereSql) {
            $sql .= ' WHERE ' . $whereSql;
        }
        $stmt = $this->pdo->prepare($sql);
        foreach($values as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        if ($stmt->execute()) {
            return $stmt->fetchColumn();
        } else {
            welLog('error', "DataAccess", "${name}からのデータの取得に失敗しました。" . $stmt->errorCode());
            throw new \Exception("${name}からのデータの取得に失敗しました。");
        }
    }

    /**
     * 複数件取得する
     *
     * @param string $name
     * @param int $offset
     * @param int $limit
     * @param string $order
     * @param array $options
     * @return array
     */
    public function select(string $name, int $offset = 0, int $limit = -1, string $order = '', array $filter = []) :array
    {
        if (!in_array($name, $this->getTables())) {
            throw new \InvalidArgumentException("${name}テーブルは存在しません。", -1);
        }
        $sql = "SELECT * FROM " . $this->pdo->quote($name) . ' ';
        list($whereSql, $values) = $this->createWhereSql($filter, $order, $limit, $offset);
        if ($whereSql) {
            $sql .= ' WHERE ' . $whereSql;
        }

        if ($order) {
            $sql .= " ORDER BY ${order}";
        }
        // limitとoffset
        if (($limit = intval($limit)) > 0) {
            $sql .= " LIMIT ${limit}";
        }
        if (($offset = intval($offset)) > 0) {
            $sql .= " OFFSET ${offset}";
        }

        $stmt = $this->pdo->prepare($sql);
        foreach($values as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_NAMED);
            welLog('debug', 'sql', WelUtil::getPdoDebug($stmt));
            welLog('debug', 'sql', "options: " . json_encode($filter));
            welLog('debug', 'sql', json_encode($results));
            return $results;
        } else {
            welLog('error', "DataAccess", "${name}からのデータの取得に失敗しました。" . $stmt->errorCode());
            throw new \RuntimeException("${name}からのデータの取得に失敗しました。");
        }
    }

    /**
     * 1件登録または更新する
     *
     * @param string $name
     * @param array $data
     * @return int
     */
    public function save(string $name, array $data) :int
    {
        // TODO: Implement save() method.
    }

    /**
     * 1件登録する
     *
     * @param string $name データ名（テーブル名やファイル名）
     * @param array $data 格納するデータ
     * @return int 登録データのid(失敗時は-1)
     */
    public function insert(string $name, array $data) :int
    {
        welLog('trace', 'DataAccess', "INSERT to ${name} start data:" . json_encode($data));

        $data = $this->addCreatedAt($data);

        $columns = array_keys($data);
        $params = [];
        foreach($columns as $column) {
            $params[] = ":${column}";
        }
        $sql = 'INSERT INTO ' . $name . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $params) . ')';
        $stmt = $this->pdo->prepare($sql);

        foreach($columns as $column) {
            $stmt->bindValue(":${column}", $data[$column]);
        }
        welLog('trace', 'DataAccess', WelUtil::getPdoDebug($stmt));

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            welLog('error', 'DataAccess', $errorInfo[2]);
            throw new Exception("${name}へのINSERTに失敗しました。");
        }
        $id = $this->pdo->lastInsertId();
        return $id;
    }

    /**
     * 1件更新する
     *
     * @param string $name データ名（テーブル名やファイル名）
     * @param int $id データのid
     * @param array $data
     * @return bool
     */
    public function update(string $name, int $id, array $data) :bool
    {
        welLog('trace', 'DataAccess', "UPDATE to ${name} data:" . json_encode($data));

        $data = $this->addUpdatedAt($data);

        list($columns, $params) = $this->parseConditions($data);
        $sql = 'UPDATE ' . $name . ' SET ';
        $sql .= implode(', ', $columns);
        $sql .= ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        foreach($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':id', $id);
        welLog('trace', 'DataAccess', WelUtil::getPdoDebug($stmt));
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            welLog('error', 'DataAccess', $errorInfo[2]);
            throw new \Exception("${name}のUPDATEに失敗しました。");
        }
        return $stmt->rowCount();
    }

    /**
     * 複数件更新する
     *
     * @param string $name
     * @param array $data
     * @param array $condition
     * @return int
     */
    public function updateAll(string $name, array $data, array $condition) :int
    {
        $data = $this->addUpdatedAt($data);

        list($dataColumns, $dataParams) = $this->parseConditions($data);
        list($whereColumns, $whereParams) = $this->parseConditions($condition, ':_');

        $sql = 'UPDATE ' . $name . ' SET ';
        $sql .= implode(', ', $dataColumns);
        $sql .= ' WHERE ' . implode(' AND ', $whereColumns);
        $stmt = $this->pdo->prepare($sql);

        foreach($dataParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        foreach($whereParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        welLog('trace', 'DataAccess', WelUtil::getPdoDebug($stmt));
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            welLog('error', 'DataAccess', $errorInfo[2]);
            throw new \Exception("${name}のUPDATEに失敗しました。");
        }
        return $stmt->rowCount();
    }

    /**
     * 1件削除する
     *
     * @param string $name
     * @param int $id
     * @return bool
     */
    public function delete(string $name, int $id) :bool
    {
        $sql = 'DELETE FROM ' . $name . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('id', $id);
        welLog('trace', 'DataAccess', WelUtil::getPdoDebug($stmt));
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            welLog('error', 'DataAccess', $errorInfo[2]);
            throw new \Exception("${name}のDELETEに失敗しました。");
        }
        return $stmt->rowCount();
    }

    /**
     * 複数件削除する
     *
     * @param string $name
     * @param array $condition
     * @return int
     */
    public function deleteAll(string $name, array $condition) :int
    {
        $sql = 'DELETE FROM ' . $name . ' WHERE ';
        list($columns, $params) = $this->parseConditions($condition);
        $sql .= implode(' AND ', $columns);
        $stmt = $this->pdo->prepare($sql);
        foreach($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        welLog('trace', 'DataAccess', WelUtil::getPdoDebug($stmt));
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            welLog('error', 'DataAccess', $errorInfo[2]);
            throw new \Exception("${name}のDELETEに失敗しました。");
        }
        return $stmt->rowCount();
    }

    /**
     * SQL文による更新
     *
     * @param string $query
     * @return int
     */
    public function updateQuery(string $query) :int
    {
        // TODO: Implement updateQuery() method.
    }

    /**
     * SQL文による削除
     *
     * @param string $query
     * @return int
     */
    public function deleteQuery(string $query, array $params = []) :int
    {
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute($params)) {
            return $stmt->rowCount();
        } else {
            throw new Exception("データの削除に失敗しました。" . $stmt->errorCode() . ':' . implode(':', $stmt->errorInfo()));
        }
    }

    /**
     * テーブル名の一覧を取得します。
     */
    protected function  processGetTables() :array
    {
        $result = [];
        $tables = $this->selectQuery("SHOW TABLES");
        foreach($tables as $table) {
            $result[] = current(array_slice($table, 0, 1, true));
        }
        return $result;
    }

    /**
     * テーブルのカラム一覧を取得する
     *
     * ## 説明
     *
     */
    public function getColumns(string $name): array
    {
        $columns = [];
        $name = $this->pdo->quote($name);
        $sql = "PRAGMA table_info(${name})";
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($results as $result) {
                $columns[$result->name] = [
                    'label' => $result->name,
                    'type' => $result->type,
                    'default' => $result->dflt_value,
                ];
                if (intval($result->notnull) > 0) {
                    $columns[$result->name]['validation'] = [
                        ['rule' => 'required'],
                    ];
                }
            }
        }
        return $columns;
    }

    /**
     * @param array $condition
     * @return array
     */
    private function parseConditions(array $condition, string $prefix = ':'): array
    {
        $columns = [];
        $values = [];
        foreach($condition as $key => $val) {
            $columns[] = "${key} = ${prefix}${key}";
            $values[$prefix . $key] = $val;
        }
        return [$columns, $values];
    }

    /**
     * created_atとupdated_atを追加する
     *
     * @param array $data
     * @return array
     */
    private function addCreatedAt(array $data) :array
    {
        if (!isset($data['created'])) {
            $data['created'] = date('Y-m-d H:i:s');
        }
        $data = $this->addUpdatedAt($data);
        return $data;
    }

    /**
     * updated_atを追加する
     *
     * @param array $data
     * @return array
     */
    private function addUpdatedAt(array $data) :array
    {
        if (!isset($data['updated'])) {
            $data['updated'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    public function convertType($type) :string
    {
        $types = explode(':', $type);
        $varCharSize = 1024;
        if (count($types) > 1) {
            $type = $types[0];
            if ($type == 'string') {
                $varCharSize = intval($types[1]);
            }
        }
        $conv = [
            'int'       => 'INT',
            'float'     => 'FLOAT',
            'double'    => 'DOUBLE',
            'string'    => "VARCHAR(${varCharSize})",
            'text'      => 'TEXT',
            'datetime'  => 'DATETIME',
        ];
        return $conv[$type] ?? 'TEXT';
    }

    /**
     * SQLのWHERE以降を生成します。
     */
    public function createWhereSql($filter): array
    {
        $whereSql = '';
        $columns = [];
        $values = [];
        foreach($filter as $key => $val) {
            if (is_array($val)) {
                // 配列はin句で処理
                $inColumns = [];
                foreach ($val as $idx => $_val) {
                    $inColumns[] = ":${key}_${idx}";
                    $values[":${key}_${idx}"] = $_val;
                }
                $columns[] = "${key} IN (" . implode(',', $inColumns) . ")";
            } elseif ($val === null) {
                $columns[] = "${key} IS NULL";
            } else {
                $columns[] = "${key} = :${key}";
                $values[":${key}"] = $val;
            }
        }

        if (count($columns) > 0) {
            $whereSql .= implode(' AND ', $columns);
        }
        return [$whereSql, $values];
    }
}