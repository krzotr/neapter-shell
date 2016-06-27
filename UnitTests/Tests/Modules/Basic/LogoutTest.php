<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleLogoutTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleLogout::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':logout help');
        $this->assertSame(ModuleLogout::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sAuthKeyFile = $this->oShell->getUtils()->getAuthFileKey();
        $sAuth = $this->oShell->getUtils()->cacheSet($sAuthKeyFile, 'test');

        @ ob_start();

        $this->oShell->getCommandOutput(':logout');
        $sOut = ob_get_contents();

        @ ob_end_clean();

        $this->assertSame("See you (:\n", $sOut);
    }
}
