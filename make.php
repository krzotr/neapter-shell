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
 * Wymagane PHP 5.3
 */
$sData = "<?php\r\n";

if( ! isset( $argv[1] ) || ( isset( $argv[1] ) && ( $argv[1] === 'lite' ) ) )
{
	$aFiles = array( 'LibProd/Arr', 'LibProd/Request', 'LibProd/ShellInterface' );

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
		}
	}
}

file_put_contents( __DIR__ . '/Tmp/dev.php', $sData );

/**
 * Wyrazenie " ! " -> "!"
 */
$sData = preg_replace( '~\s\!\s~', '!', $sData );

/**
 * Wyrazenie " && " -> "&&"
 */
$sData = preg_replace( '~\s\&&\s~', '&&', $sData );
/**
 * Wyrazenie " || " -> "||"
 */
$sData = preg_replace( '~\s\\|\|\s~', '||', $sData );

/**
 * Operator trojskladnikowy
 */
$sData = preg_replace( '~\s+\?\s~', '?', $sData );
$sData = preg_replace( '~\s+\:\s~', ':', $sData );

/**
 * Usuwanie komentarzy
 */
$sData = preg_replace( '~/\*\*(.+?)\*/~s', NULL, $sData );

/**
 * Usuwanie tabualtorow
 */
$sData = preg_replace_callback( '~(<<<DATA(.+?)DATA;|\t)~s', function( $aVal )
	{
		if( $aVal[0] !== "\t" )
		{
			return $aVal[0];
		}

	}
	, $sData
);

/**
 * Redukcja \r\n
 */
$sData = preg_replace( '~[\r\n]{2,}~', "\r\n", $sData );

/**
 * Redukcja spacji
 */
$sData = preg_replace( '~ {2,}~', "\r\n", $sData );

/**
 * Tablica $mConfig[ $sParam ] -> $mConfig[$sParam]
 */
$sData = preg_replace( '~\[\s+(.+?)\s+\]~s', '[$1]', $sData );

/**
 * Tablica - przypisanie  'key' => 5 -> 'key'=>5
 */
$sData = preg_replace( '~\s+=>\s+~', '=>', $sData );

/**
 * Obiekty $this -> prop   $this->prop
 */
$sData = preg_replace( '~\s+->\s+~', '->', $sData );

/**
 * break ;, exit ;, continue ; -> break;, exit;, continue
 */
$sData = preg_replace( '~(break|continue|exit) ;~', '$1;', $sData );

/**
 * private $a;
 * private $b;
 *  ->
 * private $a;private $b;
 */
$sData = preg_replace( '~[\r\n]+(private|public|protected)\s+\$~', '$1 $', $sData );
$sData = preg_replace( '~[\r\n]+(private|public|protected)\s+static\s+\$~', '$1 static $', $sData );

/**
 * Zmienne $a = 5; -> $a=5;
 */
$sData = preg_replace( '~(\$(this->)?[a-zA-z0-9_]+)\s+=\s+~', '$1=', $sData );

/**
 * Wyrażenie (musi byc 2 razy
 * if( isset( $mConfig[$sParam] ) ) -> if(isset($mConfig[$sParam]))
 */
$sData = preg_replace( '~\(\s+?(.+?)\s+\)~s', '($1)', $sData );
$sData = preg_replace( '~\(\s+(.+?)\s+?\)~s', '($1)', $sData );
$sData = preg_replace( '~\(\s+?(.+?)\s+\)~s', '($1)', $sData );
$sData = preg_replace( '~\(\s+(.+?)\s+?\)~s', '($1)', $sData );
$sData = preg_replace( '~\(\s+(.+?)\s+\)~s', '($1)', $sData );

/**
 * Wyrażenie if( $a === 5 ) -> if( $a===5 )
 */
$sData = preg_replace( '~\s+(!|=)=?=\s+~', '$1==', $sData );

/**
 * Kontatenacja $s = 'test' . $var . "test2"; -> $s = 'test'.$var."test2";
 */
$sData = preg_replace( '~(\'|")\s+\.\s+~', '$1.', $sData );
$sData = preg_replace( '~\s+\.\s+(\'|")~', '.$1', $sData );

/**
 * Usuwanie znakow nowej lini przed i za znakami '{' '}'
 */
$sData = preg_replace( '~[\r\n]+{[\r\n]+~', '{', $sData );

/**
 * else if -> elseif
 */
$sData = str_replace( 'else if', 'elseif', $sData );

/**
 * else if -> elseif
 */
$sData = str_replace( 'TRUE', '1', $sData );
$sData = str_replace( 'FALSE', '0', $sData );

/**
 * Usuwanie linii
 */
$sData = preg_replace( '~(_GET|_POST|_SERVER|_FILES|null|true);[\r\n]+~i', '$1;', $sData );

$sData = preg_replace( '~\';[\r\n+]~', '\';', $sData );


$sData = preg_replace( '~\r\n~', "\n", $sData );


$sData = preg_replace( '~\}\n\}\n\}\n?~', '}}}', $sData );
$sData = preg_replace( '~}\n\}\n?~', '}}', $sData );
$sData = preg_replace( '~}\n?~', '}', $sData );

$sData = preg_replace( '~(?<!\nDATA);\n(<!DATA;)~', ';', $sData );

if( substr( $sData, -2 ) !== '?>' )
{
	$sData .= '?>';
}


/**
 * JavaScript
 */
if( isset( $aFiles ) && in_array( 'shell', $aFiles ) )
{
	$sData = preg_replace( '~\$sScript\s*=\s*file_get_contents\(\s*\'Lib/jQuery.js\'\s*\);~', '$sScript=\'' . addcslashes( file_get_contents( 'LibProd/jQuery.js' ), '\'' ) . '\';', $sData );
}

file_put_contents( __DIR__ . '/Tmp/prod.php', $sData );

$sData = '?>' . $sData . '<?';

for( $i = 0; $i < 1; $i++ )
{
	$sData = sprintf( "eval(gzuncompress(base64_decode('%s')));", base64_encode( gzcompress( $sData, 9 ) ) );
}


$sFile = __DIR__ . '/Tmp/' . ( ( isset( $argv[1] ) && ( $argv[1] === 'modules' ) ) ? 'modules.txt' : 'final.php'  );

file_put_contents( $sFile, sprintf( "<?php eval(gzuncompress(base64_decode('%s')));?>", base64_encode( gzcompress( $sData, 9 ) ) ) );
