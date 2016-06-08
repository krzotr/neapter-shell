<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModulePwdTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModulePwd::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':pwd help');
        $this->assertSame(ModulePwd::getHelp() . "\r\n", $sOut);
    }

    public function testCd()
    {
        $sOut = $this->oShell->getCommandOutput(':cd /tmp');
        $this->assertSame("Katalog zmieniono na:\r\n    /tmp\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':pwd');
        $this->assertSame("/tmp\r\n", $sOut);
    }

}
