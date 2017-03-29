<?php

namespace ellsif\WelCMS;
use ellsif\Singleton;
use ellsif\util\StringUtil;

/**
 * CMSの設定・共通オブジェクトを管理するクラス。
 *
 * ## 説明
 * システム全体を通して利用する可能性のある設定・オブジェクトをまとめて管理するクラスです。<br>
 * Singletonとして実装されています。<br>
 * グローバル変数の代用として利用します。
 *
 * 各種パラメータへのgetter、setterを提供しますが、<br>
 * システムの初期化が完了した後にsetter呼び出しが行われた場合、例外をthrowします。（基本的にgetterとしての利用を想定しています）
 *
 * getInstance()によりインスタンスを取得して利用します。
 *
 *     $config = Config::getInstance();
 *     echo $config->settingSiteName(); // サイト名を表示
 */
class Pocket
{
    use Singleton;

    /**
     * インスタンスを取得する。
     */
    public static function getInstance() : Pocket
    {
        return self::instance();
    }

    // 設定の実体
    private $config;

    private $_locked;

    // 設定のプレフィックス
    const PREFS = ['db', 'dir', 'var', 'setting'];

    protected function __construct()
    {
        $this->reset();
    }

    public function reset()
    {

        $this->config = [
            'db' => [
                'Driver' => 'sqlite',
                'Hostname' => 'localhost',
                'Username' => '',
                'Password' => '',
                'Database' => '',
                'Charset' => 'utf8',
                'SystemTables' => [
                    'Setting' => 'システムの基本設定。',
                    'Content' => 'コンテンツデータ。',
                    'Page' => '個別ページ情報。',
                    'Template' => 'テンプレート情報。',
                    'Plugin' => 'プラグイン情報。',
                    'PluginContent' => 'プラグインが利用するコンテンツデータを格納します。',
                    'PluginSetting' => 'プラグインの設定。',
                    'Session' => 'セッション情報。',
                    'User' => 'ユーザー情報。',
                    'UserGroup' => 'グループ情報。',
                    'FormReservation' => 'フォーム予約テーブル。',
                ],
                'ApplicationTables' => [

                ],
            ],
            'dir' => [
                'WelCMS' => dirname(__FILE__, 3) . '/',
                'System' => '',
                'App' => 'app',
                'Plugins' => 'plugins',
                'Initialize' => '',
                'Log' => 'logs',
                'Repository' => dirname(__FILE__, 2) . '/repository/',
                'View' => dirname(__FILE__, 3) . '/views/',
                'Part' => 'views/parts',
                'EntityApp' => 'classes/entity',
                'ViewApp' => 'views',
                'PartApp' => 'views/parts',
            ],
            'setting' => [
                'FileAccessClass' => '\ellsif\LocalFileAccess', // ファイル管理に利用するファイルアクセスクラス
            ],
            'var' => [
                'isAdminPage' => false,
                'isPage' => false,
                'isTopPage' => false,
                'Params' => [],
                'Validated' => false,
                'FormData' => [],
                'FormError' => [],
                'Plugins' => [],
                'FormToken' => '',
                'AlertError' => [],
                'AlertWarning' => [],
                'AlertInfo' => [],
                'AlertSuccess' => [],
                'FooterJsBefore' => [],
                'FooterJsAfter' => [],
                'CssBefore' => [],
                'CssAfter' => [],
                'RiotJs' => [],
                'Options' => [],
                'Action' => null,
                'ActionMethod' => null,
                'Service' => null,
                'Auth' => null,
            ],
            'default' => [  // defaultはprefix無し
                'salt' => '',
                'session' => [],
                'runMode' => 'development',
                'logLevel' => 'debug',
                'timeZone' => 'Asia/Tokyo',
                'noticeMethods' => ['Email'],
                'printFormats' => ['json','xml','svg','pdf','atom','csv'],
                'loginUser' => null,
                'loginManager' => null,
                'isAdmin' => false,
            ],
        ];
    }

    // プロパティのget/set用
    protected function getset($name, $val, $pref = '', $suf = '')
    {
        if (count($val) == 0) {
            $val = $this->_get($name);
            if (is_string($val)) {
                $val = StringUtil::suffix($pref . $val, $suf);
            }
            return $val;
        }
        $this->_set($name, $val[0]);
        return null;
    }

    /**
     * $configへのアクセッサ。
     * 実際には本メソッドを経由し、$configから値を取得する。
     */
    protected function _get(string $name)
    {
        foreach(Pocket::PREFS as $pref) {
            if (strpos($name, $pref) === 0) {
                // DB関連
                $conf = $this->config[$pref];
                $label = substr($name, strlen($pref));
                if (isset($conf[$label])) {
                    return $conf[$label];
                }
            }
        }

        $conf = $this->config['default'];
        if (isset($conf[$name])) {
            return $conf[$name];
        }

        return null;
    }

    /**
     * $configへのアクセッサ。
     * publicメンバとして定義してある変数名のみ設定可能。
     * lock済みの場合例外をthrowする。
     *
     * @param string $name
     * @param $value
     */
    protected function _set(string $name, $value)
    {
        if ($this->_locked) {
            throw new \Exception('Configはロックされています。');
        }
        foreach(Pocket::PREFS as $pref) {
            if (strpos($name, $pref) === 0) {
                // DB関連
                $conf = $this->config[$pref];
                $namePref = substr($name, 0, strlen($pref));
                $label = substr($name, strlen($pref));
                if ($pref === $namePref && (isset($conf[$label]) || is_callable([$this, $name]))) {
                    if ($pref === 'dir') {
                        $this->config[$pref][$label] = StringUtil::suffix($value, '/');
                    } else {
                        $this->config[$pref][$label] = $value;
                    }
                    return;
                }
            }
        }

        if (array_key_exists($name, $this->config['default'])) {
            $this->config['default'][$name] = $value;
            return;
        }
        throw new \Exception("Configを更新できません。${name}は無効なプロパティです。");
    }

    /**
     * Configをロックする。
     *
     * ## 説明
     * 基本システムの初期化及び、プラグインの初期化が完了した時点で呼ばれるメソッドです。
     * 本メソッドが呼ばれると以降のsetter呼び出しで例外がthrowされるようになります。
     * ロックを解除することはできません。（システムで利用する想定のため、本メソッドを操作することは通常はありません）
     */
    public function lock()
    {
        $this->_locked = true;
    }

    /**
     * デバッグダンプを取得する。
     *
     * ## 説明
     * 現在のconfigの内容をjson形式で返します。
     */
    public function getDebugDump() {
        return json_encode($this->config);
    }


    /**
     * セッション情報のgetter/setter
     *
     * ## 説明
     * セッション情報を取得/設定します。
     *
     *     $session = $config->session();
     *     echo $sessin['id'];
     */
    public function session(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 実行モードのgetter/setter。
     *
     * ## 説明
     * 実行モードを設定します。
     * 実行モードの変更は基本的にconf.phpで行います。デフォルトの設定はdevelopmentです。
     *
     *     $config->runMode('production'); // 本番環境として実行
     *     $config->runMode('development'); // 開発環境として実行
     *     $config->runMode('test'); // テスト環境として実行
     *
     */
    public function runMode(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * ログレベルのgetter/setter。
     *
     * ## 説明
     * ログレベルを設定します。
     * ログレベルの変更は基本的にconf.phpで行います。デフォルトの設定はdebugです。
     */
    public function logLevel(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * タイムゾーンのgetter/setter。
     *
     * ## 説明
     * システムが利用するタイムゾーンを取得/設定します。
     * デフォルトはAsia/Tokyoとなります。本設定は日付取得のユーティリティ関数から参照されます。
     */
    public function timeZone(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 通知手段のgetter/setter。
     *
     * ## 説明
     * ※通知機能は未実装です。<br>
     * 通知機能が利用する方式を設定します。chatwork、slack、メールなどを想定しています。
     */
    public function noticeMethods(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 利用可能な出力フォーマットのgetter/setter。
     *
     * ## 説明
     * 利用可能なフォーマット(拡張子)を配列で設定します。デフォルトは['json', 'xml', 'svg', 'pdf', 'atom', 'csv']です。<br>
     * 例外として"html","htm","php"は定義に含まれていなくても許可対象となります。<br>
     * 実際に出力するにはPrinter側での出力処理の実装が必要になります。
     */
    public function printFormats(...$val) { return $this->getset(__FUNCTION__, $val); }

    public function loginUser(...$val) { return $this->getset(__FUNCTION__, $val); }

    public function loginManager(...$val) { return $this->getset(__FUNCTION__, $val); }

    public function isAdmin(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * Home画面のURLのgetter/setter。
     *
     * ## 説明
     * 管理機能のCMS設定で設定されたホームURLの取得/設定を行います。
     * 値はsettingテーブルからCMS初期化時に設定されます。開発者側でのsetは通常利用しません。
     */
    public function settingUrlHome(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * サイト名のgetter/setter。
     *
     * ## 説明
     * 管理機能のCMS設定で設定されたサイト名の取得/設定を行います。
     * 値はsettingテーブルからCMS初期化時に設定されます。開発者側でのsetは通常利用しません。
     */
    public function settingSiteName(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * アクティベーションフラグのgetter/setter。
     *
     * ## 説明
     * CMSのアクティベーションフラグの取得/設定を行います。
     * 値はsettingテーブルからCMS初期化時に設定されます。開発者側でのsetは通常利用しません。
     */
    public function settingActivated(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * ファイルアクセス用Classのgetter/setter。
     *
     * ## 説明
     *
     * 値はsettingテーブルからCMS初期化時に設定されます。開発者側でのsetは通常利用しません。
     */
    public function settingFileAccessClass(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * DBドライバのgetter/setter。
     *
     * ## 説明
     * DBドライバの設定を取得/設定します。
     *
     * ## 例
     *     $config = Config::getInstance();
     *     $config->dbDriver('sqlite');
     */
    public function dbDriver(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * DBのホスト名のgetter/setter。
     *
     * @var string $dbHostname 'localhost'固定（現時点で未使用）
     */
    public function dbHostname(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * DBのユーザー名のgetter/setter。
     *
     * @var string $dbUsername 現時点で未使用
     */
    public function dbUsername(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * DBのパスワードのgetter/setter。
     *
     * @var string $dbPassword 現時点で未使用
     */
    public function dbPassword(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * DB名のgetter/setter。
     *
     * @var string $dbDatabase sqliteのファイル名（初期値は'database.sqlite'）
     */
    public function dbDatabase(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * DB、charsetのgetter/setter。
     *
     * @var string $dbCharset 'utf8'で今のところ固定
     */
    public function dbCharset(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * システムが利用するテーブル情報ののgetter/setter。
     */
    public function dbSystemTables(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * アプリケーションが利用するテーブル情報ののgetter/setter。
     */
    public function dbApplicationTables(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * WelCMSのルートパスのgetter/setter。
     *
     * ## 説明
     * WelCMSを配置したディレクトリ（main.phpの存在するディレクトリ）の完全パスを取得、設定します。
     * 基本的に値を更新する必要はありません。
     *
     * ## 返り値
     * 引数が未指定の場合のみパスを返します。
     *
     *     /your/webroot/welcms/
     */
    public function dirWelCMS(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * Webのルートパスのgetter/setter。
     *
     * ## 説明
     * Webのルートディレクトリの完全パスを取得、設定します。<br>
     * デフォルトではdirWelCMS()の一階層上のディレクトリとなりますが、
     * welcmsディレクトリをWebルートの直下に配置しない場合はconf.phpに設定を追加する必要があります。
     *
     * ## 返り値
     * 引数が未指定の場合のみパスを返します。
     *
     *     /your/webroot/
     */
    public function dirRoot(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * システムディレクトリパスのgetter/setter。
     *
     * ## 説明
     * システムディレクトリのパスを取得、設定します。
     * デフォルトはsystemとなります。
     */
    public function dirSystem(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * アプリケーションディレクトリパスのgetter/setter。
     *
     * ## 説明
     * アプリケーションディレクトリのパスを取得、設定します。
     * アプリケーションディレクトリはシステムディレクトリと同じ構成とし、カスタマイズ用のファイルを格納します。<br>
     * 同名のファイルが存在する場合、アプリケーションディレクトリ以下のファイルがシステムディレクトリより優先して利用されます。<br>
     */
    public function dirApp(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * プラグインディレクトリパスのgetter/setter。
     *
     * ## 説明
     * プラグインディレクトリのパスを取得、設定します。
     * 設定はWelCMSを配置したディレクトリからの相対パスで行い、取得は絶対パスで行われる点に注意してください。<br>
     * デフォルトはpluginsとなります。
     *
     *     $config->dirPlugins('plgin');
     *     echo $config->dirPlugins();  // /your/webroot/welcms/plgin
     */
    public function dirPlugins(...$val) { return $this->getset(__FUNCTION__, $val, $this->dirWelCMS(), '/'); }

    /**
     * マイグレーションファイル格納ディレクトリパスのgetter/setter。
     *
     * ## 説明
     * マイグレーションファイル格納ディレクトリのパスを取得、設定します。
     * 設定はWelCMSを配置したディレクトリからの相対パスで行い、取得は絶対パスで行われる点に注意してください。<br>
     * デフォルトはmigrationとなります。<br>
     * 本機能は現在、CMSのアクティベーションのみで利用しています。将来的にCMSのバージョンアップに利用されるようになります。
     */
    public function dirInitialize(...$val) { return $this->getset(__FUNCTION__, $val, '', '/'); }

    /**
     * Viewファイル格納ディレクトリパスのgetter/setter。
     *
     * ## 説明
     * Viewファイル格納ディレクトリのパスを取得、設定します。
     * 設定はWelCMSを配置したディレクトリからの相対パスで行い、取得は絶対パスで行われる点に注意してください。<br>
     * デフォルトはviewsとなります。<br>
     */
    public function dirView(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * Viewファイル格納ディレクトリパスのgetter/setter、アプリケーション版。
     *
     * ## 説明
     * dirView()のアプリケーション版です。
     * デフォルトはviewsとなります。<br>
     */
    public function dirViewApp(...$val) { return $this->getset(__FUNCTION__, $val, $this->dirView(), '/'); }


    /**
     * View部品ファイル格納ディレクトリパスのgetter/setter。
     *
     * ## 説明
     * View部品ファイル格納ディレクトリのパスを取得、設定します。
     * 設定はWelCMSを配置したディレクトリからの相対パスで行い、取得は絶対パスで行われる点に注意してください。<br>
     * デフォルトはviews/partsとなります。<br>
     */
    public function dirPart(...$val) { return $this->getset(__FUNCTION__, $val, $this->dirSystem(), '/'); }

    /**
     * View部品ファイル格納ディレクトリパスのgetter/setter、アプリケーション版。
     *
     * ## 説明
     * dirPart()のアプリケーション版です。
     * デフォルトはviewsとなります。<br>
     */
    public function dirPartApp(...$val) { return $this->getset(__FUNCTION__, $val, $this->dirPart(), '/'); }

    /**
     * Entityファイル格納ディレクトリパスのgetter/setter。
     *
     * ## 説明
     * View部品ファイル格納ディレクトリのパスを取得、設定します。
     * 設定はWelCMSを配置したディレクトリからの相対パスで行い、取得は絶対パスで行われる点に注意してください。<br>
     * デフォルトはviews/partsとなります。<br>
     */
    public function dirRepository(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * View部品ファイル格納ディレクトリパスのgetter/setter、アプリケーション版。
     *
     * ## 説明
     * dirEntity()のアプリケーション版です。
     * デフォルトはviews/partsとなります。<br>
     */
    public function dirEntityApp(...$val) { return $this->getset(__FUNCTION__, $val, $this->dirApp(), '/'); }

    /**
     * ログファイル格納ディレクトリパスのgetter/setter。
     *
     * ## 説明
     * ログファイル格納ディレクトリのパスを取得、設定します。<br>
     * システム、アプリケーションログの出力先は同じディレクトリになります。<br>
     * 設定はWelCMSを配置したディレクトリからの相対パスで行い、取得は絶対パスで行われる点に注意してください。<br>
     * デフォルトはlogsとなります。<br>
     */
    public function dirLog(...$val) { return $this->getset(__FUNCTION__, $val); }

    ////////////////////////////////////////////////////////////////////
    // 以下、動的な設定値。
    // ページの表示毎に異なる値になる。
    ////////////////////////////////////////////////////////////////////

    /**
     * 管理ページ表示フラグのgetter/setter。
     */
    public function varIsAdminPage(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 個別ページ表示フラグのgetter/setter。
     */
    public function varIsPage(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * TOPページ表示フラグのgetter/setter。
     */
    public function varIsTopPage(...$val) { return $this->getset(__FUNCTION__, $val); }


    /**
     * ページのタイトルのgetter/setter。
     *
     * ## 説明
     * ページのタイトルの取得/設定を行います。
     */
    public function varPageTitle(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * HTTPリクエストメソッドのgetter/setter。
     *
     * ## 説明
     * HTTPリクエストメソッドの取得/設定を行います。
     * 'GET','HEAD', 'POST', 'PUT', 'DELETE'など。
     * システム初期化時に$_SERVER['REQUEST_METHOD']の取得結果を大文字にした文字列が設定されます。
     */
    public function varRequestMethod(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * リクエストされたURLのgetter/setter。
     *
     * ## 説明
     * システム初期化時に$_SERVER['REQUEST_URI']の内容が設定されます。
     */
    public function varCurrentUrl(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * リクエストされたパスのgetter/setter。
     *
     * ## 説明
     * システム初期化時に$_SERVER['REQUEST_URI']からサーバ名を除いた文字列が設定されます。
     */
    public function varCurrentPath(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * リクエストされたURL情報のgetter/setter。
     *
     * ## 説明
     * システム初期化時に$_SERVER['REQUEST_URI']のパース結果を配列に格納したものが設定されます。
     *
     * ## 例
     *     $config = Config::getInstance();
     *     $urlInfo = $config->varUrlInfo();
     *     echo $urlInfo['scheme'];
     *     echo $urlInfo['host'];
     *     echo $urlInfo['port'];
     *     echo $urlInfo['path'];
     *     echo $urlInfo['query'];
     *     echo $urlInfo['paths'];
     *     echo $urlInfo['params'];
     */
    public function varUrlInfo(...$val) { return $this->getset(__FUNCTION__, $val); }

    // GET、URLによって設定されたパラメータ（同名がある場合は後ろのもので上書き）
    //public function varGetParams(...$val) { return $this->getset(__FUNCTION__, $val); }

    // POSTによって設定されたパラメータ
    //public function varPostParams(...$val) { return $this->getset(__FUNCTION__, $val); }

    // 自動バリデーションが実行されたか（デフォルトfalse）
    //  TODO バリデーション機能の実装については検討が必要になる・・・
    public function varValidated(...$val) { return $this->getset(__FUNCTION__, $val); }

    // バリデーションが行われた場合、バリデーションの結果(bool)
    // TODO これもバリデーションの設計が。。。
    public function varValid(...$val) { return $this->getset(__FUNCTION__, $val); }

    // formStart()によって作られたformからPOSTされた場合、Formのデータやバリデーション結果が入る
    // ex. ['input1'=>['valid'=>false, 'value'=>'bad input', 'error'=>'数字を入力してください。']]
    // TODO 同じくバリデーション関連
    public function varFormData(...$val) { return $this->getset(__FUNCTION__, $val); }

    // フォーム全体に影響するエラーメッセージ（ログインエラーなど）
    // TODO これもバリデーション
    public function varFormError(...$val) { return $this->getset(__FUNCTION__, $val); }

    // 更新用フォームの場合の対象ID
    // TODO これもバリデーション
    public function varFormTargetId(...$val) { return $this->getset(__FUNCTION__, $val); }

    // TODO これもバリデーション
    public function varFormToken(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 実行中サービスのgetter/setter。
     *
     * ## 説明
     * 現在実行中のサービスの名称を取得/設定する。
     * Routerによって判定された結果が入る。
     */
    public function varService(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 実行中サービスClassのgetter/setter。
     *
     * ## 説明
     * 現在実行中のサービスのClass名称を取得/設定する。
     * Routerによって判定された結果が入る。
     */
    public function varServiceClass(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 実行中アクションのgetter/setter。
     *
     * ## 説明
     * 現在実行中のアクション名を取得/設定する。
     * Routerによって判定された結果が入る。
     */
    public function varAction(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 実行中アクションメソッドのgetter/setter。
     *
     * ## 説明
     * 現在実行中のアクション名(サービスクラスのメソッド名)を取得/設定する。
     * Routerによって判定された結果が入る。<br>
     *
     * varActionにlist,add,editなどが設定され、
     * varActionMethodにはgetList,postAddAdmin,getEditAdminなど、実際のメソッド名が設定されます。
     * （詳細はRouterの規約を参照してください）
     */
    public function varActionMethod(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * アクションに引き渡すパラメータのgetter/setter。
     *
     * ## 説明
     * アクションに引き渡すパラメータです。
     */
    public function varActionParams(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * Printerのgetter/setter。
     */
    public function varPrinter(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * プリンターが利用するフォーマットのgetter/setter。
     */
    public function varPrinterFormat(...$val) { return $this->getset(__FUNCTION__, $val); }


    /**
     * 認証方法のgetter/setter。
     *
     * ## 説明
     * 認証が必要なアクションを実行する場合に認証方法が設定されます。
     * 以下のいずれか、またはnullとなります。
     *
     * - Admin : システム管理者権限（すべての権限を有する）
     * - Manager : 管理者権限（ユーザーデータを作成できない）
     * - User : ユーザー権限（登録ユーザー）
     */
    public function varAuth(...$val) { return $this->getset(__FUNCTION__, $val); }


    // プラグイン名のリスト
    // TODO プラグインの扱いも検討必要
    public function varPlugins(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 画面に表示するアラートメッセージ（エラー）のgetter/setter。
     *
     * ## 説明
     * 画面に表示するアラートメッセージを格納する配列の設定/取得を行います。<br>
     * アクション内でエラーが発生した場合はappendAlert()によりエラーメッセージを追加します。
     *
     * ## 例
     * エラーメッセージの取得。
     *
     *     $config = Config::getInstance();
     *     foreach ($config->varAlertError() as $error) {
     *       echo $error;
     *     }
     *
     * エラーメッセージの追加。
     *
     *     $config->appendAlert('メールアドレスの形式が不正です。', 'error');
     *
     */
    public function varAlertError(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 画面に表示するアラートメッセージ（警告）のgetter/setter。
     *
     * ## 説明
     *
     */
    public function varAlertWarning(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 画面に表示するアラートメッセージ（インフォ）のgetter/setter。
     */
    public function varAlertInfo(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * 画面に表示するアラートメッセージ（成功）のgetter/setter。
     */
    public function varAlertSuccess(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * アラートメッセージを追加する。
     *
     * ## 説明
     * エラーレベルを指定してアラートメッセージを追加します。
     * メッセージはvarAlert[ErrorLevel]メソッド（varAlertError, varAlertInfo, varAlertWarning, varAlertSuccess）から取得します。
     *
     * ## パラメータ
     * <dl>
     *   <dt>message</dt>
     *   <dd>
     *     任意のメッセージを指定します。
     *   </dd>
     *   <dt>level</dt>
     *   <dd>
     *     エラーレベルを指定します。error, warning, success, infoの指定が可能です。それ以外が指定された場合はinfoとして処理されます。
     *   </dd>
     * </dl>
     *
     * ## 例
     *
     *     $config = Config::getInstance();
     *     $config->appendAlert('フォームの有効期限はあと'.$min.'分です。');  // 省略時はinfoで登録
     *     $config->appendAlert('メールアドレスの形式が不正です。', 'error');
     */
    public function appendAlert(string $message, string $level = 'info')
    {
        $config = Pocket::getInstance();
        if (strcasecmp($level, 'error') === 0) {
            $alerts = $config->varAlertError();
            $alerts[] = $message;
            $config->varAlertError($alerts);
        } elseif (strcasecmp($level, 'warning') === 0) {
            $alerts = $config->varAlertWarning();
            $alerts[] = $message;
            $config->varAlertWarning($alerts);
        } elseif (strcasecmp($level, 'success') === 0) {
            $alerts = $config->varAlertSuccess();
            $alerts[] = $message;
            $config->varAlertSuccess($alerts);
        } else {
            $alerts = $config->varAlertInfo();
            $alerts[] = $message;
            $config->varAlertInfo($alerts);
        }
    }

    /**
     * カスタムCSSのgetter/setter。（システムのCSSより前）
     *
     * ## 説明
     * linkタグにて挿入するCSSの配列を取得/設定します。<br>
     * 本メソッドで扱うCSSはシステムが利用するCSSよりも前に出力されます。
     */
    public function varCssBefore(...$val) { return $this->getset(__FUNCTION__, $val); }

    /**
     * カスタムCSSを追加する。（システムのCSSより後）
     *
     * ## 説明
     * linkタグにて挿入するCSSの配列を取得/設定します。<br>
     * 本メソッドで扱うCSSはシステムが利用するCSSよりも後に出力されます。
     */
    public function varCssAfter(...$val) { return $this->getset(__FUNCTION__, $val); }

    // bodyの後に挿入するJS
    public function varFooterJsBefore(...$val) { return $this->getset(__FUNCTION__, $val); }
    public function varFooterJsAfter(...$val) { return $this->getset(__FUNCTION__, $val); }

    public function varRiotJs(...$val) { return $this->getset(__FUNCTION__, $val); }

    // 自由に使って下さい
    public function varOptions(...$val) { return $this->getset(__FUNCTION__, $val); }


    public function addCssBefore($path) {
        $this->config['var']['CssBefore'][] = $path;
    }

    public function addCssAfter($path) {
        $this->config['var']['CssAfter'][] = $path;
    }

    public function addVarFooterJsBefore($path) {
        $this->config['var']['FooterJsBefore'][] = $path;
    }

    public function addVarFooterJsAfter($path) {
        $this->config['var']['FooterJsAfter'][] = $path;
    }

    public function addRiotJs($path) {
        $this->config['var']['RiotJs'][] = $path;
    }

}