<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleSystemTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleSystem::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':system help');
        $this->assertSame(ModuleSystem::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':system');
        $this->assertSame(ModuleSystem::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput('cat /etc/passwd');
        $this->assertSame(
            sprintf(
                "Cmd: 'cat /etc/passwd'\r\nPHPfunc: system():\r\n\r\n%s\r\n",
                file_get_contents('/etc/passwd')
            ),
            $sOut
        );

        $sOut = $this->oShell->getCommandOutput('cd /etc');
        $this->assertSame(
            "Cmd: 'cd /etc'\r\nPHPfunc: system():\r\n\r\n\r\n",
            $sOut
        );
    }
}
