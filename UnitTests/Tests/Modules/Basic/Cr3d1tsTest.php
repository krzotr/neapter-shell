<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleCr3d1tsTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleCr3d1ts::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':credits help');
        $this->assertSame(ModuleCr3d1ts::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $this->oShell->getCommandOutput(':credits');
    }

}
