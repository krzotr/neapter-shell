<?php

/**
 * ModuleHexdump - Wyswietlanie plikow w formacie szesnastkowym
 */
class ModuleHexdump implements ShellInterface
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
			'hexdump',
			'hd'
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
		return '1.0 2011-06-19 - <krzotr@gmail.com>';
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
Wyświetlanie plików w formacie szesnastkowym

	Użycie:
		hexdump ścieżka_do_pliku

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

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> oShell -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> oShell -> sArgv );
		}

		if( ! ( $rFile = fopen( $this -> oShell -> sArgv, 'r' ) ) )
		{
			return sprintf( 'Nie można otworzyć pliku "%s"', $this -> oShell -> sArgv );
		}

		$i = 0;
		$sOutput = NULL;

		/**
		 * Odczyt zawartosci pliku
		 */
		while( ! feof( $rFile ) )
		{
			/**
			 * Odczyt 16 bajtow
			 */
			$sData = fread( $rFile, 16 );

			/**
			 * Adres
			 */
			$sLine = str_pad( base_convert( $i, 10, 16 ), 8, '0', STR_PAD_LEFT ) . '  ';

			/**
			 * Wartosci w HEX
			 */
			$iLength = strlen( $sData );
			for( $j = 0; $j < $iLength; $j++ )
			{
				$sLine .= bin2hex( substr( $sData, $j, 1 ) ) . ' ';

				/**
				 * Odstep miedzy oktetami
				 */
				if( $j === 7 )
				{
					$sLine .= ' ';
				}
			}

			/**
			 * Wypelnienie spacjami
			 */
			$sLine = str_pad( $sLine, 60, ' ', STR_PAD_RIGHT );

			/**
			 * Zawartosc
			 */
			$sLine .= '|' . str_pad( htmlspecialchars( preg_replace( '~[^\x20-\x7f]~', '.', $sData ) ), 16, ' ', STR_PAD_RIGHT ) . "|\r\n";

			$i += 16;

			$sOutput .= $sLine;
		}

		return $sOutput;
	}

}