<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Testy modulu Revip
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleRevipTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $oModule;

    public function setUp()
    {
        $this->oShell = new Shell();
        $this->oModule = new ModuleRevip($this->oShell);
    }

    public function testModule()
    {
        $this->oShell->setArgs(':revip nk.pl');
        $this->assertSame("Zwrócono 1 witryn:\r\n\r\n\tnk.pl", $this->oModule->get());

        $this->oShell->setArgs(':revip onet.pl');
        $this->assertSame('Brak adresów IP', $this->oModule->get());
    }

    public function testFailModule()
    {
        $this->oShell->setArgs(':revip ThisnotExists.host');
        $this->assertSame('Nie można przetłumacz hosta', $this->oModule->get());
    }

}