<?php
namespace ellsif\WelCms\Test;

use ellsif\Logger;
use ellsif\WelCMS\SessionHandler;
use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelUtil;

class SessionHandlerTest extends \PHPUnit\Framework\TestCase
{

    public static function setUpBeforeClass()
    {
        if (file_exists(dirname(__FILE__, 3) . '/data/SessionHandlerTest.sqlite')) {
            unlink(dirname(__FILE__, 3) . '/data/SessionHandlerTest.sqlite');
        }

        $pocket = Pocket::getInstance();
        $pocket->dbDriver('sqlite');
        $pocket->dirSystem(dirname(__FILE__, 4) .  '/src');
        $pocket->dirApp(dirname(__FILE__, 2) . '/stub');

        $sessionHandler = new SessionHandler();
        session_set_save_handler($sessionHandler, true);
        register_shutdown_function('session_write_close');

        $pocket = Pocket::getInstance();
        $pocket->dbDriver('sqlite');
        $pocket->dbDatabase(dirname(__FILE__, 3) . '/data/SessionHandlerTest.sqlite');
        $pocket->dirLog(dirname(__FILE__, 3) . '/logs/');
        Logger::getInstance()->setLogDir($pocket->dirLog());

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

    /**
     * セッションの読み書きに成功することを確認する。
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

        // 同セッションに対しては上書きとなること
        session_start();
        $_SESSION['test1'] = 200;
        $_SESSION['test2'] = 'update';
        session_write_close();
        $sessions = $sessionRepository->list(['sessid' => $sessionId]);
        $this->assertCount(1, $sessions);
        $session = $sessions[0];
        $this->assertEquals('test1|i:200;test2|s:6:"update";', $session['data']);

    }

    // TODO GCのテストをしたい
}