<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

$oDirectory = new DirectoryIterator( 'Modules' );

/**
 * Wczytywanie wszystkich modułów z katalogu Modules
 */
foreach( $oDirectory as $oFile )
{
	if( is_file( $sFile = $oFile -> getPathname() ) )
	{
		require_once $sFile;
	}
}