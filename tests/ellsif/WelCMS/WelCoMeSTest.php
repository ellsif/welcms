<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Exception;
use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelCoMeS;
use ellsif\WelCMS\WelUtil;
use GuzzleHttp\Client;

class WelCoMeSTest extends \PHPUnit\Framework\TestCase
{

    /**
     * メソッド名とsqliteファイルの対応
     */
    private static $dbFiles = [];

    public static function setUpBeforeClass()
    {
        WelCoMeSTest::$dbFiles = [
            'testMainInitDB'             => dirname(__FILE__, 3) . '/data/WelCoMeSTestMainInitDB.sqlite',
            'testMainActivationRedirect' => dirname(__FILE__, 3) . '/data/WelCoMeSTestMainActivationRedirect.sqlite',
            'testMainPostActivationSuccess' => dirname(__FILE__, 3) . '/data/WelCoMeSTestMainPostActivationSuccess.sqlite',
        ];

        foreach(WelCoMeSTest::$dbFiles as $methodName => $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public static function tearDownAfterClass()
    {
    }

    protected function setUp()
    {
        $pocket = Pocket::getInstance();
        $pocket->reset();
    }

    /**
     * コンストラクタのテスト
     */
    public function testConstructSuccess()
    {
        $welCMS = new WelCoMeS(dirname(__FILE__, 2) . '/stub/conf/conf.php');
        $this->assertInstanceOf(WelCoMeS::class, $welCMS);
    }

    /**
     * コンストラクタのテスト（引数なし）
     */
    public function testConstructSuccessNoArg()
    {
        $welCMS = new WelCoMeS();
        $this->assertInstanceOf(WelCoMeS::class, $welCMS);

    }

    /**
     * コンストラクタのテスト（不正なパス）
     */
    public function testConstructFailureFileNotFound()
    {
        $this->expectException(Exception::class);
        new WelCoMeS('/bad/path.php');
    }

    /**
     * 初回起動時にDBが存在しない場合、作成されることを確認する。
     */
    public function testMainInitDBSuccess()
    {
        $dbFile = dirname(__FILE__, 3) . '/data/WelCoMeSTestMainInitDB.sqlite';
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }

        $client = new Client();
        $res = $client->get('http://localhost:1349/testMainInitDBSuccess.php');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertFileExists($dbFile);
        unlink($dbFile);
    }

    /**
     * アクティベートされていない場合はアクティベーションページを表示
     * TODO リダイレクトされるべきか・・・？
     */
    public function testMainActivationRedirect()
    {
        $client = new Client();
        $res = $client->get('http://localhost:1349/testMainActivationRedirect.php');

        $this->assertEquals(200, $res->getStatusCode());

        // TODO assertは追加必要
        $this->assertNotRegExp("/<b>Notice<\/b>/", (string)$res->getBody());
        $this->assertRegExp('/WelCMS初期設定/', (string)$res->getBody());
    }

    /**
     * アクティベーションのPOSTが通る事を確認する。
     */
    public function testMainPostActivationSuccess()
    {
        $client = new Client();
        $res = $client->post('http://localhost:1349/testMainPostActivationSuccess.php', [
            'form_params' => [
                'siteName' => 'Test Site',
                'urlHome' => 'http://example.com',
                'adminID' => 'admin',
                'adminPass' => 'password',
            ]
        ]);

        $this->assertEquals(200, $res->getStatusCode());

        // DBの更新結果をチェック
        $pocket = Pocket::getInstance();
        $pocket->dbDatabase(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainPostActivationSuccess.sqlite');
        $settingRepo = WelUtil::getRepository('Setting');
        $list = $settingRepo->list(['name'=>'activate']);
        $this->assertNotEmpty($list);
        $this->assertEquals(1, $list[0]['value']);
    }
}