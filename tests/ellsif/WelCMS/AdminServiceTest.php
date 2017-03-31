<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelUtil;
use GuzzleHttp\Client;

/**
 * @runTestsInSeparateProcesses
 */
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
            'testLoginSuccess'             => dirname(__FILE__, 3) . '/data/AdminServiceTestLoginSuccess.sqlite',
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
     * ログイン画面を表示
     */
    public function testLoginSuccess()
    {
        $dbFile = self::$dbFiles[__FUNCTION__];
        Pocket::getInstance()->dbDatabase($dbFile);

        // アクティベーションを済ませておく。
        $settingRepo = WelUtil::getRepository('Setting');
        $settingRepo->save([['name' => 'activate', 'value' => 1], ['name' => 'siteName', 'value' => 'テストサイト']]);

        $client = new Client();
        $res = $client->get('http://localhost:1349/AdminServiceTestLoginSuccess/admin/login/');

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('hoge', (string)$res->getBody());
    }
}