<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModulePingTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModulePing::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':ping help');
        $this->assertSame(ModulePing::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':ping');

        $this->assertSame("pong\r\n",$sOut);
    }

}
