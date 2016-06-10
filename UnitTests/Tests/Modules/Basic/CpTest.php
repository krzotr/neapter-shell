<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleMvTeCp extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleCp::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':cp help');
        $this->assertSame(ModuleCp::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':cp');
        $this->assertSame(ModuleCp::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':cp file1');
        $this->assertSame(ModuleCp::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':cp file1 file2 file3');
        $this->assertSame(ModuleCp::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sSource = '/tmp/' . md5(microtime(1));
        file_put_contents($sSource, str_repeat('x', 1024));

        $sDestination = '/tmp/' . sha1(microtime(1));

        $sOut = $this->oShell->getCommandOutput(
            sprintf(":cp %s %s", $sSource, $sDestination)
        );

        $this->assertSame(
            sprintf(
                "Plik \"%s\" został skopiowany do \"%s\"\r\n",
                $sSource,
                $sDestination
            ),
            $sOut
        );

        $this->assertSame(
            str_repeat('x', 1024),
            file_get_contents($sDestination)
        );

        @ unlink($sSource);
        @ unlink($sDestination);
    }

    public function testModuleError()
    {
        $sSource = '/tmp/' . md5(microtime(1));
        file_put_contents($sSource, '');

        $sDestination = '/tmp/a/b/c/d/e/f/g/h';

        $sOut = $this->oShell->getCommandOutput(
            sprintf(":cp %s %s", $sSource, $sDestination)
        );

        $this->assertSame(
            sprintf(
                "Plik \"%s\" nie został skopiowany do \"%s\"\r\n",
                $sSource,
                $sDestination
            ),
            $sOut
        );

        @ unlink($sSource);
    }

}
