<?php

/**
 * Neapter Framework
 *
 * @version   $Id: Html.php 568 2011-05-13 08:28:31Z krzotr $
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * class Html - Przydatne metody html
 *
 * @package    Helper
 * @subpackage Html
 *
 * @uses       Neapter\Core\Url
 */
class Html
{
	/**
	 * Usuwanie znakow nowej linii, tabulatorow oraz zbednych spacji z dokumentu html
	 *
	 * @static
	 * @access public
	 * @param  string $sData   Tekst
	 * @param  string $bStrict [Optional]<br>Domyslnie: <b>FALSE</b><br>
	 * <b>FALSE</b> - struktura formatowania w tagach tagow &lt;pre&gt; i &lt;textarea&gt; zostanie zachowana
	 * <b>TRUE</b> - zostana usuniete wszedzie znaki \r\n\t oraz spacja
	 * @return string          Tekst bez bialych znakow
	 */
	public static function shrink( $sData, $bStrict = FALSE )
	{
		if( $bStrict )
		{
			return preg_replace( '~([\r\n\t]+|[ ]{2,})~', NULL, $sData );
		}

		return preg_replace_callback( '~((?si)<textarea[^>]*>.+?</textarea>|(?si)<pre[^>]*>.+?</pre>|[\r\n\t]+| {2,})~', 'Html::shrinkPre', $sData );
	}

	/**
	 * Z tagow pre usuwane sa koncowe spacje i znaki \r i \n zamieniane sa na &lt;br&rt;
	 *
	 * @static
	 * @access protected
	 * @param  array     $aMatched Nadmiarowe spacje
	 * @return string              Sformatowany ciag
	 */
	public static function shrinkPre( array $aMatched )
	{
		if( ! isset( $aMatched[1] ) || ( trim( $aMatched[1] ) === '' ) )
		{
			return NULL;
		}

		$sOutput = NULL;

		$sOutput = preg_replace( '~^(.*?)(?:[\t ]+)?([\r\n]+)~m', '$1$2', $aMatched[1] );

		/**
		 * Pre
		 */
		if( strncasecmp( $aMatched[0], '<textarea', 9 ) !== 0 )
		{
			return str_replace( array( "\r\n", "\r", "\n", ), '<br />', $sOutput );
		}

		return $sOutput;
	}

}