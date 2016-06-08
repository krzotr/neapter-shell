<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleVersionTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleVersion::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':version help');
        $this->assertSame(ModuleVersion::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':version');

        $this->assertSame(
            'Neapter shell version: ' . Shell::VERSION . "\r\n",
            $sOut
        );
    }

}
