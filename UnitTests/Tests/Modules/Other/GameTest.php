<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleGameTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleGame::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':game help');
        $this->assertSame(ModuleGame::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':game');
        $this->assertSame(ModuleGame::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':game 5');
        $this->assertNotNull($sOut);

        $sOut = $this->oShell->getCommandOutput(':game 5 10');
        $this->assertNotNull($sOut);

        $sOut = $this->oShell->getCommandOutput(':game x');
        $this->assertNotNull($sOut);
    }

    public function testModuleFail()
    {
        $sOut = $this->oShell->getCommandOutput(':game abc');
        $this->assertSame(ModuleGame::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':game 5 9999');
        $this->assertSame(ModuleGame::getHelp() . "\r\n", $sOut);
    }
}
