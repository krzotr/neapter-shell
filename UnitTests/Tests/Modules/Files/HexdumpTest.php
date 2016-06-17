<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleHexdumpTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $sFile = '/etc/group';

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleHexdump::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':hexdump help');
        $this->assertSame(ModuleHexdump::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':hexdump');
        $this->assertSame(ModuleHexdump::getHelp() . "\r\n", $sOut);
    }

    public function testHexDump()
    {
        $sOut = $this->oShell->getCommandOutput(':hexdump ' . $this->sFile);
        $this->assertSame(
            shell_exec('hexdump -C ' . escapeshellcmd($this->sFile)),
            str_replace("\r\n", "\n", $sOut)
        );
    }

    public function testInvalidFIle()
    {
        $sOut = $this->oShell->getCommandOutput(':hexdump /dev/abc/def');
        $this->assertSame(
            "Nie można otworzyć pliku \"/dev/abc/def\"\r\n",
            $sOut
        );
    }
}
