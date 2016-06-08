<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleEchoTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleEcho::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':echo help');
        $this->assertSame(ModuleEcho::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':echo TeST');
        $this->assertSame("TeST\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':echo TeST test2 TeST');
        $this->assertSame("TeST test2 TeST\r\n", $sOut);
    }

}
