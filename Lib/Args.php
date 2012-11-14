<?php

/**
 * Neapter Framework
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * class Args - Parsowanie argumentow
 *
 * @uses       Neapter\Core\Request
 * @uses       Neapter\Core\Exception\ArgsException
 *
 * @package    Core
 * @subpackage Args
 */
class Args
{
	/**
	 * Surowa lista argumentow
	 *
	 * @access private
	 * @var    array
	 */
	private $aArgv = array();

	/**
	 * Przefiltrowana lista argumentow
	 *
	 * @access private
	 * @var    array
	 */
	private $aArgs = array();

	/**
	 * Lista opcji
	 *
	 * @access private
	 * @var    array
	 */
	private $aOptions = array();

	/**
	 * Lista przelacznikow
	 *
	 * @access private
	 * @var    array
	 */
	private $aSwitches = array();

	/**
	 * Konstruktor
	 *
	 * @uses   Neapter\Core\Exception\ArgsException
	 * @uses   Neapter\Core\Request
	 *
	 * @access public
	 * @param  array  $aArgv Tablica z lista parametrow
	 * @return void
	 */
	public function __construct( $mArgv = array() )
	{
		if( is_array( $mArgv ) )
		{
			/**
			 * Surowa lista argumentow
			 */
			$this -> aArgv = ( ( $mArgv === array() ) ? (array) Request::getServer( 'argv' ) : $mArgv );
		}
		else
		{
			if( ! preg_match_all( '~\'(?:(?:\\\')|.+?)\'|"(?:(?:\\")|.+?)"|[^ \r\n\t\'"]+~', $mArgv, $aMatch ) )
			{
				throw new ArgsException( 'Błąd podczas przetwarzania parametrów' );
			}

			$this -> aArgv = array_map( array( $this, 'parseArgv' ), $aMatch[0] );
		}

		/**
		 * Ilosc argumentow
		 */
		if( ( $iArgc = count( $this -> aArgv ) ) === 0 )
		{
			throw new ArgsException( 'Brak argumentów' );
		}

		/**
		 * Przefiltrowana lista argumentow
		 */
		$aArgs = $this -> aArgv;

		/**
		 * Rozdzielanie parametrow
		 */
		for( $i = 1; $i < $iArgc; ++$i )
		{
			/**
			 * Opcje
			 */
			if( strncmp( $aArgs[ $i ], '--', 2 ) === 0 )
			{
				/**
				 * --host=http://neapter.com
				 *
				 * [host] => http://neapter.com
				 */
				if( ( $iPos = strpos( $aArgs[ $i ], '=' ) ) !== FALSE )
				{
					$this -> aOptions[ substr( $aArgs[ $i ], 2, $iPos - 2 ) ] = substr( $aArgs[ $i ], $iPos + 1 );
				}
				else
				{
					$this -> aOptions[ substr( $aArgs[ $i ], 2 ) ] = TRUE;
				}

				unset( $aArgs[ $i ] );
			}
			/**
			 * Switches
			 */
			else if( ( strncmp( $aArgs[ $i ], '-', 1 ) === 0 ) && ( strncmp( $aArgs[ $i ], '--', 2 ) !== 0 ) )
			{
				$aData = str_split( substr( $aArgs[ $i ], 1 ) );
				foreach( $aData as $sSwitcher )
				{
					/**
					 * --vva -c
					 *
					 * [v] => 2
					 * [a] => 1
					 * [c] => 1
					 */
					if( ! isset( $this -> aSwitches[ $sSwitcher ] ) )
					{
						$this -> aSwitches[ $sSwitcher ] = 1;
					}
					else
					{
						$this -> aSwitches[ $sSwitcher ]++;
					}
				}
				unset( $aArgs[ $i ] );
			}
		}

		$this -> aArgs = array_values( $aArgs );
	}

	/**
	 * Pobieranie opcji
	 *
	 * @access public
	 * @param  string         $sOption Nazwa opcji
	 * @return string|boolean
	 */
	public function getOption( $sOption )
	{
		if( isset( $this -> aOptions[ $sOption ] ) )
		{
			return $this -> aOptions[ $sOption ];
		}

		return FALSE;
	}

	/**
	 * Pobieranie wszystkich opcji
	 *
	 * @access public
	 * @return array  Lista opcji
	 */
	public function getOptions()
	{
		return $this -> aOptions;
	}

	/**
	 * Pobieranie przelacznika
	 *
	 * @access public
	 * @param  string          $sOption Nazwa przelacznika
	 * @return integer|boolean
	 */
	public function getSwitch( $sSwitch )
	{
		if( isset( $this -> aSwitches[ $sSwitch ] ) )
		{
			return $this -> aSwitches[ $sSwitch ];
		}

		return FALSE;
	}

	/**
	 * Pobieranie przelacznikow
	 *
	 * @access public
	 * @return array  Lista przelacznikow
	 */
	public function getSwitches()
	{
		return $this -> aSwitches;
	}

	/**
	 * Pobieranie parametru
	 *
	 * @access public
	 * @param  string         $iParam Parametr
	 * @return string|boolean
	 */
	public function getParam( $iParam )
	{
		if( isset( $this -> aArgs[ $iParam ] ) )
		{
			return $this -> aArgs[ $iParam ];
		}

		return FALSE;
	}

	/**
	 * Pobieranie parametrow
	 *
	 * @access public
	 * @param  boolean $bRaw [Optional]<br>Domyslnie <b>TRUE</b> - pobieranie wszystkich parametrow<br>
	 * <b>FALSE</b> - pobieranie parametrow bez opcji i przelacznikow
	 * @return array         Lista parametrow
	 */
	public function getParams( $bRaw = TRUE )
	{
		if( $bRaw )
		{
			return $this -> aArgv;
		}

		return $this -> aArgs;
	}

	/**
	 * Oczyszczanie argumentow ze zbednych znakow
	 *
	 * @access private
	 * @param  string & $sVar Argument
	 * @return void
	 */
	private function parseArgv( $sVar )
	{
		$sVar = strtr( $sVar, array
			(
				'\\\'' => '\'',
				'\\"'  => '"',
				'\\\\' => '\\'
			)
		);

		if(    ( ( substr( $sVar, 0, 1 ) === '"' ) && ( substr( $sVar, -1 ) === '"' ) )
		    || ( ( substr( $sVar, 0, 1 ) === '\'' ) && ( substr( $sVar, -1 ) === '\'' ) )
		)
		{
			return substr( $sVar, 1, -1 );
		}

		return $sVar;
	}

}