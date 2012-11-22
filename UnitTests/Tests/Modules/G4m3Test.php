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
 * Testy modulu G4m3
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleG4m3Test extends PHPUnit_Framework_TestCase
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
		$this -> oShell -> parseCommand( ':G4m3 5' );
		$this -> assertTrue( (boolean) $this -> oModule -> get() );
	}

}