<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleEvalTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleEval::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':eval help');
        $this->assertSame(ModuleEval::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':eval');
        $this->assertSame(ModuleEval::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(":eval echo md5('test');");
        $this->assertSame("098f6bcd4621d373cade4e832627b4f6\r\n", $sOut);
    }

}
