<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelCoMeS;
use GuzzleHttp\Client;

/**
 * @runTestsInSeparateProcesses
 */
class WelCoMeSTest extends \PHPUnit\Framework\TestCase
{

    public static function setUpBeforeClass()
    {
        if (file_exists(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainInitDB.sqlite')) {
            unlink(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainInitDB.sqlite');
        }
        if (file_exists(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainActivationRedirect.sqlite')) {
            unlink(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainActivationRedirect.sqlite');
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

    public function testConstructFailure()
    {
        $this->expectException(\ArgumentCountError::class);
        new WelCoMeS();
    }

    public function testConstructFailureFileNotFound()
    {
        $this->expectException(\RuntimeException::class);
        new WelCoMeS('/bad/path.php');
    }

    public function testConstructSuccess()
    {
        $welCMS = new WelCoMeS(dirname(__FILE__, 2) . '/stub/conf/conf.php');
        $this->assertInstanceOf(WelCoMeS::class, $welCMS);
    }

    public function testMainInitDBSuccess()
    {
        // 初回起動時にDBが存在しない場合、作成されることを確認する。
        $welCMS = new WelCoMeS(dirname(__FILE__, 2) . '/stub/conf/conf.php');
        $pocket = Pocket::getInstance();
        $pocket->dbDatabase(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainInitDB.sqlite');

        $welCMS->main();

        $this->assertFileExists(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainInitDB.sqlite');
        unlink(dirname(__FILE__, 3) . '/data/WelCoMeSTestMainInitDB.sqlite');
    }

    // アクティベートされていない場合はアクティベーションページを表示
    public function testMainActivationRedirect()
    {
        $client = new Client();
        $res = $client->get('http://localhost:1349/testMainActivationRedirect.php');

        $this->assertEquals(200, $res->getStatusCode());

        // TODO assertは追加必要
        $this->assertNotRegExp("/<b>Notice<\/b>/", (string)$res->getBody());
        $this->assertRegExp('/WelCMS初期設定/', (string)$res->getBody());
    }
}