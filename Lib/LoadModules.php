<?php

$oDirectory = new DirectoryIterator( 'Modules' );

foreach( $oDirectory as $oFile )
{
	if( is_file( $sFile = $oFile -> getPathname() ) )
	{
		require_once $sFile;
	}
}