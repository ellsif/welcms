<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Pocket;
use ellsif\WelCMS\WelUtil;


class SettingRepositoryTest extends \PHPUnit\Framework\TestCase
{
    private $router;

    public static function setUpBeforeClass()
    {
        $pocket = Pocket::getInstance();
        $pocket->dbDriver('sqlite');
        $pocket->dirSystem(dirname(__FILE__, 4) .  '/src');
        $pocket->dirApp(dirname(__FILE__, 2) . '/stub');
        $pocket->dbDatabase(dirname(__FILE__, 3) . '/data/SettingRepositoryTest.sqlite');
    }

    public static function tearDownAfterClass()
    {
        if (file_exists(dirname(__FILE__, 3) . '/data/SettingRepositoryTest.sqlite')) {
            unlink(dirname(__FILE__, 3) . '/data/SettingRepositoryTest.sqlite');
        }
    }

    /**
     * コンストラクタのテスト(正常系)
     *
     * ## 説明
     * - テーブルが存在しない場合、自動的にテーブルが作られることを確認する。
     */
    public function testConstructSuccess()
    {
        $pocket = Pocket::getInstance();
        $settingRepo = WelUtil::getRepository('Setting');
        $dataAccess = WelUtil::getDataAccess($pocket->dbDriver());
        $this->assertNotNull($settingRepo);
        $this->assertTrue($dataAccess->isTableExists('Setting'));
    }
}