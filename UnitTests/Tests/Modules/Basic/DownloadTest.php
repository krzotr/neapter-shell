<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleDownloadTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleDownload::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':download help');
        $this->assertSame(ModuleDownload::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':download');
        $this->assertSame(ModuleDownload::getHelp() . "\r\n", $sOut);
    }

    public function testLocalDownload()
    {
        @ ob_start();

        $this->oShell->getCommandOutput(':download /etc/passwd');
        $sOut = ob_get_contents();

        @ ob_end_clean();

        $this->assertSame(file_get_contents('/etc/passwd'), $sOut);
    }

    public function testLocalDownloadGz()
    {
        @ ob_start();

        $this->oShell->getCommandOutput(':download -g /etc/passwd');
        $sOut = ob_get_contents();

        @ ob_end_clean();

        $this->assertSame(
            file_get_contents('/etc/passwd'),
            $sOut
        );
    }


    public function testLocalDownloadError()
    {
        $sOut = $this->oShell->getCommandOutput(':download /a/b/c/d/e/f/x/aa');

        $this->assertSame(
            "Błąd odczytu pliku \"/a/b/c/d/e/f/x/aa\"\r\n",
            $sOut
        );
    }

    public function testRemoteDownloadError()
    {
        $sOut = $this->oShell->getCommandOutput(
            ':download http://www.thisisouryear.com/a
        ');

        $this->assertSame(
            "Nie można pobrać pliku z \"http://www.thisisouryear.com/a\"\r\n",
            $sOut
        );
    }

    public function testRemoteDownload()
    {
        @ ob_start();

        $this->oShell->getCommandOutput(':download http://wklej.to/PVd35/text');
        $sOut = ob_get_contents();

        @ ob_end_clean();

        $this->assertSame(str_repeat('x', 50), $sOut);
    }
}
