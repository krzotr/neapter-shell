<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

class ModuleCatTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleCat::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':cat');
        $this->assertSame(ModuleCat::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':cat ' . __FILE__);
        $this->assertSame(file_get_contents(__FILE__) . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':cat x' . __FILE__);
        $this->assertSame(sprintf("Plik \"x%s\" nie istnieje\r\n", __FILE__), $sOut);
    }

}
