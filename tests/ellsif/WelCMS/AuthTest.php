<?php
namespace ellsif\WelCms\Test;

use ellsif\WelCMS\Auth;


class AuthTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    /**
     * saltの取得を確認する。
     */
    public function testGetSaltSuccess()
    {
        $salt = Auth::getSalt();
        $this->assertTrue(is_string($salt));
        $this->assertEquals(48, strlen($salt));
    }

    /**
     * saltの取得を確認する。(文字数指定)
     */
    public function testGetSalt32Success()
    {
        $salt = Auth::getSalt(64);
        $this->assertTrue(is_string($salt));
        $this->assertEquals(64, strlen($salt));
    }

    /**
     * ハッシュ化されたパスワードの取得を確認する。
     */
    public function testGetHashed()
    {
        $hashed = Auth::getHashed('password');
        $this->assertTrue(is_string($hashed));
        $this->assertNotEquals('password', strlen($hashed));
    }

    /**
     * ハッシュ化パスワードチェックが成功することを確認する。
     */
    public function testCheckHashedSuccess()
    {
        $hashed = Auth::getHashed('password');
        $result = Auth::checkHash('password', $hashed);
        $this->assertTrue($result);
    }

    /**
     * ハッシュ化パスワードチェックが失敗することを確認する。
     */
    public function testCheckHashedFailure()
    {
        $hashed = Auth::getHashed('password');
        $result = Auth::checkHash('badpassword', $hashed);
        $this->assertFalse($result);
    }
}