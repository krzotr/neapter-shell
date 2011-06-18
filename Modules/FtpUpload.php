<?php

/**
 * ModuleFtpUpload - Wysylanie pliku na FTP
 */
class ModuleFtpUpload implements ShellInterface
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
		return array
		(
			'ftpupload',
			'ftpup',
			'ftpput'
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
		return '1.0 2011-06-04 - <krzotr@gmail.com>';
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
Wysyłanie pliku na FTP

	Użycie:
		ftpupload host:port login@hasło plik_źródłowy ścieżka_docelowa

	Przykład:
		ftpupload localhost:6666 test@test /home/usr/plik.txt /
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
		if( $this -> oSHell -> iArgc !== 4 )
		{
			return $this -> getHelp();
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> oSHell -> aArgv[2] ) )
		{
			return htmlspecialchars( sprintf( 'Plik "%s" nie istnieje', $this -> oSHell -> aArgv[2] ) );
		}

		$aHost = $this -> oSHell -> getHost( $this -> oSHell -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			$aHost[1] = 21;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> oSHell -> aArgv[1] );

		/**
		 * Ustanawianie polaczenia
		 */
		if( ! ( $rFtp = ftp_connect( $aHost[0], $aHost[1], 5 ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można połączyć się z serwerem FTP "%s"', $this -> oSHell -> aArgv[0] ) );
		}

		/**
		 * Autoryzacja
		 */
		if( ! ftp_login( $rFtp, $sUsername, $sPassword ) )
		{
			return htmlspecialchars( sprintf( 'Błędne dane do autoryzacji "%s"', $this -> oSHell -> aArgv[1] ) );
		}

		/**
		 * Zmiana katalogu
		 */
		if( ! ftp_chdir( $rFtp, $this -> aArgv[3] ) )
		{
			return htmlspecialchars( sprintf( 'Na FTP nie istnieje katalog "%s"', $this -> oSHell -> aArgv[2] ) );
		}

		/**
		 * Wrzucanie pliku
		 */
		if( ! ftp_put( $rFtp, basename( $this -> aArgv[2] ), $this -> oSHell -> aArgv[2], FTP_BINARY ) )
		{
			return htmlspecialchars( sprintf( 'Nie można wgrać pliku "%s" na serwer', $this -> oSHell -> aArgv[2] ) );
		}

		ftp_close( $rFtp );

		return htmlspecialchars( sprintf( 'Plik "%s" został pomyślnie wgrany an FTP', $this -> oSHell -> aArgv[2] ) );
	}

}