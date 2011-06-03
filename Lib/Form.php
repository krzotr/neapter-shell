<?php

/**
 * Neapter Framework
 *
 * @version   $Id: Form.php 568 2011-05-13 08:28:31Z krzotr $
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * class Form - Formularze html
 *
 * @package    Helper
 * @subpackage Form
 *
 * @uses       Text
 * @uses       Request
 */
class Form
{
	/**
	 * Otwieranie formularza
	 *
	 *
	 * @static
	 * @access public
	 * @param  string $sUrl    [Optional]<br>Adres strony
	 * @param  string $sMethod [Optional]<br>Domyslnie <b>post</b> - metoda przesylania danych post / get
	 * @param  array  $aParams [Optional]<br>Tablica parametrow
	 * @return string          Html &lt;form&gt;
	 */
	public static function open( $sUrl = NULL, $sMethod = 'post', array $aParams = array() )
	{
		/**
		 * Jezeli adres to zamieniamy na aktualny url
		 */
		if( $sUrl === NULL )
		{
			$sUrl = Request::getCurrentUrl();
		}

		/**
		 * Method
		 */
		if( strtolower( $sMethod ) !== 'get' )
		{
			$sMethod = 'post';
		}

		/**
		 * Params
		 */
		$sParams = NULL;

		if( $aParams !== array() )
		{
			foreach( $aParams as $sOption => $sValue )
			{
				/**
				 * Czy atrybut jest wyrazem
				 */
				if( ctype_alpha( $sOption ) )
				{
					$sParams .= ' ' . $sOption . '="' . htmlspecialchars( $sValue, ENT_QUOTES ) .'"';
				}
			}
		}

		return '<form action="' . Request::getCurrentUrl( $sUrl, TRUE ) . '" method="' . $sMethod . '"' . $sParams . '>';
	}

	/**
	 * Otwieranie formularza multipart
	 *
	 * @uses   Url
	 *
	 * @static
	 * @access public
	 * @param  sttring $sUrl    [Optional]<br>Adres strony
	 * @param  array   $aParams [Optional]<br>Tablica parametrow
	 * @return string           Html &lt;form&gt;
	 */
	public static function openMultiPart( $sUrl = NULL, array $aParams = array() )
	{
		/**
		 * Jezeli adres to zamieniamy na aktualny url
		 */
		if( $sUrl === NULL )
		{
			$sUrl = Request::getCurrentUrl();
		}

		/**
		 * Params
		 */
		$sParams = NULL;

		if( $aParams !== array() )
		{
			foreach( $aParams as $sOption => $sValue )
			{
				/**
				 * Czy atrybut jest wyrazem
				 */
				if( ctype_alpha( $sOption ) )
				{
					$sParams .= ' ' . $sOption . '="' . htmlspecialchars( $sValue, ENT_QUOTES ) .'"';
				}
			}
		}

		return '<form action="' . Request::getCurrentUrl( $sUrl, TRUE ) . '" enctype="multipart/form-data" method="post"' . $sParams . '>';
	}

	/**
	 * Input
	 *
	 * @uses   Request
	 *
	 * @static
	 * @access protected
	 * @param  string    $sType     Typ pola
	 * @param  string    $sName     Nazwa pola
	 * @param  string    $sValue    Wartosc pola
	 * @param  boolean   $bRemember [Optional]<br>Czy po wyslaniu metoda POST pole ma zawierac przeslane dane
	 * @param  array     $aParams   [Optional]<br>Parametry
	 * @return string               Html &lt;input type=&quot;&quot; /&gt;
	 */
	protected static function input( $sType, $sName, $sValue, $bRemember = TRUE, array $aParams = array() )
	{
		/**
		 * Wartosc
		 */
		if( ( $bRemember ) && ( Request::getPost( $sName ) !== FALSE ) )
		{
			$sValue = (string) Request::getPost( $sName );
		}

		/**
		 * Params
		 */
		$sParams = NULL;

		if( $aParams !== array() )
		{
			foreach( $aParams as $sOption => $sOptionValue )
			{
				/**
				 * Czy atrybut jest wyrazem
				 */
				if( ctype_alpha( $sOption ) )
				{
					$sParams .= ' ' . $sOption . '="' . htmlspecialchars( $sOptionValue, ENT_QUOTES ) .'"';
				}
			}
		}

		return '<input type="' . $sType . '" name="' . htmlspecialchars( $sName, ENT_QUOTES ) . '" value="' . htmlspecialchars( $sValue, ENT_QUOTES ) . '"' . $sParams . ' />';
	}

	/**
	 * Input type text
	 *
	 * @static
	 * @access public
	 * @param  string  $sName     Nazwa pola
	 * @param  string  $sValue    [Optional]<br>Wartosc pola
	 * @param  boolean $bRemember [Optional]<br>Czy po wyslaniu metoda POST pole ma zawierac przeslane dane
	 * @param  array   $aParams   [Optional]<br>Tablica parametrow
	 * @return string             Html &lt;input type=&quot;text&quot; /&gt;
	 */
	public static function inputText( $sName, $sValue = NULL, $bRemember = TRUE, array $aParams = array() )
	{
		return self::input( 'text', $sName, $sValue, $bRemember, $aParams );
	}

	/**
	 * Input type hidden
	 *
	 * @static
	 * @access public
	 * @param  string  $sName     Nazwa pola
	 * @param  string  $sValue    [Optional]<br>Wartosc pola
	 * @param  array   $aParams   [Optional]<br>Tablica parametrow
	 * @return string             Html &lt;input type=&quot;hidden&quot; /&gt;
	 */
	public static function inputHidden( $sName, $sValue = NULL, array $aParams = array() )
	{
		return self::input( 'hidden', $sName, $sValue, FALSE, $aParams );
	}

	/**
	 * Input type file
	 *
	 * @static
	 * @access public
	 * @param  string $sName   Nazwa pola
	 * @param  array  $aParams [Optional]<br>Tablica parametrow
	 * @return string          Html &lt;input type=&quot;file&quot; /&gt;
	 */
	public static function inputFile( $sName, array $aParams = array() )
	{
		return self::input( 'file', $sName, NULL, FALSE, $aParams );
	}

	/**
	 * Input type submit
	 *
	 * @static
	 * @access public
	 * @param  string $sName   Nazwa pola
	 * @param  string $sValue  Wartosc pola
	 * @param  array  $aParams [Optional]<br>Tablica parametrow
	 * @return string          Html &lt;input type=&quot;submit&quot; /&gt;
	 */
	public static function inputSubmit( $sName, $sValue, array $aParams = array() )
	{
		return self::input( 'submit', $sName, $sValue, FALSE, $aParams );
	}

	/**
	 * Textarea
	 *
	 * @uses   Request
	 *
	 * @static
	 * @access public
	 * @param  string  $sName     Nazwa pola
	 * @param  string  $sValue    [Optional]<br>Wartosc
	 * @param  boolean $bRemember [Optional]<br>Czy po wyslaniu metoda POST pole ma zawierac przeslane dane
	 * @param  array   $aParams   [Optional]<br>Tablica parametrow
	 * @return string             Html &lt;textarea&gt;&lt;/textarea&gt;
	 */
	public static function textarea( $sName, $sValue = NULL, $bRemember = TRUE, array $aParams = array() )
	{
		/**
		 * Wartosc
		 */
		if( ( $bRemember ) && ( Request::getPost( $sName ) !== FALSE ) )
		{
			$sValue = (string) Request::getPost( $sName );
		}

		/**
		 * Params
		 */
		$sParams = NULL;

		if( $aParams !== array() )
		{
			foreach( $aParams as $sOption => $sOptionValue )
			{
				/**
				 * Czy atrybut jest wyrazem
				 */
				if( ctype_alpha( $sOption ) )
				{
					$sParams .= ' ' . $sOption . '="' . htmlspecialchars( $sOptionValue, ENT_QUOTES ) .'"';
				}
			}
		}

		return '<textarea name="' . htmlspecialchars( $sName, ENT_QUOTES ) . '"' . $sParams . '>' . htmlspecialchars( $sValue, ENT_QUOTES ) . '</textarea>';
	}

	/**
	 * Zamykanie formularza
	 *
	 * @static
	 * @access public
	 * @return string Html &lt;/form&gt;
	 */
	public static function close()
	{
		return '</form>';
	}

}