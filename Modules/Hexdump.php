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
 * Wyswietlanie zawartosci pliku w formacie szesnastkowym
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleHexdump extends ModuleAbstract
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
		if( $this -> oShell -> getArgs() -> getNumberOfParams() === 0 )
		{
			return $this -> getHelp();
		}

		/**
		 * Plik zrodlowy musi istniec
		 */

		$sFileName = $this -> oShell -> getArgs() -> getParam( 0 );

		if( ! is_file( $sFileName ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $sFileName );
		}

		if( ! ( $rFile = fopen( $sFileName, 'r' ) ) )
		{
			return sprintf( 'Nie można otworzyć pliku "%s"', $sFileName );
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
			$sLine = str_pad( base_convert( $i, 10, 16 ), 8, '0', STR_PAD_LEFT ) . "\x20\x20";

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