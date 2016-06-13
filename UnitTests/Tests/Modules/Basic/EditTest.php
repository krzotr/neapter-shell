<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleEditTest extends PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        ModuleEdit::getVersion();
    }

    public function testHelp()
    {
        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput(':edit help');
        $this->assertSame(ModuleEdit::getHelp() . "\r\n", $sOut);

        $sOut = $oShell->getCommandOutput(':edit');
        $this->assertSame(ModuleEdit::getHelp() . "\r\n", $sOut);
    }

    public function testForm()
    {
        $sFile = '/tmp/' . md5(microtime(1));
        file_put_contents($sFile, str_repeat('y', 1024));

        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput(':edit ' . $sFile);

        $this->assertRegExp('~<form .+?>.+?</form>~s', $sOut, $sOut);
        $this->assertRegExp('~y{1024}~s', $sOut, $sOut);

        @ unlink($sFile);
    }

    public function testWithPath()
    {
        $sFile = '/tmp/' . md5(microtime(1));
        file_put_contents($sFile, str_repeat('y', 1024));

        $sCmd = ':edit ' . $sFile;

        $_POST = array(
            'filedata' => str_repeat('=', 1024),
            'cmd' => $sCmd
        );

        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput($sCmd);
        $this->assertSame("Plik został zapisany\r\n", $sOut);

        $this->assertSame(str_repeat('=', 1024), file_get_contents($sFile));

        @ unlink($sFile);
    }
}
