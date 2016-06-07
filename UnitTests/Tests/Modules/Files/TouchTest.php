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
 * Testy modulu Touch
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleTouchTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $oModule;
    protected $sFilePath;

    public function setUp()
    {
        $this->oShell = new Shell();
        $this->oModule = new ModuleTouch($this->oShell);

        $this->sFilePath = sys_get_temp_dir() . '/' . md5(time());

        touch($this->sFilePath);

        if (!is_file($this->sFilePath)) {
            $this->fail('Nie można utworzyć przykładowego pliku');
        }
    }

    public function testModule()
    {
        $this->oShell->setArgs(':touch 2010-10-10 ' . $this->sFilePath);

        $this->assertSame('Data modyfikacji i dostępu została zmieniona', $this->oModule->get());

        clearstatcache();

        $this->assertSame('2010-10-10', date('Y-m-d', fileatime($this->sFilePath)));
    }

    public function tearDown()
    {
        @ unlink($this->sFilePath);
    }

}
