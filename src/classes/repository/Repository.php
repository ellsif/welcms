<?php
namespace ellsif\WelCMS;

/**
 * Repositoryの基底クラス
 */
class Repository
{

    protected $name = null;

    protected $columns = [];

    public function __construct($name = null){
        if ($name) {
            $this->name = $name;
        }
        $pocket = Pocket::getInstance();
        $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
        $this->columns = $dataAccess->getColumns($this->getEntityName());
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
    public function get(int $id)
    {
        $pocket = Pocket::getInstance();
        $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
        return $dataAccess->get($this->getEntityName(), $id);
    }

    /**
     * データを取得する。
     */
    public function list(array $filter = [], string $order = '', int $offset = 0, int $limit = -1): array
    {
        $pocket = Pocket::getInstance();
        $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
        return $dataAccess->select($this->getEntityName(), $offset, $limit, $order, $filter);
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
            if (isset($saveData['id']) && is_numeric($saveData['id'])) {
                // 更新
                if (!$dataAccess->update($this->getEntityName(), $saveData['id'], $saveData)) {
                    throw new \RuntimeException('データの更新に失敗しました。');
                }
            } else {
                // 登録
                $id = $dataAccess->insert($this->getEntityName(), $saveData);
                $row['id'] = $id;
            }
        }

        return $data;
    }


    /**
     * バリデーションを行う。
     *
     * ## 説明
     * DBの登録情報を元にバリデーションを行います。
     * DB(Formテーブル)に$nameに一致するバリデーション定義が存在しない場合、$rulesを利用してバリデーションを行います。
     * DBにルールが存在する場合はForm.validationのルールを利用しバリデーションを行います。
     * バリデーションの結果はPocketのvarFormDataに、エラーがあればvarFormErrorにArrayで格納されます。
     */
    protected function validate(string $name, $data, $rules, $paramName = null)
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
            $pocket->varValidated(true);
            $pocket->varValid(true);
            return;
        }

        $pocket = Pocket::getInstance();
        $pocket->varValidated(true);
        $pocket->varValid($results['valid']);
        $pocket->varFormData($results['results']);
        $pocket->varFormTargetId(intval($_POST['id']));
        //$pocket->varFormToken($form['token']);
    }

}