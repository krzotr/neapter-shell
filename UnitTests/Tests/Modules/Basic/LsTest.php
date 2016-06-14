<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleLsTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleLs::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':ls help');
        $this->assertSame(ModuleLs::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':ls -R /');
        $this->assertNotNull($sOut);

        $sOut = $this->oShell->getCommandOutput(':ls -R /tmp');
        $this->assertNotNull($sOut);

        $sOut = $this->oShell->getCommandOutput(':ls -R /a/b/c/x/f');
        $this->assertNotNull($sOut);
    }

    public function testModulePosix()
    {
        if (!extension_loaded('runkit')) {
            $this->markTestIncomplete('Please download runkit PHP extension!');
            return;
        }

        if (1 !== (int) ini_get('runkit.internal_override')) {
            $this->markTestIncomplete(
                'Please set runkit.internal_override to 1 in php.ini!'
            );
            return;
        }

        runkit_function_rename('posix_getpwuid', '_posix_getpwuid');
        runkit_function_rename('posix_getgrgid', '_posix_getgrgid');

        $oShell = new Shell();

        $sOut = $oShell->getCommandOutput(':ls -R /tmp');
        $this->assertNotNull($sOut);

        function posix_getpwuid($iId) {
            return _posix_getpwuid($iId);
        }

        function posix_getgrgid($iId) {
            return _posix_getgrgid($iId);
        }
    }
}
