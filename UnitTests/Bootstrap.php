<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname( __FILE__ ) . '/../Lib/Arr.php';
require_once dirname( __FILE__ ) . '/../Lib/Request.php';
require_once dirname( __FILE__ ) . '/../Lib/ModuleAbstract.php';
require_once dirname( __FILE__ ) . '/../Lib/Shell.php';
require_once dirname( __FILE__ ) . '/../Lib/XRecursiveDirectoryIterator.php';

function __autoload( $sClass )
{
	if( is_file( $sFile = dirname( __FILE__ ) . '/../Lib/Modules/' . $sClass . '.php' ) )
	{
		require_once $sFile;
		return TRUE;
	}

	if( is_file( $sFile = dirname( __FILE__ ) . '/../Lib/Modules/Trash/' . $sClass . '.php' ) )
	{
		require_once $sFile;
		return TRUE;
	}

	return FALSE;
}