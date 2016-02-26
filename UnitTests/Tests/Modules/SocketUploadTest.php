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
 * Testy modulu SocketUpload
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleSocketUploadTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $oModule;

    public function setUp()
    {
        $this->oShell = new Shell();
        $this->oModule = new ModuleSocketUpload($this->oShell);
    }

    public function testModule()
    {
        $this->oShell->parseCommand('socketupload');
        $this->assertSame($this->oModule->getHelp(), $this->oModule->get());

        $this->oShell->parseCommand('socketup');
        $this->assertSame($this->oModule->getHelp(), $this->oModule->get());

        $this->oShell->parseCommand('socketput');
        $this->assertSame($this->oModule->getHelp(), $this->oModule->get());
    }

}