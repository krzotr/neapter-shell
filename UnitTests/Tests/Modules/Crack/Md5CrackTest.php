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
 * Testy modulu Md5Crack
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleMd5CrackTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleMd5Crack::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':md5crack help');
        $this->assertSame(ModuleMd5Crack::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':md5crack');
        $this->assertSame(ModuleMd5Crack::getHelp() . "\r\n", $sOut);
    }

    public function testMd5()
    {
        $sOut = $this->oShell->getCommandOutput(
            ':md5crack e10adc3949ba59abbe56e057f20f883e'
        );
        $this->assertSame(
            "e10adc3949ba59abbe56e057f20f883e:123456\r\n\r\n",
            $sOut
        );

        $sOut = $this->oShell->getCommandOutput(
            ':md5crack B36D331451A61EB2D76860E00C347396'
        );
        $this->assertSame(
            "b36d331451a61eb2d76860e00c347396:killer\r\n\r\n",
            $sOut
        );
    }

    public function testMd5s()
    {
        $sOut = $this->oShell->getCommandOutput(
            ":md5crack 25f9e794323b453885f5181f1b624d0b " .
            "7ac66c0f148de9519b8bd264312c4d64 " .
            "f6fdffe48c908deb0f4c3bd36c032e72"
        );
        $this->assertSame(
            "25f9e794323b453885f5181f1b624d0b:123456789\r\n" .
            "7ac66c0f148de9519b8bd264312c4d64:abcdefg\r\n" .
            "f6fdffe48c908deb0f4c3bd36c032e72:adminadmin\r\n\r\n",
            $sOut
        );
    }

    public function testMd5uncracked()
    {
        $sOut = $this->oShell->getCommandOutput(
            ':md5crack ffffffffffffffffffffffffffffffff'
        );
        $this->assertSame(
            "ffffffffffffffffffffffffffffffff:password-not-found\r\n\r\n",
            $sOut
        );
    }

    public function testMd5Error()
    {
        $sOut = $this->oShell->getCommandOutput(
            ':md5crack fffffffffffffffffffffffffffffffffffff'
        );
        $this->assertSame("\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(
            ':md5crack fffffffffff'
        );
        $this->assertSame("\r\n", $sOut);
    }
}
