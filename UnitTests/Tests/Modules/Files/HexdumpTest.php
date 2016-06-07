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
 * Testy modulu Hexdump
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleHexdumpTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;
    protected $oModule;
    protected $sFilePath;

    public function setUp()
    {
        $this->oShell = new Shell();
        $this->oModule = new ModuleHexdump($this->oShell);

        $this->sFilePath = sys_get_temp_dir() . '/' . md5(time());

        if (!@ file_put_contents($this->sFilePath, "Plik Testowy 1234567890 abcdefghijk\r\n\r\n\r\nTest2")) {
            $this->fail('Nie można utworzyć przykładowego pliku');
        }
    }

    public function testModule()
    {
        $sData = "00000000  50 6c 69 6b 20 54 65 73  74 6f 77 79 20 31 32 33  |Plik Testowy 123|\r\n";
        $sData .= "00000010  34 35 36 37 38 39 30 20  61 62 63 64 65 66 67 68  |4567890 abcdefgh|\r\n";
        $sData .= "00000020  69 6a 6b 0d 0a 0d 0a 0d  0a 54 65 73 74 32        |ijk......Test2  |\r\n";

        $this->oShell->setArgs(':hd ' . $this->sFilePath);
        $this->assertSame($sData, $this->oModule->get());

        $this->oShell->setArgs(':hexdump ' . $this->sFilePath);
        $this->assertSame($sData, $this->oModule->get());
    }

}
