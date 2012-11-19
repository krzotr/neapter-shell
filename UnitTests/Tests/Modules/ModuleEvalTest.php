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
 * Testy modulu Eval
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModuleEvalTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModuleEval( $this -> oShell );
	}

	public function testModule()
	{
		$this -> oShell -> parseCommand( ':echo echo md5("test");' );
		$this -> assertSame( '098f6bcd4621d373cade4e832627b4f6', $this -> oModule -> get() );

		$this -> oShell -> parseCommand( ':php echo md5("test2");' );
		$this -> assertSame( 'ad0234829205b9033196ba818f7a872b', $this -> oModule -> get() );
	}

	public function testFailModule()
	{
		$this -> oShell -> parseCommand( ':php echo md5("test2")' );
		$this -> assertSame( '', $this -> oModule -> get() );
	}

}