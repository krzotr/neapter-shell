<?php

/* @todo */

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Testy modulu BackConnect
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleBackConnectTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $oModule;

    public function setUp()
    {
        $this->oShell = new Shell();
        $this->oModule = new ModuleBackConnect($this->oShell);
    }

    public function testModule()
    {
        $this->oShell->parseCommand('bc');
        $this->assertSame($this->oModule->getHelp(), $this->oModule->get());

        $this->oShell->parseCommand('backconnect');
        $this->assertSame($this->oModule->getHelp(), $this->oModule->get());
    }

}
