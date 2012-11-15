<?php

/**
 * Neapter Framework
 *
 * @version   $Id: Arr.php 466 2011-04-13 15:44:45Z krzotr $
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * class Arr - Przydatne metody do obslugi tablic
 *
 * @package    Neapter
 * @subpackage Core
 */
class Arr
{
	/**
	 * Pobieranie danych z tablicy za pomoca przyjaznego indeksu
	 *
	 * @example index1.index2.index3
	 *
	 * @static
	 * @access public
	 * @param  string $sData Indeks
	 * @param  array         Tablica
	 * @return mixed         Wartosci tablicy
	 */
	public static function get( $sData, array & $aArrayData )
	{
		$mConfig = $aArrayData;

		/**
		 * Rozdzielanie parametrow
		 */
		$aData = explode( '.', $sData );

		foreach( $aData as $sParam )
		{
			if( ! isset( $mConfig[ $sParam ] ) )
			{
				return FALSE;
			}

			$mConfig = $mConfig{$sParam};
		}

		return $mConfig;
	}

}