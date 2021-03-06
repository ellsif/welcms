<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelUtil;
use GuzzleHttp\Client;

class AdminServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * メソッド名とsqliteファイルの対応
     */
    protected static $dbFiles = [];

    private $dbFile;

    public static function setUpBeforeClass()
    {
        self::$dbFiles = [
            'testLoginSuccess'     => dirname(__FILE__, 3) . '/data/AdminServiceTestLoginSuccess.sqlite',
            'testPostLoginSuccess' => dirname(__FILE__, 3) . '/data/AdminServiceTestPostLoginSuccess.sqlite',
        ];

        foreach(self::$dbFiles as $methodName => $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public static function tearDownAfterClass()
    {
        foreach(self::$dbFiles as $methodName => $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    protected function setUp()
    {
        $pocket = Pocket::getInstance();
        $pocket->reset();
    }

    /**
     * ログイン画面が表示されることを確認する。
     */
    public function testLoginSuccess()
    {
        $dbFile = self::$dbFiles[__FUNCTION__];
        Pocket::getInstance()->dbDatabase($dbFile);

        // アクティベーションを済ませておく。
        $settingRepo = WelUtil::getRepository('Setting');
        $settingRepo->activation('http://localhost:1349/', 'テストサイト', 'admin', 'password');

        $client = new Client();
        $res = $client->get('http://localhost:1349/AdminServiceTestLoginSuccess/admin/login/');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertNotRegExp("/<b>Notice<\/b>/", (string)$res->getBody());
        $this->assertRegExp('/テストサイト システム管理画面ログイン/', (string)$res->getBody());
    }

    /**
     * ログインが成功することを確認する。
     */
    public function testPostLoginSuccess()
    {
        $dbFile = self::$dbFiles[__FUNCTION__];
        Pocket::getInstance()->dbDatabase($dbFile);

        // ログイン情報の登録。
        $settingRepo = WelUtil::getRepository('Setting');
        $settingRepo->activation('http://localhost:1349/', 'テストサイト', 'admin', 'password');

        $client = new Client(['cookies' => true]);
        $res = $client->post('http://localhost:1349/AdminServiceTestPostLoginSuccess/admin/login/', [
            'form_params' => [
                'adminID' => 'admin',
                'adminPass' => 'password',
            ],
            'allow_redirects' => false,
        ]);

        // ログイン後にリダイレクトされる
        $this->assertEquals(301, $res->getStatusCode());
        $this->assertEquals('http://localhost:1349/AdminServiceTestPostLoginSuccess/admin', $res->getHeaderLine('Location'));

        // 管理者のダッシュボードの表示
        $res = $client->get('http://localhost:1349/AdminServiceTestPostLoginSuccess/admin/');
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertNotRegExp("/<b>Notice<\/b>/", (string)$res->getBody());
        $this->assertRegExp('/ダッシュボード/', (string)$res->getBody());

    }
}