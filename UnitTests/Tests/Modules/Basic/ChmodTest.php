<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleChmodTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleChmod::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':chmod help');
        $this->assertSame(ModuleChmod::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':chmod');
        $this->assertSame(ModuleChmod::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':chmod 777');
        $this->assertSame(ModuleChmod::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':chmod 777 /tmp/test /tmp/test2');
        $this->assertSame(ModuleChmod::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sFile = "/tmp/" . md5(microtime(1));
        file_put_contents($sFile, '');
        chmod($sFile, 0000);

        $sOut = $this->oShell->getCommandOutput(':chmod 0777 ' . $sFile);
        $this->assertSame("Uprawnienia zostały zmienione\r\n", $sOut);
        $this->assertSame('777', decoct(fileperms($sFile) & 0777));

        clearstatcache();

        $sOut = $this->oShell->getCommandOutput(':chmod 0123 ' . $sFile);
        $this->assertSame("Uprawnienia zostały zmienione\r\n", $sOut);
        $this->assertSame('123', decoct(fileperms($sFile) & 0777));
    }

    public function testInvalidChmod()
    {
        $sOut = $this->oShell->getCommandOutput(':chmod 11311 /tmp/test');
        $this->assertSame("Błędny chmod \"11311\"\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':chmod abcd /tmp/test');
        $this->assertSame("Błędny chmod \"abcd\"\r\n", $sOut);
    }

    public function testFileDoesntExist()
    {
        $sOut = $this->oShell->getCommandOutput(':chmod 777 /tmp/b/c/d/f/t/y');
        $this->assertSame("Plik \"/tmp/b/c/d/f/t/y\" nie istnieje\r\n", $sOut);
    }

}
