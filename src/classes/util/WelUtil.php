<?php
namespace ellsif\WelCMS;

use ellsif\DataAccess;
use ellsif\FileAccess;
use ellsif\util\FileUtil;
use ellsif\SqliteAccess;
use ellsif\util\StringUtil;

class WelUtil
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
    public static function getDataAccess($driver): DataAccess
    {
        // TODO MySQLやCSV対応
        if ($driver !== 'sqlite') {
            throw new Exception("${driver}はサポートされていません。");
        }
        return new SqliteAccess();
    }

    /**
     * FileAccessクラスのインスタンスを取得する。
     *
     * ## 説明
     *
     */
    public static function getFileAccess() :FileAccess
    {
        $config = Pocket::getInstance();
        $fileAccessClass = $config->settingFileAccessClass();
        return new $fileAccessClass();
    }

    /**
     * Repositoryのインスタンスを取得する。
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
        $config = Pocket::getInstance();
        $modelPath = $config->dirRepository() . $name . 'Repository.php';
        $fileExists = file_exists($modelPath);
        if ($fileExists) {
            $nameSpace = FileUtil::getNameSpace($modelPath);
            $className = "${nameSpace}\\${name}Repository";
            return new $className($name);
        }

        // テーブルがあるならば、汎用Entityを返す
        $dataAccess = WelUtil::getDataAccess($config->dbDriver());
        if ($dataAccess->isTableExists($name)) {
            return new \ellsif\WelCMS\Repository($name);
        }

        if ($fileExists) {
            throw new \InvalidArgumentException("${name}Repositoryの初期化に失敗しました。", 500);
        } else {
            throw new \InvalidArgumentException(
                "${name}Repositoryの初期化に失敗しました。${modelPath}が存在しません。",
                500
            );
        }
    }

    /**
     * WebPartを取得する。
     *
     * ## 説明
     *
     */
    public static function loadPart(string $name): WebPart
    {
        $config = Pocket::getInstance();
        $partPath = $config->dirSystem() . 'classes/parts/' . $name . '.php';
        if (file_exists($partPath)) {
            $nameSpace = FileUtil::getNameSpace($partPath);
            $className = "${nameSpace}\\${name}";
            return new $className();
        }
        throw new \RuntimeException("${name}WebPartの初期化に失敗しました", 500);
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
        $config = Pocket::getInstance();
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
        $config = Pocket::getInstance();
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
        $config = Pocket::getInstance();
        date_default_timezone_set($config->timeZone());
        return date('Y-m-d H:i:s');
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
        if (!WelUtil::isUrl($url)) {
            if (intval($_SERVER['REMOTE_PORT']) == 443) {
                $url = 'https://' . WelUtil::getHostname() . $url;
            } else {
                $url = 'http://' . WelUtil::getHostname() . $url;
            }
        }
        $urlInfo = parse_url($url);
        if ($urlInfo !== FALSE) {
            $path = $urlInfo['path'];
            if (Pocket::getInstance()->dirWelCMS()) {
                // index.phpがルートディレクトリに無い場合
                echo $path;
                $path = StringUtil::leftRemove($path, '/' . Pocket::getInstance()->dirWelCMS());
                $urlInfo['path'] = $path;
            }
            $paths = array_filter(explode('/', $path), "strlen");
            $urlInfo['paths'] = array_values($paths);

            $urlInfo['params'] = [];
            if (isset($urlInfo['query'])) {
                $urlInfo['params'] = WelUtil::parseQuery($urlInfo['query']);
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
     * 個別ページの認証を行う。
     *
     * ## 説明
     * 以下の場合に例外をthrowします。
     *
     * - ページが存在しない（404）
     * - ページが非公開かつ管理者ログインしていない（404）
     * - ページに認証設定されており認証されたグループでログインしていない（401）
     */
    public static function authenticatePage($page)
    {
        $config = Pocket::getInstance();
        if (is_string($page)) {
            $pageEntity = WelUtil::getRepository('Page');
            $pages = $pageEntity->list(['path' => trim($page, '/')]);
            $page = $pages[0] ?? null;
        }
        if ($page) {
            if (intval($page['published']) === 0) {
                throw new \InvalidArgumentException('Not Found', 404);
            } elseif (!WelUtil::isAllowed($config->loginUser(), $page)) {
                throw new \InvalidArgumentException('Unauthorized', 401);
            }
        } else {
            throw new \InvalidArgumentException('Not Found', 404);
        }
    }

    /**
     * 個別ページの表示許可があるか判定する。
     */
    public static function isAllowed($user, $page): bool
    {
        $userGroupRepo = WelUtil::getRepository('UserGroup');
        $userGroups = $userGroupRepo->getUserGroups($user['id']);
        if (count($userGroups)) {
            $allowedUserGroupIds = array_filter(explode('|', $page['allowedUserGroupIds']), 'strlen');
            $groupIds = array_column($userGroups, 'id');
            foreach($groupIds as $groupId) {
                if (array_search($groupId, $allowedUserGroupIds) >= 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Sessionテーブルからセッション情報を取得
     */
    public static function getSession()
    {
        $sessionRepository = WelUtil::getRepository('Session');
        $sessions = $sessionRepository->list(['sessid' => session_id()]);
        if (count($sessions) > 0) {
            return $sessions[0];
        } else {
            return null;
        }
    }

    /**
     * 指定のURLへリダイレクトします。
     */
    public static function redirect($path, $code = 301)
    {
        $url = (WelUtil::isUrl($path)) ? $path : WelUtil::getUrlBase() . StringUtil::leftRemove($path, '/');
        header("HTTP/1.1 ${code}");
        header( "Location: " . $url);
        exit;
    }

    /**
     * ベースURLを取得します。
     * TODO 階層下げた場合の対応が必要。
     */
    public static function getUrlBase()
    {
        $urlInfo = Pocket::getInstance()->varUrlInfo();
        $urlBase = $urlInfo['scheme'] . '://' . $urlInfo['host'];
        if (intval($urlInfo['port']) != 80) {
            $urlBase .= ':' . $urlInfo['port'];
        }
        return $urlBase;
    }
}