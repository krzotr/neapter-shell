<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleHelpTest extends PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        ModuleHelp::getVersion();
    }

    public function testHelp()
    {
        $oShell = new Shell();

        $sOut = $oShell->getCommandOutput(':help');
        $this->assertRegExp('~(bcat|cd|chmod|eval|php|system|exec)~', $sOut);

        $sOut = $oShell->getCommandOutput(':help all');
        $this->assertRegExp('~(bcat|cd|chmod|eval|php|system|exec)~', $sOut);
        $this->assertRegExp('~>>>>> Module: Module.+? <<<<<~', $sOut);
    }
}
