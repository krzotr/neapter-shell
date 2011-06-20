<?php

/**
 * ModuleG4m3 - Gra
 */
class ModuleG4m3 implements ShellInterface
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
		return array( 'g4m3' );
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
Gra z komputerem, wspaniała na samotne wieczory ;)

	Użycie:
		g4m3 cyfra_z_przedziału_0-9

		g4m3 cyfra_z_przedziału_0-9 [ilość_losowań]
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
			return $this -> oShell -> getHelp();
		}

		/**
		 * Jesli 'liczba' jest rowna 'x' to komputer sam losuje liczby
		 */
		if( ( $this -> oShell -> aArgv[0] !== 'x' ) && ( ! ctype_digit( $this -> oShell -> aArgv[0] ) || strlen( $this -> oShell -> aArgv[0] ) !== 1 ) )
		{
			return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
		}

		/**
		 * Maksymalnie 1000 losowan
		 */
		if( isset( $this -> oShell -> aArgv[1] ) && ( ! ctype_digit( $this -> oShell -> aArgv[1] ) || ( $this -> oShell -> aArgv[1] > 1000 ) ) )
		{
			return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
		}

		$iLoop = ( isset( $this -> oShell -> aArgv[1] ) ? (int) $this -> oShell -> aArgv[1] : 10 );

		$sOutput = NULL;

		$iWins  = 0;
		$iLoses = 0;

		$iDigit = (int) $this -> oShell -> aArgv[0];

		$i = 0;
		do
		{
			if( $this -> oShell -> aArgv[0] === 'x' )
			{
				$iDigit = mt_rand( 0, 9 );
			}

			if( ( $iNum = mt_rand( 0, 9 ) ) === $iDigit )
			{
				$sOutput .= sprintf( "<span class=\"green\">Wygrałeś</span>   Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\r\n", $iDigit, $iNum );
				++$iWins;
			}
			else
			{
				$sOutput .= sprintf( "<span class=\"red\">Przegrałeś</span> Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\r\n", $iDigit, $iNum );
				++$iLoses;
			}
		}
		while( ++$i < $iLoop );

		return sprintf( "<span class=\"red\">Przegrałeś</span>: <strong>%d</strong>, <span class=\"green\">Wygrałeś</span>: <strong>%d</strong>, Success rate: <strong>%.2f</strong> %%\r\n\r\n%s", $iLoses, $iWins, ( $iWins / $this -> oShell -> aArgv[1] ) * 100, $sOutput );
	}

}