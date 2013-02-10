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
 * Testy modulu Speedtest
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleSpeedtestTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;
	protected $sFilePath;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModuleSpeedtest( $this -> oShell );

		$this -> sFilePath = sys_get_temp_dir() . '/' . md5( time() );

		touch( $this -> sFilePath );

		if( ! is_file( $this -> sFilePath ) )
		{
			$this -> fail( 'Nie można utworzyć przykładowego pliku' );
		}
	}

	public function testModule()
	{
		$this -> oShell -> parseCommand( ':speedtest http://test.online.kz/download/1.test' );

		$this -> assertRegExp( '~^Pobrano: \d+ bajtów w \d+\.\d+ sekundy\r\nŚrednia prędkość to: \d+\.\d+ KB/s\z~', $this -> oModule -> get() );
	}

	public function testFailModule()
	{
		$this -> oShell -> parseCommand( ':speedtest http://file.not.found' );

		$this -> assertSame( 'Nie można pobrać pliku', $this -> oModule -> get() );
	}

}