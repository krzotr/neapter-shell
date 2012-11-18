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
	public function setUp() {}

	public function testModule()
	{
		$oShell = new Shell();
		$oModule = new ModulePing( $oShell );

		$oShell -> parseCommand( ':echo TeST' );
		$this -> assertSame( 'TeST', $oModule -> get() );
	}

}