<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

$sData = '<?php ';

if( ! isset( $argv[1] ) || ( isset( $argv[1] ) && ( $argv[1] === 'lite' ) ) )
{
	$aFiles = array( 'Lib/Arr', 'Lib/Request', 'Lib/ShellInterface', 'Lib/XRecursiveDirectoryIterator' );

	if( ! isset( $argv[1] ) )
	{
		$oDirectory = new DirectoryIterator( __DIR__ . '/Modules' );

		foreach( $oDirectory as $oFile )
		{
			if( $oFile -> isFile() && ( $oFile -> getFilename() !== 'Dummy.php' ) )
			{
				$aFiles[] = 'Modules/' . basename( $oFile -> getPathname(), '.php' );
			}
		}
	}
	else
	{
		//$aFiles[] = 'Modules/Eval';
	}

	$aFiles[] = 'shell';

	print_r( $aFiles );

	foreach( $aFiles as $sFile )
	{
		if( $sFile === 'shell' )
		{
			/**
			 * Style
			 */
			$sShellRawData = file_get_contents( $sFile . '.php', NULL, NULL, 6 );

			if( ! preg_match( '~\$this -> sStyleSheet = file_get_contents\( \'(.+?)\' \);~', $sShellRawData, $aMatch ) )
			{
				echo "Cos nie tak ze stylami\r\n";
				exit ;
			}

			$sShellData = preg_replace( '~\$this -> sStyleSheet = file_get_contents\( \'(.+?)\' \);~', NULL,
				file_get_contents( $sFile . '.php', NULL, NULL, 6 )
			);


			if( ! is_file( $aMatch[1] ) )
			{
				echo "Plik ze stylami nie istnieje\r\n";
				exit ;
			}

			$sShellData = preg_replace( '~private \$StyleSheet;~', '', $sShellData );
			$sShellData = preg_replace( '~{\$this -> sStyleSheet}~',
				preg_replace( '~[\r\n\t]+~', NULL, file_get_contents( $aMatch[1] ) ),
				$sShellData
			);

			$sData .= $sShellData;
		}
		else
		{
			$sData .= file_get_contents( $sFile . '.php', NULL, NULL, 6 );
		}
	}

	$sData = preg_replace( '~^require_once.+?[\r\n]+~m', NULL, $sData ) . "?>";
}
else if( isset( $argv[1] ) && ( $argv[1] === 'modules' ) )
{
	$oDirectory = new DirectoryIterator( 'Modules' );

	foreach( $oDirectory as $oFile )
	{
		if( is_file( $sFile = $oFile -> getPathname() ) && ( $oFile -> getFilename() !== 'Dummy.php' ) )
		{
			$sData .= file_get_contents( $sFile, NULL, NULL, 6 );
			echo $oFile -> getBasename() . "\r\n";
		}
	}
}

/**
 * JavaScript
 */
if( isset( $aFiles ) && in_array( 'shell', $aFiles ) )
{
	$sData = preg_replace( '~\$sScript\s*=\s*file_get_contents\(\s*\'Lib/js.js\'\s*\);~', '$sScript=\'' . addcslashes( file_get_contents( 'LibProd/js.js' ), '\'' ) . '\';', $sData );
}

file_put_contents( __DIR__ . '/Tmp/dev.php', $sData );

if( substr( $sData, -2 ) !== '?>' )
{
	$sData .= '?>';
}


/**
 * Usuwanie bialych znakow itp
 * =================================================================================================
 */
$aTokens = token_get_all( $sData ) ;

$sOutput = NULL;

$aExclude = array();

$aInclude = array
(
	'return',
	'include',
	'include_once',
	'require_once',
	'require',
	'class',
	'private',
	'public',
	'protected',
	'interface',
	'final',
	'abstract',
	'const',
	'static',
	'function',
	'throw',
	'new'
);

$aReplace = array
(
	"\n" => '\n',
	"\r" => '\r',
	"\t" => '\t',
);

foreach( $aTokens as $i => $aToken )
{
	if( in_array( $i, $aExclude ) )
	{
		continue;
	}
	if( ! is_int( $aToken[0] ) )
	{
		$sOutput .= $aToken[0];
		continue ;
	}

	switch( $aToken[0] )
	{
		case T_DOC_COMMENT:
			$sOutput .= '';
			break;
		case T_WHITESPACE:
			$sOutput .= '';
			break;
		case T_START_HEREDOC:
			$sOutput .= '"'.strtr( addcslashes( $aTokens[ $i + 1 ][1], '$"'), $aReplace ) . '"';
			$aExclude[] = $i + 1;
			$aExclude[] = $i + 2;
			break;
		default:
			if( trim( strtolower( $aToken[1] ) ) === 'as' )
			{
				$sOutput .= ' as ';
				break;
			}

			if( trim( strtolower( $aToken[1] ) ) === 'implements' )
			{
				$sOutput .= ' implements ';
				break;
			}

			if( trim( strtolower( $aToken[1] ) ) === 'instanceof' )
			{
				$sOutput .= ' instanceof ';
				break;
			}

			if( trim( strtolower( $aToken[1] ) ) === 'extends' )
			{
				$sOutput .= ' extends ';
				break;
			}

			if( in_array( trim( strtolower( $aToken[1] ) ), $aInclude ) )
			{
				$sOutput .= $aToken[1] . ' ';
			}
			else
			{
				$sOutput .= $aToken[1];
			}
	}
}

$sData = trim( $sOutput );

/**
 * =================================================================================================
 */


file_put_contents( __DIR__ . '/Tmp/prod.php', $sData );

$sData = '?>' . $sData . '<?';

$sFile = __DIR__ . '/Tmp/' . ( ( isset( $argv[1] ) && ( $argv[1] === 'modules' ) ) ? 'modules.txt' : 'final.php'  );

file_put_contents( $sFile, sprintf( '<?php $_=%s; eval(gzuncompress($_));?>', var_export( gzcompress( $sData, 9 ), 1 ) ) );