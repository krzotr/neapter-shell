<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleUploadTest extends PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        ModuleUpload::getVersion();
    }

    public function testHelp()
    {
        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput(':upload help');
        $this->assertSame(ModuleUpload::getHelp() . "\r\n", $sOut);
    }

    public function testForm()
    {
        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput(':upload');
        $this->assertRegExp('~<form .+?>.+?</form>~s', $sOut, $sOut);
    }

    public function testWithoutPath()
    {
        $sFile = '/tmp/' . md5(microtime(1));
        file_put_contents($sFile, str_repeat('x', 1024));

        $_FILES = array(
            'file' => array(
                'name' => 'test_file_php.txt',
                'type' => 'text/plain',
                // 'tmp_name' => '/tmp/phpLlccj5',
                'tmp_name' => $sFile,
                'error' => 0,
                'size' => 1024,
            )
        );

        chdir('/tmp');

        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput(':upload');
        $this->assertSame("Plik wgrany\r\n", $sOut);

        $this->assertTrue(is_file('/tmp/test_file_php.txt'));
        $this->assertSame(
            str_repeat('x', 1024),
            file_get_contents('/tmp/test_file_php.txt')
        );

        @ unlink('/tmp/test_file_php.txt');
    }

    public function testWithPath()
    {
        $sFile = '/tmp/' . md5(microtime(1));
        file_put_contents($sFile, str_repeat('y', 1024));

        $_FILES = array(
            'file' => array(
                'name' => 'test_file_php.txt',
                'type' => 'text/plain',
                // 'tmp_name' => '/tmp/phpLlccj5',
                'tmp_name' => $sFile,
                'error' => 0,
                'size' => 1024,
            )
        );

        chdir("/proc/");

        @ mkdir("/tmp/_test_php/");

        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput(':upload /tmp/_test_php/myfile.txt');
        $this->assertSame("Plik wgrany\r\n", $sOut);

        $this->assertTrue(is_file('/tmp/_test_php/myfile.txt'));
        $this->assertSame(
            str_repeat('y', 1024),
            file_get_contents('/tmp/_test_php/myfile.txt')
        );

        @ unlink('/tmp/_test_php/myfile.txt');
        @ rmdir('/tmp/_test_php');
    }

}
