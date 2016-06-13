<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleModulesTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleModules::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':modules');
        $this->assertSame(ModuleModules::getHelp() . "\r\n", $sOut);
    }

    public function testModulesLoaded()
    {
        $sOut = $this->oShell->getCommandOutput(':modules loaded');
        $this->assertRegExp("~Załadowano \d+ modułów:\r\n~", $sOut);
        $this->assertRegExp("~\s+Module[^\r\n]~", $sOut);
    }

    public function testModulesVersion()
    {
        $sOut = $this->oShell->getCommandOutput(':modules version');
        $this->assertRegExp("~^Module~m", $sOut);
        $this->assertRegExp("~krzotr@gmail.com~m", $sOut);

        /* Remove after fixing versions numbers */
        $this->assertRegExp("~- (\d+\.\d+\.\d+|\d+\.\d+) ~m", $sOut);

        // $this->assertRegExp("~- \d+\.\d+\.\d+ ~m", $sOut);
    }

    public function testLoadModules()
    {
        $sContent = <<<'FILE'
            <?php
            class ModuleUnittestModule extends ModuleAbstract
            {
                public static function getCommands()
                {
                    return array('unittest');
                }

                public static function getVersion()
                {
                    return '9.9.9 2011-11-11 - <krzotr@gmail.com>';
                }

                public static function getHelp()
                {
                    return "Testowanie modulu\r\n\r\n    Użycie:\r\n        unittest";
                }

                public function get()
                {
                    return 'Unittest - Done';
                }

            }
FILE;

        $sTmpFile = '/tmp/' . md5(microtime(1));

        file_put_contents($sTmpFile, $sContent);

        $sOut = $this->oShell->getCommandOutput(':modules ' . $sTmpFile);
        @ unlink($sTmpFile);

        $this->assertSame("Moduł został załadowany\r\n", $sOut);

        /* Test module */
        $sOut = $this->oShell->getCommandOutput(':modules version');
        $this->assertRegExp(
            '~^ModuleUnittestModule\s+-\s+9\.9\.9 2011-11-11 - ~m',
            $sOut
        );

        $sOut = $this->oShell->getCommandOutput(':unittest');
        $this->assertSame("Unittest - Done\r\n", $sOut);
    }
}
