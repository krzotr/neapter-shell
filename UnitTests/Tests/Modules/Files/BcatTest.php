<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleBcatTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleBcat::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':bcat help');
        $this->assertSame(ModuleBcat::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':bcat');
        $this->assertSame(ModuleBcat::getHelp() . "\r\n", $sOut);
    }

    public function testFileDoesntExist()
    {
        $sOut = $this->oShell->getCommandOutput(':bcat /dev/abx/cde');
        $this->assertSame("Plik \"/dev/abx/cde\" nie istnieje\r\n", $sOut);
    }

    public function testModule()
    {
        $sExpected = sprintf(
            "MIME-Version: 1.0\r\n" .
            "Content-Type: application/octet-stream; name=\"passwd\"\r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-Disposition: attachment; filename=\"passwd\"" .
            "\r\n\r\n%s\r\n",
            chunk_split(
                base64_encode(file_get_contents('/etc/passwd')),
                130
            )
        );

        $sOut = $this->oShell->getCommandOutput(':bcat /etc/passwd');
        $this->assertSame($sExpected, $sOut);
    }
}
