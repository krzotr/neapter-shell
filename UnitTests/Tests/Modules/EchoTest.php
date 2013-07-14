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
 * Testy modulu Echo
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleEchoTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModuleEcho( $this -> oShell );
	}

	public function testModule()
	{
		$this -> oShell -> setArgs( ':echo TeST' );
		$this -> assertSame( 'TeST', $this -> oModule -> get() );

		$this -> oShell -> setArgs( ':echo TeST test2 TeST' );
		$this -> assertSame( 'TeST test2 TeST', $this -> oModule -> get() );
	}

}