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
 * Testy modulu Id
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleIdTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $oModule;

    public function setUp()
    {
        $this->oShell = new Shell();
        $this->oModule = new ModuleId($this->oShell);
    }

    public function testModule()
    {
        $this->oShell->setArgs(':id ' . __FILE__);
        $this->assertSame(sprintf('user=%s uid=%d gid=%d', get_current_user(), getmyuid(), getmygid()), $this->oModule->get());

        $this->oShell->setArgs(':whoami ' . __FILE__);
        $this->assertSame(sprintf('user=%s uid=%d gid=%d', get_current_user(), getmyuid(), getmygid()), $this->oModule->get());
    }

}