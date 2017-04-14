<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\Router;
use ellsif\WelCMS\WelUtil;


class WelUtilTest extends \PHPUnit\Framework\TestCase
{
    private $router;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    protected function setUp()
    {
    }

    /**
     *
     */
    public function testGetParamMapSuccess()
    {
        $param = ['var1', '10', 'var2', '100', 'var2', '20', 'var3[]', '1', 'var3[]', '2', 'var4[foo]', 'foooo', 'var4[bar]', 'barrr'];

        $result = WelUtil::getParamMap($param);
        $this->assertEquals('{"var1":"10","var2":"20","var3":["1","2"],"var4":{"foo":"foooo","bar":"barrr"}}', json_encode($result));
    }
}