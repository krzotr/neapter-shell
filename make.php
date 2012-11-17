<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once __DIR__ . '/Lib/Request.php';
require_once __DIR__ . '/Lib/Args.php';
require_once __DIR__ . '/Lib/ArgsException.php';


/**
 * Wyswietlanie pomocy
 *
 * @ignore
 *
 * @return string
 */
function getHelp()
{
	return <<<HELP
  --help, -h
    plik pomocy

  --type=normal|lite|modules
    normal  - tworzenie shella z wszystkimi modułami
    lite    - tworzenie shella z modułami podstawowymi
    modules - tworzenie pliku z samymi modułami

  --no-js
    kod źródłowy JS nie jest dołączany do shella

  --no-css
    kaskadowy arkusz stylów nie zostanie dołaczony do shella

  --no-extended-version
    data utworzenia shella oraz informacje o dodatkowych opcjach nie są doklejane do numeru wersji

HELP;
}


Request::init();

$oArgs = new Args();

if( $oArgs -> getOption( 'help' ) || $oArgs -> getSwitch( 'h' ) )
{
	die( getHelp() );
}

$sType = $oArgs -> getOption( 'type' );

if( $sType === FALSE )
{
	$sType = 'normal';
}


$sData = '<?php ';
switch( $sType )
{
	case 'lite':
	case 'normal':

		$aFiles = array
		(
			'Lib/Arr',
			'Lib/Request',
			'Lib/ModuleAbstract',
			'Lib/XRecursiveDirectoryIterator'
		);

		if( $sType !== 'lite' )
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

		$aFiles[] = 'shell';

		echo "\r\n\t" . implode( "\r\n\t", $aFiles ) . "\n";

		foreach( $aFiles as $sFile )
		{
			if( $sFile === 'shell' )
			{
				/**
				 * /shell.php
				 */
				$sShellFile = file( $sFile . '.php' );

				$sShellFile[0] = NULL;

				/**
				 * /Lib/Shell.php
				 */
				foreach( $sShellFile as & $sLine )
				{
					if( strncmp( $sLine, 'require_once', 12 ) === 0 )
					{
						$sLine = file_get_contents( dirname( __FILE__ ) . '/Lib/Shell.php', NULL, NULL, 6 );
						break ;
					}
				}
				$sShellFile[] = "\n\nexit;\n";

				$sShellData = implode( '', $sShellFile );

				/**
				 * Wyciaganie Style
				 */
				if( ! preg_match( '~\$this -> sStyleSheet = file_get_contents\( dirname\( __FILE__ \) \. \'(.+?)\' \);~', $sShellData, $aMatch ) )
				{
					echo "Cos nie tak ze stylami\r\n";
					exit ;
				}

				$aMatch[1] = substr( $aMatch[1], 4 );

				if( ! is_file( $aMatch[1] ) )
				{
					echo "Plik ze stylami nie istnieje\r\n";
					exit ;
				}

				/**
				 * Podmienianie styli
				 */
				$sShellData = preg_replace( '~\$this -> sStyleSheet = file_get_contents\((.+?)\);~', NULL, $sShellData );
				$sShellData = preg_replace( '~private \$StyleSheet;~', '', $sShellData );
				$sShellData = preg_replace( '~{\$this -> sStyleSheet}~',
					preg_replace( '~[\r\n\t]+~', NULL, ( $oArgs -> getOption( 'no-css' ) ? '' : file_get_contents( $aMatch[1] ) ) ),
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
		break ;
	case 'modules':
		$oDirectory = new DirectoryIterator( 'Modules' );

		foreach( $oDirectory as $oFile )
		{
			if( is_file( $sFile = $oFile -> getPathname() ) && ( $oFile -> getFilename() !== 'Dummy.php' ) )
			{
				$sData .= file_get_contents( $sFile, NULL, NULL, 6 );
				echo $oFile -> getBasename() . "\r\n";
			}
		}
		break ;
	default:
		die( getHelp() );
}

if( $sType !== 'modules' )
{
	/**
	 * JavaScript
	 */
	if( isset( $aFiles ) && in_array( 'shell', $aFiles ) )
	{
		$sJs = $oArgs -> getOption( 'no-js' ) ? '' :  addcslashes( file_get_contents( 'LibProd/js.js' ), '\'' );
		$sData = preg_replace( '~\$sScript\s*=\s*file_get_contents\(\s*\'Lib/js.js\'\s*\);~', '$sScript=\'' . $sJs . '\';', $sData );
	}

	/**
	 * Doklejanie informacji o wersji
	 */
	if( ! $oArgs -> getOption( 'no-extended-version' ) )
	{
		$sInfo = date('\mYmd') . ( $oArgs -> getOption( 'no-js' ) ? ',no-js' : '' ) . ( $oArgs -> getOption( 'no-css' ) ? ',no-css' : '' );
		$sData = preg_replace( "~const\s+VERSION\s+=\s+'(.+?)';~", "const VERSION = '$1 (" . $sInfo . ")';", $sData );
	}
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

			if( trim( strtolower( $aToken[1] ) ) === '(boolean)' )
			{
				$sOutput .= '(bool)';
				break;
			}

			if( $aToken[1] === 'echo' )
			{
				$sOutput .= 'echo ';
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