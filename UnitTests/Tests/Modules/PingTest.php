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
 * Testy modulu Ping
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModulePingTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModulePing( $this -> oShell );
	}

	public function testModule()
	{
		$this -> oShell -> setArgs( ':ping' );
		$this -> assertSame( 'pong', $this -> oModule -> get() );
	}

}