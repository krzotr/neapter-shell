<?php

/**
 * Neapter Framework
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * class PackerException - Pack wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    Lib
 * @subpackage Exception
 *
 * @uses       Exception
 */
class PackerException extends Exception {}

/**
 * class Pack - Pakowanie plikow
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    Lib
 * @subpackage Pack
 *
 * @uses       XRecursiveDirectoryIterator
 * @uses       RecursiveIteratorIterator
 */
class Pack
{
	/**
	 * Sciezka zawierajaca pliki do spakowania
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sInput;

	/**
	 * Plik wynikowy
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sOutput;

	/**
	 * Ustawienia sciezki
	 *
	 * @access public
	 * @param  string $sValue Sciezka
	 * @return Pack   Obiekt  Pack
	 */
	public function setInput( $sValue )
	{
		$this -> sInput = realpath( (string) $sValue );

		return $this;
	}

	/**
	 * Ustawienia sciezki do pliku wynikowego
	 *
	 * @access public
	 * @param  string $sValue Sciezka
	 * @return Pack   Obiekt  Pack
	 */
	public function setOutput( $sValue )
	{
		$this -> sOutput = (string) $sValue;

		return $this;
	}

	/**
	 * Wykonanie procesu pakowania pliku
	 *
	 * @access public
	 * @return void
	 */
	public function get()
	{
		/**
		 * Sciezka jest wymagana
		 */
		if( $this -> sInput === NULL )
		{
			throw new PackerException( 'Nie wybrano katalogu źródłowego' );
		}

		/**
		 * Folder zrodlowy musi istniec
		 */
		if( ! is_dir( $this -> sInput ) )
		{
			throw new PackerException( 'Katalog źródłowy nie istnieje' );
		}

		/**
		 * Sciezka do pliku docelowego jest wymagana
		 */
		if( $this -> sOutput === NULL )
		{
			throw new PackerException( 'Nie wybrano pliku docelowego' );
		}

		try
		{
			/**
			 * Iteracyjne przeszukanie katalogu
			 */
			$oDirectory = new RecursiveIteratorIterator( new XRecursiveDirectoryIterator( $this -> sInput ) );
		}
		catch( UnexpectedValueException $oException )
		{
			throw new PackerException( sprintf( 'Wystąpił nieoczekiwany błąd: %s', $oException -> getMessage() ) );
		}

		/**
		 * Otwieranie pliku do zapisu
		 */
		if( ! ( $rFile = @ fopen( $this -> sOutput, 'w' ) ) )
		{
			throw new PackerException( 'Nie można utworzyć pliku wynikowego' );
		}

		/**
		 * Blokowanie pliku
		 */
		flock( $rFile, LOCK_EX );

		/**
		 * Naglowek - 16 bajtow
		 * NFPACK - 8 bajtow
		 * nastepne 8 bajtow sa zarezerwowane
		 */
		fwrite( $rFile, "NFPACKER\0\0\0\0\0\0\0\0" );

		/**
		 * Dlugosc sciezki
		 */
		$iPathLen = strlen( $this -> sInput );

		/**
		 *  2 bajty na dlugosc sciezki do pliku
		 * X bajtow na sciezke
		 *  4 bajty na rozmiar pliku
		 * X bajtow na zawartosc pliku
		 *  4 bajty na hash pliku
		 */
		foreach( $oDirectory as $oDir )
		{
			/**
			 * Element musi byc plikiem
			 */
			if( ! $oDir -> isFile() )
			{
				continue ;
			}

			/**
			 * Relatywna Sciezka
			 */
			$sPathName = $oDir -> getPathName();

			$sPath = substr( $sPathName, $iPathLen );

			if( ( strncmp( $sPath, '/', 1 ) === 0 ) || ( strncmp( $sPath, '\\', 1 ) === 0 ) )
			{
				$sPath = substr( $sPath, 1 );
			}

			/**
			 * Pierwsze 2 bajty to dlugosc sciezki do pliku
			 */
			fwrite( $rFile, pack( 'n', strlen( $sPath ) ) );

			/**
			 * Kolejno umieszczona jest sciezka do pliku
			 */
			fwrite( $rFile, str_replace( '\\', '/', $sPath ) );

			/**
			 * Nastepne 4 bajty to rozmiar pliku
			 */
			fwrite( $rFile, pack( 'N', $oDir -> getSize() ) );

			/**
			 * Otwieranie pliku
			 */
			if( ! ( $rFileContent = fopen( $sPathName, 'r' ) ) )
			{
				flock( $rFile, LOCK_UN );

				fclose( $rFileContent );

				throw new PackerException( sprintf( 'Nie można otworzyć pliku "%s"', $sPathName ) );
			}

			/**
			 * Odczyt i zapisanie pliku
			 */
			while( ! feof( $rFileContent ) )
			{
				fwrite( $rFile, fread( $rFileContent, 65536 ) );
			}

			/**
			 * 4 bajty to suma kontrolna
			 */
			fwrite( $rFile, substr( md5_file( $sPathName, TRUE ), 0, 4 ) );

			/**
			 * Zamkniecie pliku
			 */
			fclose( $rFileContent );
		}

		/**
		 * Zdjecie blokady z pliku
		 */
		flock( $rFile, LOCK_UN );

		/**
		 * Zamkniecie pliku
		 */
		fclose( $rFile );
	}

}

/**
 * class Pack - Rozpakowanie pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    Lib
 * @subpackage Pack
 *
 * @uses       XRecursiveDirectoryIterator
 * @uses       RecursiveIteratorIterator
 */
class Unpack
{
	/**
	 * Plik zrodlowy
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sInput;

	/**
	 * Plik wynikowy
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sOutput;

	/**
	 * Ustawienia sciezki
	 *
	 * @access public
	 * @param  string $sValue Sciezka
	 * @return Pack   Obiekt  Pack
	 */
	public function setInput( $sValue )
	{
		$this -> sInput = realpath( (string) $sValue );

		return $this;
	}

	/**
	 * Ustawienia sciezki do pliku wynikowego
	 *
	 * @access public
	 * @param  string $sValue Sciezka
	 * @return Pack   Obiekt  Pack
	 */
	public function setOutput( $sValue )
	{
		$this -> sOutput = realpath( (string) $sValue ) . '/';

		return $this;
	}

	/**
	 * Wykonanie procesu pakowania pliku
	 *
	 * @access public
	 * @return string Output
	 */
	public function get()
	{
		$sOutput = NULL;

		/**
		 * Plik zrodlowy jest wymagany
		 */
		if( $this -> sInput === NULL )
		{
			throw new PackerException( 'Nie wybrano pliku źródłowego' );
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> sInput ) )
		{
			throw new PackerException( 'Plik źródłowy nie istnieje' );
		}

		/**
		 * Sciezka do pliku docelowego jest wymagana
		 */
		if( $this -> sOutput === NULL )
		{
			throw new PackerException( 'Nie wybrano pliku docelowego' );
		}

		/**
		 * Otwieranie pliku zrodlowego do odczytu
		 */
		if( ! ( $rFile = @ fopen( $this -> sInput, 'r' ) ) )
		{
			throw new PackerException( sprintf( 'Nie można otworzyć pliku: %s', $this -> sInput ) );
		}

		/**
		 * Blokowanie pliku
		 */
		flock( $rFile, LOCK_EX );

		/**
		 * Sprawdzanie czy naglowek jest poprawny
		 */
		if( fread( $rFile, 8 ) !== 'NFPACKER' )
		{
			throw new PackerException( 'Plik źródłowy ma niewłaściwy format' );
		}

		/**
		 * Pominiecie naglowka
		 */
		fseek( $rFile, 16 );

		while( ! feof( $rFile ) )
		{
			/**
			 * Rozmiar sciezki
			 */
			if( ( $sData = fread( $rFile, 2 ) ) === '' )
			{
				break ;
			}

			list( , $iSize ) = unpack( 'n', $sData );

			/**
			 * Sciezka
			 */
			$sFileName = fread( $rFile, $iSize );

			/**
			 * Rozmiar pliku
			 */
			list( , $iSize ) = unpack( 'N', fread( $rFile, 4 ) );

			/**
			 * Tworzenie katalogu
			 */
			if( ! is_dir( ( $sPathName = dirname( $this -> sOutput . $sFileName ) ) ) )
			{
				mkdir( $sPathName, 0777, TRUE );
			}

			$sFileName = $this -> sOutput . $sFileName;

			/**
			 * Zapis do pliku
			 */
			if( ! file_put_contents( $sFileName, ( $iSize === 0 ? NULL : fread( $rFile, $iSize ) ) ) )
			{
				throw new PackerException( sprintf( 'Nie można zapisać pliku %s', $sFileName ) );
			}

			/**
			 * Sprawdzanie sumu kontrolnej
			 */
			if( ! strncmp( md5_file( $sFileName, TRUE ), fread( $rFile, 4 ), 4 ) === 0 )
			{
				flock( $rFile, LOCK_UN );

				fclose( $rFile );

				throw new PackerException( sprintf( 'Błędna suma kontrolna pliku: %s', $sPathName ) );
			}

			$sOutput .= $sFileName . "\r\n";
		}

		/**
		 * Zdjecie blokady z pliku
		 */
		flock( $rFile, LOCK_UN );

		/**
		 * Zamkniecie pliku
		 */
		fclose( $rFile );

		return $sOutput;
	}

}

/**
 * =================================================================================================
 */

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * ModulePacker - Pakowanie / rozpakowywanie plikow oraz katalogow
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class ModulePack implements ShellInterface
{
	/**
	 * Obiekt Shell
	 *
	 * @ignore
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
			'pack',
			'unpack'
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
		return '1.00 2011-09-12 - <krzotr@gmail.com>';
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
Pakowanie / rozpakowywanie plików oraz katalogów

	Użycie:
		pack input output
		pack -d input output
		unpack input output

	Przykład:
		pack /tmp/ /tmp/output.nfp
		pack -d /tmp/output.nfp /tmp/
		unpack /tmp/output.nfp /tmp/
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
		 * Unpack
		 */
		if( ( $this -> oShell -> sCmd === 'unpack' ) || in_array( 'd', $this -> oShell -> aOptv ) )
		{
			try
			{
				$oUnpack = new Unpack();
				$oUnpack
					-> setInput( $this -> oShell -> aArgv[0] )
					-> setOutput( $this -> oShell -> aArgv[1] )
					-> get();
				return 'Plik został wypakowany';
			}
			catch( PackerException $oException )
			{
				return $oException -> getMessage();
			}
		}

		/**
		 * Pack
		 */
		try
		{
			$oUnpack = new Pack();
			$oUnpack
				-> setInput( $this -> oShell -> aArgv[0] )
				-> setOutput( $this -> oShell -> aArgv[1] )
				-> get();
			return 'Katalog został spakowany';
		}
		catch( PackerException $oException )
		{
			return $oException -> getMessage();
		}
	}

}