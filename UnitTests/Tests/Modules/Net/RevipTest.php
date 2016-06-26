<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleRevipTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleRevip::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':revip help');
        $this->assertSame(ModuleRevip::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':revip');
        $this->assertSame(ModuleRevip::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput(':revip 127.0.0.1');
        $this->assertSame("Zwrócono 1 witryn:\r\n\r\n  dxhsjlb.com\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':revip przemo.org');
        $this->assertSame("Zwrócono 1 witryn:\r\n\r\n  przemo.org\r\n", $sOut);
    }

    public function testFail()
    {
        $sOut = $this->oShell->getCommandOutput(':revip xxxaaaaabb.xc');
        $this->assertSame("Nie można przetłumacz hosta\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':revip 0.0.0.0');
        $this->assertSame("Brak hostów na podanym adresie IP\r\n", $sOut);
    }

    public function testConnectionError()
    {
        ini_set('default_socket_timeout', '0');

        $sOut = $this->oShell->getCommandOutput(':revip 127.0.0.1');
        $this->assertSame("Nie można połączyć się z serwerem\r\n", $sOut);

        ini_restore('default_socket_timeout');
    }
}
