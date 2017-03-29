<?php
namespace ellsif\WelCMS;

// DB初期設定
$pocket = Pocket::getInstance();

$dataAccess = WelUtil::getDataAccess($pocket->dbDriver());

$dataAccess->createTable('contents', array(
    'name' => 'TEXT',
    'content_type' => "TEXT DEFAULT 'text/plain'",
    'body_type' => "TEXT DEFAULT 'text'", // text, path
    'body' => 'TEXT',
    'options' => 'TEXT',
    'status' => 'TEXT'  // 何に使うか不明
));

$dataAccess->createTable('templates', array(
    'media_type' => "TEXT DEFAULT 'text/html'",
    'is_parts' => "INTEGER DEFAULT 0",  /* 部品かどうか。ちょっと用途を忘れている。 */
    'name' => 'TEXT', /* テンプレート名 */
    'body' => 'TEXT', /* テンプレート本体(HTML) */
    'options' => 'TEXT',  /* これはなんだっけ */
    'body_template' => 'TEXT', /* テンプレート化されたもの(JSON) */
));

// 個別ページ
$dataAccess->createTable('pages', array(
    'template_id' => 'INTEGER',
    'name' => 'TEXT', // 管理用の名前
    'path' => 'TEXT', // URL（相対）
    'options' => 'TEXT',  // keywordとかを入れる
    'body_cache' => 'TEXT',
    'published' => 'INTEGER DEFAULT 0',  // 公開フラグ（0:非公開、1:公開、2:制限付き）
    'allowedUserGroupIds' => 'TEXT',  // 制限付きの場合に許可されるユーザーグループのID（パイプ区切り）
));

$dataAccess->createTable('page_contents', array(
    'page_id' => 'INTEGER',
    'content_id' => 'INTEGER'
));

$dataAccess->createTable('Setting', array(
    'content_type' => "TEXT DEFAULT 'text/plain'",
    'value_type' => "TEXT DEFAULT 'text'",
    'label' => "TEXT",
    'name' => "TEXT",
    'value' => "TEXT",
    'options' => "TEXT",
    'use_in_page' => "INTEGER DEFAULT 0"
));


/**
 * pluginの仕様
 * pluginsディレクトリがベースだが・・・
 *
 * １．インストール
 *  ・圧縮ファイルまたはフォルダごとアップロードして管理画面からインストール。（zipの場合は自動展開）
 *  ・管理画面から行う（実質zipを落として上の動作、プラグインディレクトリが必要だが、とりあえず公式で用意）
 *  ・このタイミングでpluginsに登録する。実施のタイミングはプラグイン管理→インストール
 * ２．有効化
 *  ・管理画面から行う
 * ３．プラグイン毎の設定
 *  ・管理画面から行う（あれば）
 * ４．バージョンアップは？
 *  ・これも管理画面から行う
 *  ・設定項目は通常のインストールと同じでpluginSettingsをにinsertし、同名の項目については旧バージョンのvalueを引き継ぐ
 * ５．バージョン戻しは？
 *  ・これも管理画面から行いたい
 *
 * pluginsテーブルには同名のpluginで別のバージョンの設定が混在することになる。
 * フォルダ構成的にはplugin/version/*とかになるのか。。。
 */
$dataAccess->createTable('plugins', array(
    'name' => "TEXT", /* 表示名（例：お問い合わせフォーム） */
    'class' => "TEXT", /* namespaceを含むクラス名 */
    'version' => "TEXT", /* versionがいるかどうか不明・・・ */
    'supported' => "TEXT",  /* サポートするWelCoMeSのバージョン */
    'released' => "TEXT", /* リリースされた日(Y-m-d h:i:s) */
    'author' => "TEXT", /* 作者情報。jsonで入れて[{'name':'Sakai', 'website':'https://ellsif.com/', 'contact':''},{}] */
    'active' => "INTEGER",  /* 1ならactive（同classでのactiveは1つまで） */
));

// このテーブルは何用だろうか。。。
// 写真を扱うプラグインとかかな。。。
$dataAccess->createTable('pluginContents', array(
    'plugin_id' => "INTEGER",
    'name' => "TEXT", /* 項目名（例：お問い合わせパターン１） */
    'content_type' => "TEXT DEFAULT 'text/plain'",  /* application/jsonとか */
    'body_type' => "TEXT DEFAULT 'text'", /* textでいいか、これは画像のパスとかだったら"path"を入れる */
    'body' => "TEXT", /* 本体 */
    'options' => "TEXT",  /* なんか自由に使って下さい */
));

// これはページ内でプラグインを埋める場合に使う（ちょっとやってみないと分からない）
$dataAccess->createTable('page_pluginContents', array(
    'page_id' => "INTEGER",
    'pluginContent_id' => "INTEGER"
));

// プラグインの設定（管理画面で使う？）
$dataAccess->createTable('pluginSettings', array(
    'plugin_id' => "INTEGER",
    'content_type' => "TEXT DEFAULT 'text/plain'",
    'value_type' => "TEXT DEFAULT 'text'",
    'name' => "TEXT",
    'value' => "TEXT",
    'options' => "TEXT",
    'use_in_page' => "INTEGER DEFAULT 0"
));

// セッション管理用
$dataAccess->createTable('Session', array(
    'sessid' => "TEXT",
    'data' => "TEXT",
));

/**
 * フォームデータ管理
 * 1セッション、1アクションに対して1つformを予約できる。
 * フォームが予約されている場合、該当のtokenと共にPOSTされないデータは無効になる。
 * actionにはフォーム画面のactionが入る（POST先のactionではない）
 */
/**
 * フォームの設計については見直し
 *
 * バリデーションはリポジトリにメソッドを追加し、validationカラムにメソッド名を入れておく？のか？
 * 出来ればバリデーションルールは管理画面から変更したいよね。（TODO ）
 * セッションに予約のtokenを持てばいいよね。
 * actionは何だ、識別子か
 *
 * つまりformReservationsはいらない。
 * Formテーブルを使おう。
 * 内容は
 * 1.識別名
 * 2.バリデーションルール。（なお、ルールを追加したい場合はソースの修正が必要）
 * 3.くらい・・・？
 *
 * そもそもバリデーションはどうやるか考えよう。
 * タイプ1、旧来の方式（POSTのタイミングで画面を返す）
 * タイプ2、ajaxバリバリで項目が変更される毎にバリデーションを行う。更新もajaxで戻る（1画面完結）
 *
 * タイプ1の場合、アレ
 * タイプ2の場合、token取得のAPIをコールした後、tokenを付けて再度POSTする
 */
$dataAccess->createTable('Form', array(
    'name' => "TEXT",
    'validation' => "TEXT",
));

// ユーザー管理
$dataAccess->createTable('users', array(
    'userId' => "TEXT",  // ログイン時に利用するID
    'hashed' => "TEXT", // ハッシュ化されたパスワード
    'name' => "TEXT",
    'email' => "TEXT",
));

// グループ管理
$dataAccess->createTable('userGroups', array(
    'name' => 'TEXT',
    'userIds' => 'TEXT',  // users.idのパイプ区切り（|1|2|3|のようになる）
));

// 初期設定値登録
$dataAccess->insert('Setting', array('label' => 'アクティベート済', 'name' => 'Activated', 'value' => '0'));
