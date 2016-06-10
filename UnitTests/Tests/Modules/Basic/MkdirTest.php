<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleMkdirTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleMkdir::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':mkdir');
        $this->assertSame(ModuleMkdir::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sTmpDir = '/tmp/' . md5(microtime(1));

        $sOut = $this->oShell->getCommandOutput(':mkdir ' . $sTmpDir);

        $this->assertSame(
            sprintf("Katalog \"%s\" został utworzony\r\n\r\n", $sTmpDir),
            $sOut
        );
    }

    public function testCreateTwoDirs()
    {
        $sTmpDir = '/tmp/' . md5(microtime(1));
        $sTmpDir2 = '/tmp/' . sha1(microtime(1));

        $sOut = $this->oShell->getCommandOutput(
            sprintf(
                ':mkdir %s %s',
                $sTmpDir,
                $sTmpDir2
            )
        );

        $this->assertSame(
            sprintf(
                "Katalog \"%s\" został utworzony\r\nKatalog \"%s\" został utworzony\r\n\r\n",
                $sTmpDir,
                $sTmpDir2
            ),
            $sOut
        );

        @ rmdir($sTmpDir);
        @ rmdir($sTmpDir2);
    }

    public function testCreateInvalidDir()
    {
        $sOut = $this->oShell->getCommandOutput(':mkdir /dev/null');

        $this->assertSame(
            "Katalog \"/dev/null\" nie został utworzony\r\n\r\n",
            $sOut
        );
    }

}
