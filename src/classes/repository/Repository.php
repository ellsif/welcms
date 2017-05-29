<?php
namespace ellsif\WelCMS;
use ellsif\Logger;

/**
 * Repositoryの基底クラス
 */
class Repository
{

    protected $name = null;

    protected $dataAccess = null;

    protected $columns = [];

    /**
     * コンストラクタ
     *
     * ## 説明
     *
     */
    public function __construct($name = null)
    {
        if ($name) {
            $this->name = $name;
        }
        $pocket = Pocket::getInstance();
        $this->dataAccess = WelUtil::getDataAccess($pocket->dbDriver());

        if (!$this->dataAccess->isTableExists($name)) {
            if (count($this->columns) > 0) {
                $this->dataAccess->createTable($name, $this->columns);
            } else {
                throw new Exception("テーブル${name}が利用できませんでした。");
            }
        }
    }

    protected function initColumns()
    {
        $columns = $this->dataAccess->getColumns($this->getEntityName());

        foreach($columns as $name => $column) {
            if ($name === 'id' || $name === 'created' || $name === 'updated') {
                continue;
            }
            if (!array_key_exists($name, $this->columns)) {
                $this->columns[$name] = $column;
            }
        }
    }

    /**
     * Entity名（=テーブル名）を取得する。
     *
     * ## 説明
     * テーブルの命名規則にスネークケースなどを利用している場合は、
     * 継承先クラスで$nameプロパティをオーバーライドする必要があります。
     *
     *     protected $name = 'user_addresses';
     */
    public function getEntityName(): string
    {
        if ($this->name) {
            return $this->name;
        }
        $arr = explode('\\', get_class($this));
        $className = $arr[count($arr)-1];
        if (($pos = mb_strrpos($className, 'Repository')) > 0) {
            $className = mb_substr($className, 0, $pos);
        }
        return $className;
    }

    /**
     * id指定でデータを取得する。
     */
    public function get(int $id, $modif = true)
    {
        $pocket = Pocket::getInstance();
        $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
        $data = $dataAccess->get($this->getEntityName(), $id);
        if ($data && $modif) {
            $data = [$data];
            $data = $this->modifOnLoad($data);
            return $data[0];
        } else {
            return $data;
        }
    }

    /**
     * id指定でデータを削除する
     */
    public function delete($id)
    {
        $id = intval($id);
        if ($id) {
            $pocket = Pocket::getInstance();
            $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
            return $dataAccess->delete($this->getEntityName(), $id);
        }
        return false;
    }


    /**
     * データを取得する。
     */
    public function list(array $filter = [], string $order = '', int $offset = 0, int $limit = -1, $modif = true): array
    {
        $pocket = Pocket::getInstance();
        $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
        $list = $dataAccess->select($this->getEntityName(), $offset, $limit, $order, $filter);
        if ($modif) {
            return $this->modifOnLoad($list);
        } else {
            return $list;
        }
    }

    /**
     * データ件数を取得する。
     */
    public function count(array $filter = []): int
    {
        $pocket = Pocket::getInstance();
        $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
        return $dataAccess->count($this->getEntityName(), $filter);
    }

    /**
     * データを保存する。
     *
     * ## 説明
     * 連想配列の配列を引数に取り、順次登録、更新を行います。
     * 処理するデータにid要素が存在する場合は更新、そうでない場合は登録します。
     * 処理中に例外が発生した場合は全データをロールバックして例外をthrowします。
     *
     * ## 戻り値
     * 引数に渡された$dataにidを設定(注：登録の場合のみ)して返します。
     */
    public function save(array $data): array
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('データの保存に失敗しました。');
        }

        foreach($data as &$row) {
            // 有効なカラムのみ抽出
            $saveData = array_filter($row, function($key){
                return in_array($key, array_keys($this->columns));
            }, ARRAY_FILTER_USE_KEY);

            $pocket = Pocket::getInstance();
            $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());

            if (isset($row['id']) && is_numeric($row['id'])) {
                // 更新
                Logger::getInstance()->log('debug', 'update', json_encode($saveData));
                if (!$dataAccess->update($this->getEntityName(), $row['id'], $this->modifOnSave($saveData))) {
                    throw new \RuntimeException('データの更新に失敗しました。');
                }
            } else {
                // 登録
                Logger::getInstance()->log('debug', 'regist', json_encode($saveData));
                $id = $dataAccess->insert($this->getEntityName(), $this->modifOnSave($saveData));
                $row['id'] = $id;
                Logger::getInstance()->log('debug', 'registed', "id = ${id}");

            }
        }
        return $data;
    }


    /**
     * バリデーションを行う。
     *
     * ## 説明
     * バリデーションの結果はPocketのvarFormDataに、エラーがあればvarFormErrorにArrayで格納されます。
     */
    public function validate($data, $rules, $paramName = null)
    {
        // TODO 今のところDBによるバリデーションは未実装

        if ($paramName === null) {
            // 全チェック（この場合だけtokenのチェックが入る）
            $results = Validator::validAll($data, $rules);
        } else if (array_key_exists($paramName, $rules)) {
            // 項目指定がある場合
            $results = Validator::valid($data[$paramName] ?? '', $rules[$paramName]);
        } else {
            // 指定された項目に対するバリデーションが無い場合はバリデーションOKとする
            $pocket = Pocket::getInstance();
            $pocket->varValid(true);
            return;
        }

        $pocket = Pocket::getInstance();
        $pocket->varValid($results['valid']);
        $pocket->varFormData($results['results']);
        // $pocket->varFormTargetId(intval($_POST['id']));
        //$pocket->varFormToken($form['token']);
    }


    public function getDebugDump()
    {
        return json_encode($this->columns);
    }

    /**
     * パイプ区切りの文字列を分割して配列にします。
     */
    public static function pipeExplode($str): array
    {
        $str = trim($str, '|');

        if ($str === '') {
            return [];
        }
        return explode('|', $str);
    }

    /**
     * 配列をパイプ区切りの文字列にします。
     */
    public static function pipeImplode($array): string
    {
        if (!is_array($array) || count($array) == 0) {
            return '';
        }
        return '|' . implode('|', $array) . '|';
    }

    /**
     * Load時にデータを編集します。
     *
     * ## 説明
     * カラムの定義にonLoadの指定がある場合、該当のメソッドでデータを編集します。
     */
    protected function modifOnLoad(&$array): array
    {
        foreach($array as &$row) {
            foreach($this->columns as $key => $attr) {
                if (isset($row[$key]) && isset($attr['onLoad']) && is_callable($attr['onLoad'], false, $func)) {
                    $row[$key] = $func($row[$key]);
                }
            }
        }
        return $array;
    }

    /**
     * Save時にデータを編集します。
     *
     * ## 説明
     * カラムの定義にonSaveの指定がある場合、該当のメソッドでデータを編集します。
     */
    protected function modifOnSave(&$array): array
    {
        foreach($this->columns as $key => $attr) {
            if (isset($array[$key]) && isset($attr['onSave']) && is_callable($attr['onSave'])) {
                $array[$key] = $attr['onSave']($array[$key]);
            }
        }
        return $array;
    }

    /**
     * 配列形式のjson_encodeを行います。
     */
    public static function json_decode($arg)
    {
        return \json_decode($arg, true);
    }

    /**
     * カラム定義を取得します。
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * 特定のカラムの値に対応する名称を取得します。
     *
     * ## 説明
     * $columns[$column]['values']定義を元に名称を取得します。
     * 取得に失敗した場合は$valueを返します。
     *
     * ## 例
     *
     *     protected $columns = [
     *         'state' => [
     *             'label'      => 'ステータス',
     *             'type'       => 'int',
     *             'values'     => [
     *                 '0' => '下書き',
     *                 '1' => '公開',
     *                 '-1' => '削除',
     *             ]
     *         ],
     *     ];
     *
     *     $repo->label('state', '1');  // '公開'
     */
    public function getValueName($column, $value)
    {
        if (isset($this->columns[$column])) {
            $column = $this->columns[$column];
            if (isset($column['values']) && is_array($column['values'])) {
                foreach ($column['values'] as $_val => $_name) {
                    if ($value == $_val) {
                        return $_name;
                    }
                }
            }
        }
        return $value;
    }


    /**
     * バリデーションルールを取得します。
     */
    public function getValidationRules($columns = null)
    {
        $rules = [];
        foreach($this->columns as $name => $settings) {
            if ($columns && !in_array($name, $columns)) {
                continue;
            }
            if (isset($settings['validation'])) {
                $rules[$name] = $settings['validation'];
            }
        }
        return $rules;
    }

}