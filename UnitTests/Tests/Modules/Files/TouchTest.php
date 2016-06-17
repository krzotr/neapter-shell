<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleTouchTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleTouch::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':touch help');
        $this->assertSame(ModuleTouch::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':touch');
        $this->assertSame(ModuleTouch::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sTmp = '/tmp/' . md5(microtime(1));
        file_put_contents($sTmp, str_repeat('x', 1024));

        $sOut = $this->oShell->getCommandOutput(':touch 2005-05-05 ' . $sTmp);
        $this->assertSame(
            "Data modyfikacji i dostępu została zmieniona\r\n",
            $sOut
        );

        clearstatcache();

        $this->assertSame(
            '2005-05-05',
            date('Y-m-d', filemtime($sTmp))
        );

        @unlink($sTmp);
    }

    public function testFileDoesntExist()
    {
        $sOut = $this->oShell->getCommandOutput(':touch 2016-06-06 /tmp/ad/ax');
        $this->assertSame("Plik \"/tmp/ad/ax\" nie istnieje\r\n", $sOut);
    }


    public function testFail()
    {
        $sOut = $this->oShell->getCommandOutput(
            ':touch 2016-06-06 /etc/passwd'
        );
        $this->assertSame(
            "Data modyfikacji i dostępu nie została zmieniona\r\n",
            $sOut
        );
    }
}
