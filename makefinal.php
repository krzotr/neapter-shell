<?php

$sFilePath = __DIR__ . '/s-prod.php';

$sData = "<?php\n";
foreach( array( 'Arr', 'Form', 'Html', 'Request', 's' ) as $sFile )
{
	$sData .= file_get_contents( $sFile . '.php', NULL, NULL, 6 );
}

$sData = preg_replace( '~require_once.+?[\r\n]~', NULL, $sData ) . "?>";


file_put_contents( $sFilePath, $sData );

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
 * Usuwanie komentarzy, tabulatorow
 */
$sData = preg_replace( '~/\*(.+?)\*/|\t~s', NULL, $sData );

/**
 * Redukcja \r\n
 */
$sData = preg_replace( '~[\r\n]{2,}~', "\n", $sData );

/**
 * Redukcja spacji
 */
$sData = preg_replace( '~ {2,}~', "\n", $sData );

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
$sData = preg_replace( '~(?<!CONTENT;)(?<!HELP;)\n\}[\r\n]+~', '}', $sData );


/**
 * Usuwanie linii
 */
$sData = preg_replace( '~(_GET|_POST|_SERVER|_FILES|null|true);[\r\n]+~i', '$1;', $sData );

$sData = preg_replace( '~\';[\r\n+]~i', '\';', $sData );

echo $sData; die;


$sData = '?>' . $sData . '<?php';


for( $i = 0; $i < 2; $i++ )
{
	$sData = sprintf( "\$sData=<<<'CONTENT_DATA%d'\n%s\nCONTENT_DATA%d;\neval(gzuncompress(\$sData));", $i, gzcompress( $sData, 9 ), $i );
}


printf( "<?php \$sData=<<<'CONTENT'\r\n%s\nCONTENT;\neval( gzuncompress( \$sData ) );", gzcompress( $sData, 9 ) );


exit ;







echo '<pre>';
echo htmlspecialchars( $sData );