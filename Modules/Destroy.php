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
 * Trwale usuwanie shella
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleDestroy extends ModuleAbstract
{
	/**
	 * Pierwszy klucz dostepowy
	 *
	 * @access private
	 * @var    string
	 */
	private $sKey;

	/**
	 * Drugi klucz dostepowy
	 *
	 * @access private
	 * @var    string
	 */
	private $sFinalKey;

	/**
	 * Konstruktor
	 *
	 * @access public
	 * @param  object $oShell Obiekt Shell
	 * @return void
	 */
	public function __construct( Shell $oShell )
	{
		parent::__construct( $oShell );

		$this -> sKey = strtoupper( substr( md5( Request::getServer( 'HOST' ) ), 0, 8 ) );

		$this -> sFinalKey = strtoupper( substr( md5( Request::getServer( 'SCRIPT_FILENAME' ) ), 0, 8 ) );
	}

	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array
		(
			'destroy',
			'removeshell'
		);
	}

	/**
	 * Zwracanie wersji modulu
	 *
	 * @access public
	 * @return string
	 */
	public function getVersion()
	{
		/**
		 * Wersja Data Autor
		 */
		return '1.00 2011-09-10 - <krzotr@gmail.com>';
	}

	/**
	 * Zwracanie pomocy modulu
	 *
	 * @access public
	 * @return string
	 */
	public function getHelp()
	{
		return sprintf( "Trwałe usuwanie shella\r\n\r\n\tUżycie:\r\n\t\tdestroy %s", $this -> sKey );
	}

	/**
	 * Wywolanie modulu
	 *
	 * @access public
	 * @return string
	 */
	public function get()
	{
		/**
		 * Help
		 */
		if( $this -> oShell -> iArgc === 0 )
		{
			return $this -> getHelp();
		}

		if( ( $this -> oShell -> aArgv[0] === $this -> sKey ) )
		{
			if( isset( $this -> oShell -> aArgv[1] ) && ( $this -> oShell -> aArgv[1] === $this -> sFinalKey ) )
			{
				$sFilePath = Request::getServer( 'SCRIPT_FILENAME' );

				/**
				 * Usuwanie pliku
				 */
				if( ! unlink( $sFilePath ) )
				{
					$this -> oShell -> getCommandSystem( sprintf( 'rm %s', $sFilePath ) );
				}

				return sprintf( 'Shell %szostał usunięty', ( ! is_file( $sFilePath ) ? NULL : 'nie ' ) );
			}
			else
			{
				return sprintf( "Na pewno chcesz usunąć tego szela?\r\nJeżeli tak to wywołaj poniższe polecenie:\r\n\r\n\t:destroy %s %s\r\n\r\nPamiętaj, aby ciąg %s wpisać w odwrotniej kolejności",
					$this -> sKey, $sFinalKey = strrev( $this -> sFinalKey ), $sFinalKey
				);
			}
		}

		return $this -> getHelp();
	}

}