<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

$sIp = $argv[1];

$sPort = $argv[2];

if( ip2long( $sIp ) === FALSE )
{
	die( "Błędny adres IP" );
}

$aRange = array();

$aPorts = explode( ',', $sPort );

foreach( $aPorts as $sPort )
{
	if( strpos( $sPort, '-' ) !== FALSE )
	{
		list( $iMin, $iMax ) = explode( '-', $sPort );

		if( ! (    ctype_digit( $iMin ) && ctype_digit( $iMax )
			&& ( $iMin > 0 ) && ( $iMax < 65536 )
			&& ( $iMin < $iMax )
		      )
		)
		{
			die( 'Podano błędny zakres' );
		}

		$aRange = array_merge( $aRange, range( (int) $iMin, (int) $iMax ) );
	}
	else if( ctype_digit( $sPort ) && ( $sPort > 0 ) && ( $sPort < 65536 ) )
	{
		$aRange[] = (int) $sPort;
	}
	else
	{
		die( 'Podano błędny zakres' );
	}
}

$aRange = array_unique( $aRange );
sort( $aRange );


printf( "Skanowanie %s:\r\n\r\n", $sIp );

foreach( $aRange as $iPort )
{
	if( ( $rSock = @ fsockopen( 'tcp://' . $sIp, $iPort, $iErrorno = 0, $sErrorstr = NULL, 5 ) ) !== FALSE )
	{
		fwrite( $rSock, str_repeat( 'x', 1024 ) . "\r\n\r\n\r\n" );
		$sBanner = preg_replace( '~[^[:print:]]~', ' ', fread( $rSock, 200 ) );
		printf( "%5d - Otwarty - (%s)\r\n", $iPort, $sBanner );
	}
}