<?php

/**
 * ModuleDummy - Szkielet modulu
 */
class ModuleDummy implements ShellInterface
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
		/**
		 * 'dummy' - 'dummy2' oraz 'dummy3' sa aliasami
		 *
		 * array
		 * (
		 *      'dummy',
		 *      'dummy2',
		 *      'dummy3'
		 * )
		 */
		return array();
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
Opis Polecenia

	Dłuższy Opis Polecenia

	Użycie:
		nazwa_polecenia parametr0 parametr1

	Przykład:
		nazwa_polecenia http://www.wp.pl
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
		 * $oShell -> bSafeMode - SafeMode
		 *   TRUE jezeli wlaczone
		 *
		 * $oShell -> bWindows  - Czy dzialamy na systemie Windows ?
		 *   TRUE jezeli dzialamy na windowsie
		 *
		 * $oShell -> sCmd      - Komenda - (:test param1 param2)
		 *   test
		 *
		 * $oShell -> aArgv     - Argumenty (:test param1 param2)
		 *   [0] => param1
		 *   [1] => param2
		 *
		 * $oShell -> iArgc     - Ilosc parametrow (:test param1 param2)
		 *   2
		 *
		 * $oShell -> aOptv     - Opcje (:test -ab -c param1 param2)
		 *   [0] => a
		 *   [1] => b
		 *   [2] => c
		 *
		 * $oSHell -> sArgv     - Caly ciag parametrow (:test -ab -c param1 param2)
		 *   -ab -c param1 param2
		 */
	}

}