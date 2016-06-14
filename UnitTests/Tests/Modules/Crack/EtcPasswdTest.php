<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleEtcPasswdTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleEtcPasswd::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':etcpasswd help');
        $this->assertSame(ModuleEtcPasswd::getHelp() . "\r\n", $sOut);
    }

    protected function scan($iMin, $iMax)
    {
        $sOutput = '';

        for ($i = $iMin; $i <= $iMax; ++$i) {
            if (($aUser = posix_getpwuid($i)) !== false) {
                $sOutput .= sprintf(
                    "%s:%s:%d:%d:%s:%s:%s\r\n",
                    $aUser['name'],
                    $aUser['passwd'],
                    $aUser['uid'],
                    $aUser['gid'],
                    $aUser['gecos'],
                    $aUser['dir'],
                    $aUser['shell']
                );
            }
        }

        return $sOutput . "\r\n";
    }

    public function testModuleErrors()
    {
        $sOut = $this->oShell->getCommandOutput(':etcpasswd 50 5');
        $this->assertSame("Błędny zakres górny\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':etcpasswd 500000 5000000');
        $this->assertSame("Błędny zakres dolny\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':etcpasswd 5000 5000000');
        $this->assertSame("Błędny zakres górny\r\n", $sOut);
    }

    public function testRange()
    {
        $sOut = $this->oShell->getCommandOutput(':etcpasswd 0 1100');
        $this->assertSame($this->scan(0, 1100), $sOut);
    }

    public function testFullScan()
    {
        $sOut = $this->oShell->getCommandOutput(':etcpasswd');
        $this->assertSame($this->scan(0, 1100), $sOut);
    }
}
