<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Auth;
use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelUtil;
use GuzzleHttp\Client;

/**
 * @runTestsInSeparateProcesses
 */
class ManagerServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * メソッド名とsqliteファイルの対応
     */
    protected static $dbFiles = [];

    private $dbFile;

    public static function setUpBeforeClass()
    {
        self::$dbFiles = [
            'testLoginSuccess'     => dirname(__FILE__, 3) . '/data/ManagerServiceTestLogin.sqlite',
            'testPostLoginSuccess' => dirname(__FILE__, 3) . '/data/ManagerServiceTestLogin.sqlite',
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
        // アクティベーションを済ませておく。
        $dbFile = self::$dbFiles[__FUNCTION__];
        Pocket::getInstance()->dbDatabase($dbFile);
        $settingRepo = WelUtil::getRepository('Setting');
        $settingRepo->activation('http://localhost:1349/', 'テストサイト', 'admin', 'password');

        $client = new Client();
        $res = $client->get('http://localhost:1349/ManagerServiceTestLogin/manager/login/');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertNotRegExp("/<b>Notice<\/b>/", (string)$res->getBody());
        $this->assertRegExp('/テストサイト 管理画面ログイン/', (string)$res->getBody());
    }

    /**
     * ログインが成功することを確認する。
     */
    public function testPostLoginSuccess()
    {
        // アクティベーションを済ませておく。
        $dbFile = self::$dbFiles[__FUNCTION__];
        Pocket::getInstance()->dbDatabase($dbFile);
        $settingRepo = WelUtil::getRepository('Setting');
        $settingRepo->activation('http://localhost:1349/', 'テストサイト', 'admin', 'password');

        // ユーザーを登録しておく
        $managerRepo = WelUtil::getRepository('Manager');
        $managerRepo->save([['managerId' => 'manager', 'password' => Auth::getHashed('password')]]);

        // ログイン処理開始
        $client = new Client(['cookies' => true]);
        $res = $client->post('http://localhost:1349/ManagerServiceTestLogin/manager/login/', [
            'form_params' => [
                'managerId' => 'manager',
                'password' => 'password',
            ],
            'allow_redirects' => false,
        ]);

        // ログイン後にリダイレクトされる
        $this->assertEquals(301, $res->getStatusCode());
        $this->assertEquals('http://localhost:1349/ManagerServiceTestLogin/manager', $res->getHeaderLine('Location'));

        // 管理者のダッシュボードの表示
        $res = $client->get('http://localhost:1349/ManagerServiceTestLogin/manager/');
        $this->assertEquals(200, $res->getStatusCode());
    }
}