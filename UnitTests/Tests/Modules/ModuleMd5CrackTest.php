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
	public function setUp() {}

	public function testModule()
	{
		$oShell = new Shell();
		$oModule = new ModuleMd5Crack( $oShell );

		$oShell -> parseCommand( ':md5crack e10adc3949ba59abbe56e057f20f883e' );
		$this -> assertSame( "e10adc3949ba59abbe56e057f20f883e:123456\r\n", $oModule -> get() );

		$oShell -> parseCommand( ':md5crack ffffffffffffffffffffffffffffffff' );
		$this -> assertSame( "ffffffffffffffffffffffffffffffff:password-not-found\r\n", $oModule -> get() );

		$oShell -> parseCommand( ':md5crack 25f9e794323b453885f5181f1b624d0b 7ac66c0f148de9519b8bd264312c4d64 f6fdffe48c908deb0f4c3bd36c032e72' );
		$this -> assertSame( "25f9e794323b453885f5181f1b624d0b:123456789\r\n7ac66c0f148de9519b8bd264312c4d64:abcdefg\r\nf6fdffe48c908deb0f4c3bd36c032e72:adminadmin\r\n", $oModule -> get() );
	}

}