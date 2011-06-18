<?php

/**
 * ModuleMv - Przenoszenie pliku / katalogu
 */
class ModuleMv implements ShellInterface
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
		return array
		(
			'mv',
			'move',
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
Przenoszenie pliku

	Użycie:
		mv plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
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
		if( $this -> oShell -> iArgc !== 2 )
		{
			return $this -> oShell -> getHelp();
		}

		$sOutput = NULL;

		if( ! rename( $this -> oShell -> aArgv[0], $this -> oShell -> aArgv[1] ) )
		{
			return sprintf( 'Plik "%s" <span class="red">nie został przeniesiony</span> do "%s"', $this -> oShell -> aArgv[0], $this -> oShell -> aArgv[1] );
		}

		return sprintf( 'Plik "%s" <span class="green">został przeniesiony</span> do "%s"', $this -> oShell -> aArgv[0], $this -> oShell -> aArgv[1] );
	}

}