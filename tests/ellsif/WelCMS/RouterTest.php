<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Config;
use ellsif\WelCMS\Router;
use ellsif\WelCMS\Util;


class RouterTest extends \PHPUnit\Framework\TestCase
{
    private $router;

    public static function setUpBeforeClass()
    {
        $config = Config::getInstance();
        $config->dbDriver('sqlite');
        $config->dbDatabase(dirname(__FILE__, 3) . '/data/RouterTest.sqlite');
        $config->dirApp(dirname(__FILE__, 2) . '/stub');
        require_once $config->dirApp() . 'service/TestService.php';

        $dataAccess = Util::getDataAccess();
        $dataAccess->createTable('Page', [
            'template_id' => 'INTEGER',
            'name' => 'TEXT',
            'path' => 'TEXT',
            'options' => 'TEXT',
            'bodyCache' => 'TEXT',
            'published' => 'INTEGER DEFAULT 0',
            'allowedUserGroupIds' => 'TEXT',
        ]);

        $pageEntity = Util::getRepository('Page');
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
                'allowedUserGroupIds' => '1',
            ],
        ]);
    }

    public static function tearDownAfterClass()
    {
        unlink(dirname(__FILE__, 3) . '/data/RouterTest.sqlite');
    }

    protected function setUp()
    {
        $this->router = Router::getInstance();
        $_SERVER = [
            'REQUEST_URI' => 'http://localhost.localdomain:8080/test/action/',
            'REQUEST_METHOD' => 'GET',
        ];

        $config = Config::getInstance();
        $config->settingActivated(1);
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
        $this->router->initialize();

        $config = Config::getInstance();
        $this->assertEquals('GET', $config->varRequestMethod());
        $this->assertEquals('http://localhost.localdomain:8080/test/action/', $config->varCurrentUrl());
        $this->assertEquals('test/action', $config->varCurrentPath());
    }

    public function testRoutingActivate()
    {
        $config = Config::getInstance();
        $config->settingActivated(0);   // not activate

        $this->router->initialize();
        $this->router->routing();

        $this->assertEquals('AdminService', $config->varService());
        $this->assertEquals('activate', $config->varAction());
    }

    public function testRoutingActivate404()
    {
        $config = Config::getInstance();
        $config->settingActivated(0);   // not activate
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/fabicon.ico';

        $this->router->initialize();

        $this->expectException(\InvalidArgumentException::class);
        $this->router->routing();
    }

    public function testRouting404BadExtention()
    {
        $_SERVER['REQUEST_URI'] = 'http://localhost.localdomain:8080/fabicon.badext';

        $this->router->initialize();

        $this->expectException(\InvalidArgumentException::class);
        $this->router->routing();
    }

    public function testRoutingDefault()
    {
        $this->router->initialize();
        $this->router->routing();

        $config = Config::getInstance();
        $this->assertEquals('ellsif\WelCMS\Printer', $config->varPrinter());
        $this->assertEquals('test', $config->varService());
        $this->assertEquals('action', $config->varAction());
    }
}