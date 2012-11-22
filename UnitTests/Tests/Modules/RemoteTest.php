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
 * Testy modulu Remote
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage UnitTests
 */
class ModulePhpinfoTest extends PHPUnit_Framework_TestCase
{
	protected $oShell;
	protected $oModule;

	public function setUp()
	{
		$this -> oShell = new Shell();
		$this -> oModule = new ModuleRemote( $this -> oShell );
	}

	public function testModule()
	{
		$sCmd = md5( microtime( 1 ) );

		$this -> oShell -> parseCommand( ':remote http://hosting.iptcom.net/phpinfo.php ' . $sCmd );
		$this -> assertTrue( strpos( $this -> oModule -> get(), '&lt;tr&gt;&lt;td class=&quot;e&quot;&gt;_REQUEST[&quot;cmd&quot;]&lt;/td&gt;&lt;td class=&quot;v&quot;&gt;' . $sCmd . '&lt;/td&gt;&lt;/tr&gt;' ) !== FALSE  );
	}

}