<?php

/**
 * ProxyException - Proxy wyjatki
 */
class ProxyException extends Exception {}

/**
 * class Proxy - Proxy
 */
class Proxy
{
	/**
	 * Wersja
	 */
	const VERSION = '1.0';

	/**
	 * Numer portu
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $iPort = 0;

	/**
	 * Czy ignolrowac obazki
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bNoImages = FALSE;

	/**
	 * Konstruktor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		/**
		 * Rozszerzenie "sockets" jes wymagane
		 */
		if( ! function_exists( 'socket_create' ) )
		{
			throw new ProxyException( 'Brak rozszerzenia "sockets"' );
		}
	}

	/**
	 * Ustawianie portu
	 *
	 * @access public
	 * @param  integer $iValue Numer portu
	 * @return Proxy           Obiekt Proxy
	 */
	public function setPort( $iValue )
	{
		/**
		 * Sprawdzanie poprawnosci portu
		 */
		if( ( $iValue < 0 ) || ( $iValue > 65535 ) )
		{
			throw new ProxyException( sprintf( 'Błędny port "%d"', $iValue ) );
		}

		$this -> iPort = (int) $iValue;

		return $this;
	}

	/**
	 * Ignorowanie obrazkow
	 *
	 * @access public
	 * @parm   boolean $bValue Ignorowanie obrazkow
	 * @return Proxy           Obiekt Proxy
	 */
	public function setNoImages( $bValue )
	{
		$this -> bNoImages = (boolean) $bValue;

		return $this;
	}

	/**
	 * Uruchamianie proxy
	 *
	 * @access public
	 * @return void
	 */
	public function get()
	{
		if( $this -> iPort === 0 )
		{
			throw new ProxyException( 'Nie podano portu' );
		}

		/**
		 * Tworzenie socketa
		 */
		if( ! ( $rSock = socket_create( AF_INET, SOCK_STREAM, getProtoByName( 'tcp' ) ) ) )
		{
			throw new ProxyException( 'Nie można utworzyć socketa' );
		}

		/**
		 * Podpinanie
		 */
		if( ! socket_bind( $rSock, '0.0.0.0', $this -> iPort ) )
		{
			throw new ProxyException( sprintf( 'Nie można zbindować 0.0.0.0:%d', $this -> iPort ) );
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
			throw new ProxyException( 'Nie można nasłuchiwać' );
		}


		echo "Proxy zostaĹ‚o uruchomione\r\n\r\n";

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

			/**
			 * Komendy
			 */
			if( substr( $sTmp, 0, 1 ) === ':' )
			{
				$sCommand = rtrim( substr( $sTmp, 1 ) );

				switch( $sCommand )
				{
					case 'exit':
						echo "Command -> exit\r\n";
						socket_write( $rClient, "Dobranoc ;)" );

						socket_close( $rClient );

						/*
						 * Rozlaczenie klienta
						 */
						socket_close( $rSock );
						exit ;
					case 'ping':
						echo "Command -> ping\r\n";
						socket_close( $rClient );
						break ;
				}
				continue ;
			}

			/**
			 * zakonczenie proxy, wysterczy w adresie wpisac http://command.exit/
			 */
			if( $aHost[1] === 'command.exit' )
			{
				echo "Command -> exit\r\n";
				socket_write( $rClient, "HTTP/1.1 200 OK\r\n\r\nProxy zakonczylo swoje dzialanie" );
				socket_close( $rClient );
				socket_close( $rSock );
				exit ;
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
			if( $this -> bNoImages && in_array( pathinfo( $aGet[1], PATHINFO_EXTENSION ), array( 'jpg', 'gif', 'png', 'ico', 'psd', 'bmp' ) ) )
			{
				echo "Ignore Image\r\n";
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
					echo "Error\r\n";
					continue ;
				}

				/**
				 * Wysylanie naglowkow
				 */
				if( ! socket_write( $rHost, $sHeaders ) )
				{
					echo "Error\r\n";
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
				printf( "Data: %7dKB Speed: %7.2fKB/s\r\n", ceil( $iTotalLen / 1024 ), ( $iTotalLen / ( microtime( 1 ) - $fSpeed ) / 1024 ) );
			}

			/**
			 * Rozlaczenie z hostem
			 */
			socket_close( $rHost );

			/**
			 * Rozlaczenie klienta
			 */
			socket_close( $rClient );

			@ ob_flush();
			@ flush();
		}

		/**
		 * Zamykanie socketa (chyba a raczej na pewno sie nigdy nie zamknie)
		 */
		socket_close( $rSock );
	}

}

/**
 * =================================================================================================
 */

/**
 * ModuleProxy - Proxy
 */
class ModuleProxy implements ShellInterface
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
		return array( 'proxy' );
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
Proxy HTTP

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
		if( ! class_exists( 'Proxy' ) )
		{
			return 'proxy - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( $this -> oShell -> iArgc !== 1 )
		{
			return $this -> getHelp();
		}

		try
		{
			ob_start();

			header( 'Content-Type: text/plain; charset=utf-8', TRUE );

			$oProxy = new Proxy();
			$oProxy -> setPort( $this -> oShell -> aArgv[0] )
				-> setNoImages( in_array( 'i', $this -> oShell -> aOptv ) )
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

}