<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\Router;
use ellsif\WelCMS\WelUtil;


class UserRepositoryTest extends \PHPUnit\Framework\TestCase
{
    private $router;

    public static function setUpBeforeClass()
    {
        $config = Pocket::getInstance();
        $config->dbDriver('sqlite');
        $config->dbDatabase(dirname(__FILE__, 3) . '/data/UserRepositoryTest.sqlite');
        $config->dirSystem(dirname(__FILE__, 4) .  '/src');
        $config->dirApp(dirname(__FILE__, 2) . '/stub');
        $config->dirLog(dirname(__FILE__, 3) . '/logs/');
    }

    public static function tearDownAfterClass()
    {
        if (file_exists(dirname(__FILE__, 3) . '/data/UserRepositoryTest.sqlite')) {
            unlink(dirname(__FILE__, 3) . '/data/UserRepositoryTest.sqlite');
        }
    }

    protected function setUp()
    {
    }

    /**
     * Save時にinfoがjson_encodeで登録される事を確認する。Load時にinfoがjson_decodeで取得される事を確認する。
     */
    public function testOnSaveOnLoadSuccess()
    {
        $userRepo = WelUtil::getRepository('User');
        $userRepo->save([
            [
                'userId' => 'test',
                'password' => 'dummy',
                'name' => 'test user',
                'email' => 'test@example.com',
                'info' => ['test' => 'json']
            ]
        ]);

        // 生データ
        $users = $userRepo->list([], '', 0, -1, false);
        $this->assertCount(1, $users);
        $this->assertEquals('{"test":"json"}', $users[0]['info']);

        // 編集あり
        $users = $userRepo->list();
        $this->assertCount(1, $users);
        $this->assertArrayHasKey('test', $users[0]['info']);
        $this->assertEquals('json', $users[0]['info']['test']);
    }

}