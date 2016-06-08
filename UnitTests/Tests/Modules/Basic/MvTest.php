<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleMvTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleMv::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':mv help');
        $this->assertSame(ModuleMv::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':mv');
        $this->assertSame(ModuleMv::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':mv file1');
        $this->assertSame(ModuleMv::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':mv file1 file2 file3');
        $this->assertSame(ModuleMv::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sSource = '/tmp/' . md5(microtime(1));
        file_put_contents($sSource, '');

        $sDestination = '/tmp/' . sha1(microtime(1));

        $sOut = $this->oShell->getCommandOutput(
            sprintf(":mv %s %s", $sSource, $sDestination)
        );

        $this->assertSame(
            sprintf(
                "Plik \"%s\" został przeniesiony do \"%s\"\r\n",
                $sSource,
                $sDestination
            ),
            $sOut
        );
    }

    public function testModuleError()
    {
        $sSource = '/tmp/' . md5(microtime(1));
        file_put_contents($sSource, '');

        $sDestination = '/tmp/a/b/c/d/e/f/g/h';

        $sOut = $this->oShell->getCommandOutput(
            sprintf(":mv %s %s", $sSource, $sDestination)
        );

        $this->assertSame(
            sprintf(
                "Plik \"%s\" nie został przeniesiony do \"%s\"\r\n",
                $sSource,
                $sDestination
            ),
            $sOut
        );
    }

}
