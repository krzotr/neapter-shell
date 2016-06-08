<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleIdTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleId::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':id help');
        $this->assertSame(ModuleId::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sExpected = sprintf(
            "user=%s uid=%d gid=%d\r\n",
            get_current_user(),
            getmyuid(),
            getmygid()
        );

        $sOut = $this->oShell->getCommandOutput(':id');
        $this->assertSame($sExpected, $sOut);

        $sOut = $this->oShell->getCommandOutput(':whoami');
        $this->assertSame($sExpected, $sOut);
    }

}
