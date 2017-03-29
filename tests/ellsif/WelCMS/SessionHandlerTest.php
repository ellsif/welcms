<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\SessionHandler;
use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelUtil;

/**
 * @runTestsInSeparateProcesses
 */
class SessionHandlerTest extends \PHPUnit\Framework\TestCase
{

    public static function setUpBeforeClass()
    {
        if (file_exists(dirname(__FILE__, 3) . '/data/SessionHandlerTest.sqlite')) {
            unlink(dirname(__FILE__, 3) . '/data/SessionHandlerTest.sqlite');
        }

        $pocket = Pocket::getInstance();
        $pocket->dbDriver('sqlite');
        $pocket->dirWelCMS(dirname(__FILE__, 4) .  '/src');
        $pocket->dirSystem(dirname(__FILE__, 4) .  '/src');
        $pocket->dirApp(dirname(__FILE__, 2) . '/stub');

        $sessionHandler = new SessionHandler();
        session_set_save_handler($sessionHandler, true);
        register_shutdown_function('session_write_close');

        $pocket = Pocket::getInstance();
        $pocket->dbDriver('sqlite');
        $pocket->dbDatabase(dirname(__FILE__, 3) . '/data/SessionHandlerTest.sqlite');
        $pocket->dirWelCMS(dirname(__FILE__, 4) .  '/src');
        $pocket->dirSystem(dirname(__FILE__, 4) .  '/src');
        $pocket->dirApp(dirname(__FILE__, 2) . '/stub/');
        $pocket->dirLog(dirname(__FILE__, 3) . '/logs/');

        // テストデータ作成
        $dataAccess = WelUtil::getDataAccess('sqlite');
        $dataAccess->createTable('Session', [
            'sessid' => "TEXT",
            'data' => "TEXT",
        ]);
        $dataAccess->createTable('User', [
            'userId' => "TEXT",
            'hashed' => "TEXT",
            'name' => "TEXT",
            'email' => "TEXT",
        ]);
        $userRepository = WelUtil::getRepository('User');
        $userRepository->save([
            [
                'userId' => 'ellsif',
                'name' => 'ellsif test',
                'email' => 'info@ellsif.com',
            ]
        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        // TODO Sessionを全消し
    }

    public function testSessionStartSuccess()
    {
        $result = session_start();
        session_write_close();

        $this->assertTrue($result);
    }

    /*
    // TODO 今のところ失敗しない
    public function testSessionStartFailure()
    {
        $this->assertFalse(session_start());
    }
    */

    public function testSessionReadWriteSuccess()
    {
        session_start();
        $sessionId = session_id();

        $_SESSION['test1'] = 100;
        $_SESSION['test2'] = 'test';

        session_write_close();

        $sessionRepository = WelUtil::getRepository('Session');
        $sessions = $sessionRepository->list(['sessid' => $sessionId]);

        $this->assertCount(1, $sessions);
        $session = $sessions[0];
        $this->assertEquals('test1|i:100;test2|s:4:"test";', $session['data']);
    }

    // TODO GCのテストをしたい
}