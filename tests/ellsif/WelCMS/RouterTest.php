<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\Router;
use ellsif\WelCMS\WelUtil;


class RouterTest extends \PHPUnit\Framework\TestCase
{
    private $router;

    public static function setUpBeforeClass()
    {
        $config = Pocket::getInstance();
        $config->dbDriver('sqlite');
        $config->dbDatabase(dirname(__FILE__, 3) . '/data/RouterTest.sqlite');
        $config->dirWelCMS(dirname(__FILE__, 4) .  '/src');
        $config->dirSystem(dirname(__FILE__, 4) .  '/src');
        $config->dirApp(dirname(__FILE__, 2) . '/stub');
        require_once $config->dirApp() . '/classes/service/TestService.php';

        $dataAccess = WelUtil::getDataAccess('sqlite');

        // ユーザーグループのテストデータ
        $dataAccess->createTable('UserGroup', [
            'name' => 'TEXT',
            'userIds' => 'TEXT',
        ]);
        $userGroupEntity = WelUtil::getRepository('UserGroup');
        $userGroupEntity->save([
            [
                'name' => '所属するグループ',
                'userIds' => '|1|',
            ]
        ]);

        // 個別ページのテストデータ
        $dataAccess->createTable('Page', [
            'template_id' => 'INTEGER',
            'name' => 'TEXT',
            'path' => 'TEXT',
            'options' => 'TEXT',
            'bodyCache' => 'TEXT',
            'published' => 'INTEGER DEFAULT 0',
            'allowedUserGroupIds' => 'TEXT',
        ]);
        $pageEntity = WelUtil::getRepository('Page');
        $pageEntity->save([
            [
                'template_id' => 1,
                'name' => 'Topページ',
                'path' => '',
                'published' => 1,
                'allowedUserGroupIds' => '',
            ],
            [
                'template_id' => 1,
                'name' => '下書きページ',
                'path' => 'hidden',
                'published' => 0,
                'allowedUserGroupIds' => '',
            ],
            [
                'template_id' => 1,
                'name' => '会員専用ページ',
                'path' => 'member',
                'published' => 1,
                'allowedUserGroupIds' => '|1|',
            ],
        ]);
    }

    public static function tearDownAfterClass()
    {
        unlink(dirname(__FILE__, 3) . '/data/RouterTest.sqlite');
    }

    protected function setUp()
    {
        $this->router = new Router();
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/test/action/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = Pocket::getInstance();
        $config->reset();
        $config->settingActivated(1);
        $config->dbDriver('sqlite');
        $config->dbDatabase(dirname(__FILE__, 3) . '/data/RouterTest.sqlite');
        $config->dirWelCMS(dirname(__FILE__, 4) .  '/src');
        $config->dirSystem(dirname(__FILE__, 4) .  '/src');
        $config->dirApp(dirname(__FILE__, 2) . '/stub/');
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(Router::class, $this->router);
    }

    /**
     * @depends testGetInstance
     */
    public function testInitialize()
    {
        $this->router->routing();

        $config = Pocket::getInstance();
        $this->assertEquals('GET', $config->varRequestMethod());
        $this->assertEquals('http://localhost.localdomain:8080/test/action/', $config->varCurrentUrl());
        $this->assertEquals('test/action', $config->varCurrentPath());
    }

    public function testRoutingActivate()
    {
        $config = Pocket::getInstance();
        $config->settingActivated(0);   // not activate

        $this->router->routing();

        $this->assertEquals('AdminService', $config->varService());
        $this->assertEquals('activate', $config->varAction());
    }

    public function testRoutingActivate404()
    {
        $config = Pocket::getInstance();
        $config->settingActivated(0);   // not activate
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/fabicon.ico';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(404);
        $this->router->routing();
    }

    public function testRouting404BadExtention()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/fabicon.badext';

        $this->expectException(\InvalidArgumentException::class);
        $this->router->routing();
    }

    public function testRoutingDefault()
    {
        $this->router->routing();

        $config = Pocket::getInstance();
        $this->assertEquals('\ellsif\WelCMS\Printer', $config->varPrinter());
        $this->assertEquals('test', $config->varService());
        $this->assertEquals('action', $config->varAction());
    }

    public function testRoutingHidden()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/hidden';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(404);
        $this->router->routing();
    }

    public function testRoutingPageAllowed()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/member';
        $config = Pocket::getInstance();
        $config->loginUser(['id' => 1]);

        $this->router->routing();

        $this->assertEquals('\ellsif\WelCMS\PagePrinter', $config->varPrinter());
        $this->assertTrue(true);
    }

    public function testRoutingPageNotAllowed()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/member';
        $config = Pocket::getInstance();
        $config->loginUser(['id' => 2]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(401);
        $this->router->routing();
    }

    /***********************************************************************
     * Auth関係のテスト
     ***********************************************************************/
    public function testRoutingUserSuccess()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/test/auth1';
        $config = Pocket::getInstance();
        $config->loginUser(['id' => 1]);

        $this->router->routing();

        $this->assertEquals('auth1', $config->varAction());
        $this->assertEquals('auth1User', $config->varActionMethod());
    }

    public function testRoutingUserFailure()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/test/auth1';

        $this->expectExceptionCode(401);
        $this->router->routing();
    }

    public function testRoutingAdminSuccess()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/test/auth2';
        $config = Pocket::getInstance();
        $config->isAdmin(true);

        $this->router->routing();
        $this->assertEquals('auth2', $config->varAction());
        $this->assertEquals('auth2Admin', $config->varActionMethod());
    }

    public function testRoutingAdminFailure()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/test/auth2';
        $config = Pocket::getInstance();
        $config->loginUser(['id' => 1]);
        $config->loginManager(['id' => 1]);

        $this->expectExceptionCode(401);
        $this->router->routing();
    }

    public function testRoutingManagerSuccess()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/test/auth3';
        $config = Pocket::getInstance();
        $config->loginManager(['id' => 1]);

        $this->router->routing();
        $this->assertEquals('auth3', $config->varAction());
        $this->assertEquals('auth3Manager', $config->varActionMethod());
    }

    public function testRoutingManagerFailure()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/test/auth3';
        $config = Pocket::getInstance();
        $config->loginUser(['id' => 1]);

        $this->expectExceptionCode(401);
        $this->router->routing();
    }
}