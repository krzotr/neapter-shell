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
 * Testy modulu Cd
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleCdTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModuleCd( $this -> oShell );
	}

	public function testModule()
	{
		$this -> oShell -> parseCommand( ':cd ..' );
		$this -> assertSame( getcwd(), realpath( dirname( __DIR__ ) . DIRECTORY_SEPARATOR . '..' ) );
	}


	public function testFailModule()
	{
		$this -> oShell -> parseCommand( ':cd directory_not_ExiSt5' );
		$this -> assertSame( getcwd(), realpath( dirname( __DIR__ ) . DIRECTORY_SEPARATOR . '..' ) );
	}

}