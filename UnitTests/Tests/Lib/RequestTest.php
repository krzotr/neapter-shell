<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_GET['test_get'] = 'test_get';
        $_POST['test_post'] = 'test_post';
        $_SERVER['test_server'] = 'test_server';
        Request::init();
    }

    public function testGetCurrentUrl()
    {
        $this->assertNull(Request::getCurrentUrl());
    }

    public function testGetGet()
    {
        $this->assertFalse(Request::getGet('get_variable_doesnt_exist'));
        $this->assertSame('test_get', Request::getGet('test_get'));
    }

    public function testGetPost()
    {
        $this->assertFalse(Request::getPost('post_variable_doesnt_exist'));
        $this->assertSame('test_post', Request::getPost('test_post'));
    }

    public function testGetFiles()
    {
        $this->assertFalse(Request::getFiles('test_files'));
    }

    public function testGetServer()
    {
        $this->assertFalse(Request::getServer('server_variable_doesnt_exist'));
        $this->assertSame('test_server', Request::getServer('test_server'));
    }

    public function testGetServerAll()
    {
        $this->assertSame($_SERVER, Request::getServerAll());
    }

    public function testIsAjax()
    {
        $this->assertFalse(Request::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        Request::init();

        $this->assertTrue(Request::isAjax());
    }

}
