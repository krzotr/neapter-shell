<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModulePhpinfoTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModulePhpinfo::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':phpinfo help');
        $this->assertSame(ModulePhpinfo::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':phpinfo');

        $this->assertRegExp('~_SERVER~', $sOut);
        $this->assertRegExp('~session\.name~', $sOut);
        $this->assertRegExp('~memory_limit~', $sOut);
    }

}
