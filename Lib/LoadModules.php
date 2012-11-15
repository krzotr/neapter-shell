<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

$oDirectory = new DirectoryIterator( __DIR__ . '/../Modules' );

/**
 * Wczytywanie wszystkich modułów z katalogu Modules
 */
foreach( $oDirectory as $oFile )
{
	if( $oFile -> isFile() )
	{
		require_once $oFile -> getPathname();
	}
}

$oDirectory = new DirectoryIterator( __DIR__ . '/../Modules/Trash' );

/**
 * Wczytywanie wszystkich modułów z katalogu Modules/Trash
 */
foreach( $oDirectory as $oFile )
{
	if( $oFile -> isFile() )
	{
		require_once $oFile -> getPathname();
	}
}
