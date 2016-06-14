<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleAbstractTest extends PHPUnit_Framework_TestCase
{
    public function testModuleAbstract()
    {
        $oModule = new ModulePing(new Shell());
        $oModule->setArgs(':id');

        $this->assertSame('pong', $oModule->get());
    }
}
