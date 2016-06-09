<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleRemoveTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleRemove::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':rm help');
        $this->assertSame(ModuleRemove::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':rm');
        $this->assertSame(ModuleRemove::getHelp() . "\r\n", $sOut);
    }

    public function testRemoveFile()
    {
        $sFile = '/tmp/' . md5(microtime(1));
        file_put_contents($sFile, '');

        $sOut = $this->oShell->getCommandOutput(':rm ' . $sFile);

        $this->assertSame(
            sprintf("Plik \"%s\" został usunięty\r\n", $sFile),
            $sOut
        );
    }

    public function testRemoveFileError()
    {
        $sFile = '/tmp/a/c/d/e/f/g/h';

        $sOut = $this->oShell->getCommandOutput(':rm ' . $sFile);
        $this->assertSame(
            sprintf("Podana ścieżka \"%s\" nie istnieje\r\n", $sFile),
            $sOut
        );

        $sOut = $this->oShell->getCommandOutput(':rm /proc/cpuinfo');
        $this->assertSame("Plik \"/proc/cpuinfo\" nie został usunięty\r\n", $sOut);
    }

    public function testRemoveRecursive()
    {
        /* @todo */
    }

}
