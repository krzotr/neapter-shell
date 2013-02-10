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
 * Testy modulu Mkdir
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleMkdirTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;
	protected $sTmoPath;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModuleMkdir( $this -> oShell );
		$this -> sTmoPath = sys_get_temp_dir() . '/';
	}

	public function testModule()
	{
		$sTmpPath = $this -> sTmoPath . md5( microtime() );

		$this -> oShell -> parseCommand( ':mkdir ' . $sTmpPath );
		$this -> assertSame( $this -> oModule -> get(), sprintf( "Katalog \"%s\" <span class=\"green\">został utworzony</span>\r\n", $sTmpPath ) );
		clearstatcache();
		$this -> assertTrue( is_dir( $sTmpPath ) );


		$sTmpPath1 = $this -> sTmoPath . md5( microtime() ) . '/' . md5( microtime() ) . '/' . md5( microtime() ) . '/' . md5( microtime() );
		$sTmpPath2 = $this -> sTmoPath . md5( microtime() ) . '/' . md5( microtime() ) . '/' . md5( microtime() );
		$sTmpPath3 = $this -> sTmoPath . md5( microtime() ) . '/' . md5( microtime() );

		clearstatcache();
		$this -> oShell -> parseCommand( ':mkdir ' . $sTmpPath1 . ' ' . $sTmpPath2 . ' ' . $sTmpPath3);
		$this -> assertSame( $this -> oModule -> get(), "Katalog \"{$sTmpPath1}\" <span class=\"green\">został utworzony</span>\r\nKatalog \"{$sTmpPath2}\" <span class=\"green\">został utworzony</span>\r\nKatalog \"{$sTmpPath3}\" <span class=\"green\">został utworzony</span>\r\n" );
		clearstatcache();
		$this -> assertTrue( is_dir( $sTmpPath1 ) );
		$this -> assertTrue( is_dir( $sTmpPath2 ) );
		$this -> assertTrue( is_dir( $sTmpPath3 ) );
	}


	public function testFailModule()
	{
		$sTmpPath = $this -> sTmoPath . str_repeat( 'x', 1000 );

		$this -> oShell -> parseCommand( ':mkdir ' . $sTmpPath );

		$this -> assertSame( $this -> oModule -> get(), sprintf( "Katalog \"%s\" <span class=\"red\">nie został utworzony</span>\r\n", $sTmpPath ) );
		clearstatcache();
		$this -> assertFalse( is_dir( $sTmpPath ) );
	}

}