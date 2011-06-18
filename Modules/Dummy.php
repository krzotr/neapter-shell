<?php

/**
 * ModuleDummy - Szkielet modulu
 *
 * Klasa musi dzialac na php 5.2.X !!!
 *
 * Jezeli uzyjemy 'eval' to stale __FILE__, __DIR__ itp nie zadzialaja !!!
 * Zamiast __FILE__ uzyj 'Request::getServer( 'SCRIPT_FILENAME' )'
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
		return '1.0 2011-06-04 - <adres_autora>';
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
		 * $this -> oShell -> bSafeMode - SafeMode
		 *   TRUE jezeli wlaczone
		 *
		 * $this -> oShell -> bWindows  - Czy dzialamy na systemie Windows ?
		 *   TRUE jezeli dzialamy na Windowsie
		 *
		 * $this -> oShell -> sCmd      - Komenda - (:test param1 param2)
		 *   test
		 *
		 * $this -> oShell -> aArgv     - Argumenty (:test param1 param2)
		 *   [0] => param1
		 *   [1] => param2
		 *
		 * $this -> oShell -> iArgc     - Ilosc parametrow (:test param1 param2)
		 *   2
		 *
		 * $this -> oShell -> aOptv     - Opcje (:test -ab -c param1 param2)
		 *   [0] => a
		 *   [1] => b
		 *   [2] => c
		 *
		 * $this -> oShell -> sArgv     - Caly ciag parametrow (:test -ab -c param1 param2)
		 *   -ab -c param1 param2
		 */
	}

}