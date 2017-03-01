<?php
namespace ellsif\WelCMS;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use ellsif\DataAccess;
use ellsif\SqliteAccess;

class Util
{
    /**
     * DataAccessクラスインスタンス取得する。
     *
     * ## 説明
     * CMS設定ファイル(config.php)の設定に従い、下記のいずれかのインスタンスを取得します。
     * - SqliteDataAccess
     * - CsvDataAccess ※未実装
     * - MySqlDataAccess ※未実装
     */
    public static function getDataAccess(): DataAccess
    {
        // TODO MySQLやCSV対応
        return SqliteAccess::getInstance();
    }

    /**
     * ローカルファイルへの文字列出力を行う。
     *
     * ## パラメータ
     * <dl>
     *   <dt>path</dt>
     *     <dd>出力先パスを指定します。<br>指定されたディレクトリが存在しない場合、ディレクトリを作成します。指定されたファイルが既に存在している場合は追記します。</dd>
     *   <dt>string</dt>
     *     <dd>ファイルに書き出す文字列を指定します。</dd>
     * </dl>
     *
     * ## エラー/例外
     * 書き込みに失敗した場合、Exceptionをthrowします。
     *
     * ## 例
     *     writeFile('/path/to/file.txt', 'Hello!');
     *
     */
    public static function writeFile($path, $string)
    {
        if (file_exists($path) && !is_writable($path)) {
            throw new \Exception("${path} に書き込み権限がありません。");
        }

        makeDirectory(dirname($path));
        if (!$handle = fopen($path, 'a')) {
            throw new \Exception("${path} のオープンに失敗しました。");
        }
        if (fwrite($handle, $string . "\n") === FALSE) {
            throw new \Exception("${path} の書き込みに失敗しました。");
        }
        fclose($handle);
    }

    /**
     * FileAccessクラスのインスタンスを取得する。
     *
     * ## 説明
     *
     */
    public static function getFileAccess() :FileAccess
    {
        $config = Config::getInstance();
        $fileAccessClass = $config->settingFileAccessClass();
        return $fileAccessClass::getInstance();
    }

    /**
     * Entityのインスタンスを取得する。
     *
     * ## 説明
     * テーブル名を指定してEntityクラスをインスタンス化して取得します。<br>
     * classes/entityディレクトリに同名のクラスファイルが存在する場合は
     * 同ファイルをrequireし、インスタンス化して返却します。<br>
     * クラスファイルが存在しない場合もDB上に$nameで指定されたテーブルが存在すれば汎用Entityクラスのインスタンスを生成して返します。
     *
     * ## パラメータ
     * <dl>
     *   <dt>name</dt>
     *   <dd>
     *     テーブル名を指定します。大文字と小文字は区別されます。
     *   </dd>
     * </dl>
     *
     * ## 戻り値
     * Modelクラスのインスタンスを返します。
     *
     * ## エラー/例外
     * modelクラスのファイルが存在しない、かつ指定された名称に対応するテーブルが存在しない場合はErrorをthrowします。
     */
    public static function getRepository(string $name): \ellsif\WelCMS\Repository
    {
        $config = Config::getInstance();
        $modelPath = $config->dirEntity() . $name . 'Repository.php';
        if (file_exists($modelPath)) {
            $nameSpace = Util::getNameSpace($modelPath);
            $className = "${nameSpace}\\${name}Entity";
            return new $className();
        }
        // テーブルがあるならば、汎用Entityを返す
        $dataAccess = Util::getDataAccess();
        $tables = $dataAccess->getTables();
        if (in_array($name, $tables)) {
            return new \ellsif\WelCMS\Repository($name);
        }

        throw new InvalidArgumentException("${name}Repositoryの初期化に失敗しました", 500);
    }

    /**
     * WebPartを取得する。
     *
     * ## 説明
     *
     */
    public static function loadPart(string $name): WebPart
    {
        $config = Config::getInstance();
        $partPath = $config->dirSystem() . 'classes/parts/' . $name . '.php';
        if (file_exists($partPath)) {
            $nameSpace = getNameSpace($partPath);
            $className = "${nameSpace}\\${name}";
            return new $className();
        }
        throwError("${name}WebPartの初期化に失敗しました");
    }

    /**
     * ディレクトリを作成する。
     */
    public static function makeDirectory($path, $mode = 0777)
    {
        if (!file_exists($path)) {
            if (!mkdir($path, $mode, true)) {
                throw new \Exception("${path} ディレクトリの作成に失敗しました。");
            }
        }
    }

    /**
     * 日付を取得する。
     *
     * ## 説明
     * Configで設定されたtimeZoneを設定した上でdate($format)をコールします。
     * dete()関数の代わりに利用してください。
     */
    public static function getDate($format = 'Y-m-d')
    {
        $config = Config::getInstance();
        date_default_timezone_set($config->timeZone());
        return date($format);
    }

    /**
     * 時間を取得する。
     *
     * ## 説明
     * Configで設定されたtimeZoneを設定した上でdate('H:i:s')をコールします。
     * dete()関数の代わりに利用してください。
     */
    public static function getTime()
    {
        $config = Config::getInstance();
        date_default_timezone_set($config->timeZone());
        return date('H:i:s');
    }

    /**
     * 日時を取得する。
     *
     * ## 説明
     * Configで設定されたtimeZoneを設定した上でdate('Y-m-d H:i:s')をコールします。
     * dete()関数の代わりに利用してください。
     */
    public static function getDateTime()
    {
        $config = Config::getInstance();
        date_default_timezone_set($config->timeZone());
        return date('Y-m-d H:i:s');
    }

    public static function toDir($path)
    {
        if (mb_substr($path, -1) !== '/') {
            $path = "${path}/";
        }
        return $path;
    }

    public static function getPdoDebug(\PDOStatement $stmt) :string
    {
        ob_start();
        $stmt->debugDumpParams();
        $debug = ob_get_contents();
        ob_end_clean();
        return $debug;
    }


    /**
     * URLをパースする。
     *
     * ## 説明
     * parse_url()のパース結果に幾つか項目を追加して返します。
     *
     * ## パラメータ
     *
     * ## 返り値
     * 正しいURLでない場合は空配列を返します。
     * パースに成功した場合、連想配列を返します。連想配列には以下の要素が含まれる可能性があります。
     *
     * - scheme
     * - host
     * - port
     * - user
     * - pass
     * - path
     * - query ("?"以降)
     * - fragment ("#"以降)
     * - paths (pathを"/"で分割した配列)
     * - params (queryを分割した連想配列)
     *
     * ## 変更履歴
     * - 初回実装
     *
     * ## 例
     *
     */
    public static function parseUrl(string $url) :array
    {
        if (!Util::isUrl($url)) {
            if (intval($_SERVER['REMOTE_PORT']) == 443) {
                $url = 'https://' . Util::getHostname() . $url;
            } else {
                $url = 'http://' . Util::getHostname() . $url;
            }
        }
        $urlInfo = parse_url($url);
        if ($urlInfo !== FALSE) {
            $path = $urlInfo['path'];
            $paths = array_filter(explode('/', $path), "strlen");
            $urlInfo['paths'] = array_values($paths);

            $urlInfo['params'] = [];
            if (isset($urlInfo['query'])) {
                $urlInfo['params'] = Util::parseQuery($urlInfo['query']);
            }
            return $urlInfo;
        } else {
            return [];
        }
    }

    // queryをパースする
    public static function parseQuery(string $query) :array
    {
        $results = [];
        $params = explode('&', $query);
        foreach ($params as $param) {
            list($name, $val) = explode('=', $param);
            if (strpos($name, '[]') > 0) {
                $name = substr($name, 0, strrpos($name, "[]"));
                if (!isset($results[$name])) {
                    $results[$name] = [];
                }
                $results[$name][] = $val;
            } else {
                $results[$name] = $val;
            }
        }
        return $results;
    }

    // ホスト名を取得する
    public static function getHostname() :string
    {
        if ($_SERVER['HTTP_HOST']) {
            return $_SERVER['HTTP_HOST'];
        } else {
            return 'unknownhost';
        }
    }

    public static function isPost() :bool
    {
        return strcasecmp('POST', $_SERVER['REQUEST_METHOD']) == 0;
    }

    /**
     * ハッシュ化に使うsaltを取得
     *
     * @return string
     */
    public static function getSalt($length = 24) :string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    // ハッシュ化されたパスワードを取得
    public static function getHashed(string $password, string $salt, int $version) :string
    {
        $hash = hash('sha256', $password . $salt);
        return "${hash}:${salt}$${version}$";
    }

    // パスワードのチェック
    public static function checkHash(string $password, string $hashstr) :bool
    {
        $ary = explode(':', $hashstr);
        if (count($ary) == 2) {
            $ary = explode('$', $ary[1]);
            if (count($ary) == 3) {
                $salt = $ary[0];
                $version = intval($ary[1]);
                $hashed = getHashed($password, $salt, $version);
                return $hashstr === $hashed;
            }
        }
        return false;
    }

    /**
     * データ配列を連想配列に変換する
     *
     * ## 説明
     * 連想配列の配列から指定された項目をキーとする連想配列を生成します。
     *
     *     $ary = [
     *       ['id' => 'abc', 'name' => 'name1', 'data' => 'XXXX'],
     *       ['id' => 'def', 'name' => 'name2', 'data' => 'YYYY'],
     *       ['id' => 'ghi', 'name' => 'name3', 'data' => 'ZZZZ'],
     *     ];
     * 上記のような配列から
     *
     *     [
     *       'abc' => ['id' => 'abc', 'name' => 'name1', 'data' => 'XXXX'],
     *       'def' => ['id' => 'def', 'name' => 'name2', 'data' => 'YYYY'],
     *       'ghi' => ['id' => 'ghi', 'name' => 'name3', 'data' => 'ZZZZ'],
     *     ]
     * または$valNameを指定することで
     *
     *     [
     *       'abc' => 'name1',
     *       'def' => 'name2',
     *       'ghi' => 'name3',
     *     ]
     * のような配列を得ることができます。
     *
     * ## パラメータ
     * <dl>
     *   <dt>ary</dt>
     *     <dd>抽出元の配列を指定します。</dd>
     *   <dt>keyName</dt>
     *     <dd>戻り値のキーに利用する項目名を指定します。値がユニークで無い場合は後の要素で上書きされます。</dd>
     *   <dt>valName</dt>
     *     <dd>戻り値の値に利用する項目名を指定します。未指定の場合、配列全体が値に設定されます。</dd>
     * </dl>
     *
     * ## エラー/例外
     * keyNameに指定されたキーが存在しない配列要素がある場合はExceptionをthrowします。
     */
    public static function getMap(array $ary, string $keyName, $valName = null) :array
    {
        $hash = [];
        foreach ($ary as $data) {
            if (!isset($data[$keyName])) {
                throw new \Exception("存在しないキー${keyName}が指定されました。");
            }
            if ($valName && isset($data[$valName])) {
                $hash[$data[$keyName]] = $data[$valName];
            } else {
                $hash[$data[$keyName]] = $data;
            }
        }
        return $hash;
    }

    public static function isUrl(string $url) :bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return !($scheme === NULL || $scheme === FALSE);
    }

    /**
     * phpファイルからnamespaceを取得する。
     *
     * ## パラメータ
     * <dl>
     *   <dt>phpFilePath</dt>
     *     <dd>PHPファイルのパスを指定します。</dd>
     * </dl>
     *
     * ## 戻り値
     * namespaceを返します。取得に失敗した場合はnullを返します。
     */
    public static function getNameSpace(string $phpFilePath)
    {
        $nameSpace = null;
        if (file_exists($phpFilePath)) {
            $fp = fopen($phpFilePath, 'r');
            while ($line = fgets($fp)) {
                if (strpos($line, 'namespace ') !== false) {
                    $nameSpace = rtrim(substr($line, strpos($line, 'namespace ') + 10), " ;\n");
                    break;
                }
            }
            fclose($fp);
        }
        return $nameSpace;
    }

    /**
     * ファイルの一覧を取得する。
     *
     * ## 説明
     * 指定されたディレクトリのファイルを再帰的に取得して返します。
     */
    public static function getFileList(array $directories): array
    {
        $paths = [];
        foreach ($directories as $dir) {
            $it = new \RecursiveDirectoryIterator($dir,
                \FilesystemIterator::CURRENT_AS_FILEINFO |
                \FilesystemIterator::KEY_AS_PATHNAME |
                \FilesystemIterator::SKIP_DOTS);
            $iterator = new \RecursiveIteratorIterator($it);
            foreach ($iterator as $path => $info) {
                if ($info->isFIle()) {
                    $paths[] = $path;
                }
            }
        }
        return $paths;
    }

    /**
     * 先頭から文字列を除去する。
     */
    public static function leftRemove(string $str, string $prefix): string
    {
        if (($pos = mb_strpos($str, $prefix)) === 0) {
            return mb_substr($str, mb_strlen($prefix));
        }
        return $str;
    }

    /**
     * POSTパラメータを取得
     */
    public static function getPost(string $name, $default = null)
    {
        return $_POST[$name] ?? $default;
    }

    /**
     * 配列かどうかを判定する。（連想配列はfalseとなる）
     */
    public static function isArray($obj)
    {
        if (!is_array($obj)) {
            return false;
        }
        $size = count($obj);
        for ($i = 0; $i < $size; $i++) {
            if (!key_exists($i, $obj)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 配列の要素にオブジェクトまたは配列を含むか調べる。
     */
    public static function isObjectArray($obj)
    {
        if (!is_array($obj)) {
            return false;
        }
        foreach ($obj as $key => $val) {
            if (is_array($val) || is_object($val)) {
                return true;
            }
        }
        return false;
    }


    /**
     * キャメルケースに変換する。
     *
     * @param $str
     * @return string
     */
    public static function toCamel($str, $lcfirst = false): string
    {
        $str = ucwords($str, '_');
        if ($lcfirst) {
            return lcfirst(str_replace('_', '', $str));
        } else {
            return str_replace('_', '', $str);
        }
    }

    /**
     * スネークケースに変換
     *
     * @param $str
     * @return string
     */
    public static function toSnake($str): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $str)), '_');
    }
}