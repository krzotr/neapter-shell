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
 * Testy modulu Bcat
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleBcatTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModuleBcat( $this -> oShell );
	}

	public function testModule()
	{
		$this -> oShell -> parseCommand( ':bcat ' . __FILE__ );

		$sMime = sprintf( "MIME-Version: 1.0\r\nContent-Type: application/octet-stream; name=\"%s\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"%s\"\r\n\r\n",
			basename( __FILE__ ), basename( __FILE__ )
		);
		$sData = htmlspecialchars( $sMime . chunk_split( base64_encode( file_get_contents( __FILE__ ) ), 130 ) );

		$this -> assertSame( $sData, $this -> oModule -> get() );
	}

	public function testFailModule()
	{
		$this -> oShell -> parseCommand( ':cat x' . __FILE__ );
		$this -> assertSame( sprintf( 'Plik "x%s" nie istnieje', __FILE__ ), $this -> oModule -> get() );
	}

}