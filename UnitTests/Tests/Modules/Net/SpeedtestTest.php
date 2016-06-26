<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleSpeedtestTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    protected $sFormat = "Pobrano: %f KB w %f sekund\r\nŚrednia prędkość:" .
                         "%f KB/s\r\n";

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleSpeedtest::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':speedtest help');
        $this->assertSame(ModuleSpeedtest::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(
            ':speedtest http://www.google.com http://www.wp.pl'
        );
        $this->assertSame(ModuleSpeedtest::getHelp() . "\r\n", $sOut);
    }

    public function testDownload()
    {
        $sOut = $this->oShell->getCommandOutput(':speedtest');
        $this->assertTrue(count(sscanf($sOut, $this->sFormat)) === 3);

        $sOut = $this->oShell->getCommandOutput(
            ':speedtest http://noc.leon.pl/test/10MB.bin'
        );
        $this->assertTrue(count(sscanf($sOut, $this->sFormat)) === 3);
    }

    public function testErrors()
    {
        $sOut = $this->oShell->getCommandOutput(
            ':speedtest ftp://noc.leon.pl/test/10MB.bin'
        );
        $this->assertSame("Wspierany jest tylko protokół http!\r\n", $sOut);


        $sOut = $this->oShell->getCommandOutput(
            ':speedtest http://noc.leon.pl/test/1MB.bin'
        );
        $this->assertSame("Nie można pobrać pliku!\r\n", $sOut);
    }
}
