<?php
namespace ellsif\WelCMS;

/**
 * Repositoryの基底クラス
 */
class Repository
{
    protected $scheme = null;

    protected $dataAccess = null;

    protected $modifiersOnSave = [];

    protected $modifiersOnLoad = [];

    /**
     * コンストラクタ
     *
     * ## 説明
     *
     */
    public function __construct(Scheme $scheme, DataAccess $dataAccess = null)
    {
        $this->scheme = $scheme;
        $this->dataAccess = $dataAccess ? $dataAccess : $this->dataAccess = welDataAccess();
        if (!$this->dataAccess->isTableExists($this->scheme->getName())) {
            $this->dataAccess->createTable($this->scheme);
        }
    }

    /**
     * テーブル名を取得します。
     */
    public function getName(): string
    {
        return $this->scheme->getName();
    }

    /**
     * Schemeを取得します。
     */
    public function getScheme(): Scheme
    {
        return $this->scheme;
    }

    /**
     * データの保存、取得時に利用するModifierの設定を行います。
     *
     * ## 説明
     * onLoad、onSaveには関数名文字列、またはクロージャを指定します。
     */
    public function addModifier(string $column, $onSave = null, $onLoad = null): Repository
    {
        if ($onSave) {
            $this->modifiersOnSave[$column][] = $onSave;
        }
        if ($onLoad) {
            $this->modifiersOnLoad[$column][] = $onSave;
        }
        return $this;
    }

    /**
     * id指定でデータを1件取得します。
     *
     * ## 説明
     * $modifがtrueの場合、取得されるデータは
     * addModifier()で指定されたメソッドによって加工されます。
     */
    public function get(int $id, $modif = true)
    {
        $data = $this->dataAccess->get($this->getName(), $id);
        if ($data && $modif) {
            return $this->modifyOnLoad($data);
        }
        return $data;
    }

    /**
     * データを配列で取得します。
     */
    public function list(string $query, array $params = [], bool $modify = true): array
    {
        $list = $this->dataAccess->selectQuery($query, $params);
        if ($modify) {
            return $this->modifyListOnLoad($list);
        } else {
            return $list;
        }
    }

    public function first(string $query, array $params = [], bool $modify = true): ?array
    {
        $results = $this->list($query, $params, $modify);
        return $results[0] ?? null;
    }

    /**
     * データ件数を取得します。
     */
    public function count(string $query, array $params = []): int
    {
        return $this->dataAccess->count($query, $params);
    }

    /**
     * データを1件保存します。
     *
     * ## 説明
     * 処理するデータにid要素が存在する場合は更新、そうでない場合は登録します。
     *
     * ## 戻り値
     * 引数に渡された$dataにidを設定(登録の場合のみ)して返します。
     */
    public function save(array $data): array
    {
        // 有効なカラムのみ抽出
        $saveData = [];
        $columns = array_keys($this->scheme->getDefinition());
        foreach ($data as $column => $val) {
            if (in_array($column, $columns)) {
                $saveData[$column] = $val;
            }
        }

        if (isset($row['id']) && is_numeric($row['id'])) {
            // 更新
            welLog('debug', 'update', json_encode($saveData));
            if (!$this->dataAccess->update($this->getName(), $row['id'], $this->modifyOnSave($saveData))) {
                throw new \RuntimeException('データの更新に失敗しました。');
            }
        } else {
            // 登録
            welLog('debug', 'regist', json_encode($saveData));
            $id = $this->dataAccess->insert($this->getName(), $this->modifyOnSave($saveData));
            $row['id'] = $id;
            welLog('debug', 'registed', "id = ${id}");
        }
        return $data;
    }

    /**
     * データを複数件保存します。
     */
    public function saveList(array $list): array
    {
        foreach($list as &$row) {
            $row = $this->save($row);
        }
        return $list;
    }

    /**
     * Load時にデータを加工します。
     *
     * ## 説明
     * カラムの定義にonLoadの指定がある場合、該当のメソッドでデータを編集します。
     */
    protected function modifyOnLoad(&$row): array
    {
        foreach($this->modifiersOnLoad as $column => $functions) {
            if (isset($row[$column])) {
                foreach($functions as $function) {
                    if (is_string($function) && is_callable($function, false, $func)) {
                        $row[$column] = $func($row[$column]);
                    } elseif (is_object($function) && $function instanceof \Closure) {
                        $row[$column] = $function($row[$column]);
                    }
                }
            }
        }
        return $row;
    }

    /**
     * Load時にデータリストを加工します。
     */
    protected function modifyListOnLoad(array &$array): array
    {
        foreach($array as &$row) {
            $row = $this->modifyOnLoad($row);
        }
        return $array;
    }

    /**
     * Save時のデータ加工処理を行います。
     */
    protected function modifyOnSave(&$row): array
    {
        foreach($this->modifiersOnSave as $column => $functions) {
            if (isset($row[$column])) {
                foreach($functions as $function) {
                    if (is_string($function) && is_callable($function, false, $func)) {
                        $row[$column] = $func($row[$column]);
                    } elseif (is_object($function) && $function instanceof \Closure) {
                        $row[$column] = $function($row[$column]);
                    }
                }
            }
        }
        return $row;
    }
}