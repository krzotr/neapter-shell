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
 * ModuleRoksa - Ogloszenia towarzyskie
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    Neapter
 * @subpackage Modules
 */
class ModuleRoksa extends ModuleAbstract
{
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
		return '1.02 2011-11-20 - <krzotr@gmail.com>';
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
Ogłoszenia towarzyskie Roksa.pl - Kraków

	Typ:
		wiek - sortowanie według wieku rosnąco
		cena - sortowanie według ceny rosnąco
		nowe - nowe dziewczęta

	Limit:
		ilość pozycji na stronie 1 - 100

	Użycie:
		roska [typ] [limit]

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

		$iLimit = 20;

		if( isset( $this -> oShell -> aArgv[1] ) && in_array( $this -> oShell -> aArgv[1], range( 1, 100 ) ) )
		{
			$iLimit = (int) $this -> oShell -> aArgv[1];
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

		$aWhores = array_slice( $aWhores, 0, $iLimit );

		$sOutput = NULL;
		foreach( $aWhores as $aData )
		{
			$sOutput .= sprintf( "http://www.roksa.pl/lpl/anons.php?nr=%-6d Wiek:%d Cena:%4d %-24.24s\r\n", $aData['id'], $aData['year'], $aData['price'], $aData['nick'] );
		}

		return $sOutput;
	}

}