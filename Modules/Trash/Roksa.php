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
 * ModuleRoksa - Ogloszenia towarzyskie
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class ModuleRoksa implements ShellInterface
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
		return array( 'roksa' );
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
		return '1.01 2011-06-23 - <krzotr@gmail.com>';
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
Ogłoszenia towarzyskie

	Typ:
		wiek - sortowanie według wieku rosnąco
		cena - sortowanie według ceny rosnąco
		nowe - nowe dziewczęta

	Użycie:
		roska [typ]

	Przykład:
		roksa nowe
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
		if( $this -> oShell -> iArgc > 2 )
		{
			return $this -> getHelp();
		}

		$sData = file_get_contents( 'http://www.roksa.pl/lpl/anonse.php?r=panie&miasto=Krak%C3%B3w' );

		$sPattern = '~<div \s+ id="anonshint_an_(\d+)" \s+ class="anonshint_container"> \s+
				<div \s+ class="tooltip_header">(.+?)</div> \s+
				<div \s+ class="tooltip_content">wiek: \s+ (\d+)<br/>cena: \s+ (\d+)zł \s+
				</div> \s+
			</div>~x';

		preg_match_all( $sPattern, $sData, $aMatch );

		$aWhores = array();
		$aYears  = array();
		$aPrice  = array();

		foreach( $aMatch[1] as $iKey => $iId )
		{
			$aWhores[] = array
			(
				'id'    => $iId,
				'nick'  => $aMatch[2][ $iKey ],
				'year'  => $aMatch[3][ $iKey ],
				'price' => $aMatch[4][ $iKey ],
			);

			$aYears[] = $aMatch[3][ $iKey ];
			$aPrice[] = $aMatch[4][ $iKey ];
		}

		if( isset( $this -> oShell -> aArgv[0] ) )
		{
			if( $this -> oShell -> aArgv[0] === 'wiek' )
			{
				array_multisort( $aYears, $aWhores );
			}
			else if( $this -> oShell -> aArgv[0] === 'cena' )
			{
				array_multisort( $aPrice, $aWhores );
			}
		}

		$aWhores = array_slice( $aWhores, 0, 20 );

		$sOutput = NULL;
		foreach( $aWhores as $aData )
		{
			$sOutput .= sprintf( "http://www.roksa.pl/lpl/anons.php?nr=%-6d Wiek:%d Cena:%4d %-24.24s\r\n", $aData['id'], $aData['year'], $aData['price'], $aData['nick'] );
		}

		return $sOutput;
	}

}