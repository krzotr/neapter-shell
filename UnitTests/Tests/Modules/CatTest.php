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
 * Testy modulu Cat
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleCatTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $oModule;

    public function setUp()
    {
        $this->oShell = new Shell();
        $this->oModule = new ModuleCat($this->oShell);
    }

    public function testModule()
    {
        $this->oShell->setArgs(':cat ' . __FILE__);
        $this->assertSame(htmlspecialchars(file_get_contents(__FILE__)), $this->oModule->get());

        $this->oShell->setArgs(':cat x' . __FILE__);
        $this->assertSame(sprintf('Plik "x%s" nie istnieje', __FILE__), $this->oModule->get());
    }


    public function testFailModule()
    {
        $this->oShell->setArgs(':cat x' . __FILE__);
        $this->assertSame(sprintf('Plik "x%s" nie istnieje', __FILE__), $this->oModule->get());
    }

}