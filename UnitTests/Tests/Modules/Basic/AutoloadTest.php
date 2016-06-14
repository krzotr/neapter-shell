<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleAutoloadTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleAutoload::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':autoload help');
        $this->assertSame(ModuleAutoload::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':autoload');
        $this->assertSame(ModuleAutoload::getHelp() . "\r\n", $sOut);
    }

    public function testList()
    {
        $sOut = $this->oShell->getCommandOutput(':autoload -l');
        $this->assertSame("Nie wczytano żadnych rozszerzeń\r\n", $sOut);
    }

    public function testFlush()
    {
        $sOut = $this->oShell->getCommandOutput(':autoload -f');
        $this->assertSame("Plik z rozszerzeniami został usunięty\r\n", $sOut);
    }

    /* @todo */
    public function testAutoloadModule()
    {

    }

    /* @todo */
    public function testAutoloadModules()
    {

    }
}
