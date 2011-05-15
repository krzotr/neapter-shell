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
	 * Input type password
	 *
	 * @static
	 * @access public
	 * @param  string  $sName     Nazwa pola
	 * @param  string  $sValue    [Optional]<br>Wartosc pola
	 * @param  boolean $bRemember [Optional]<br>Czy po wyslaniu metoda POST pole ma zawierac przeslane dane
	 * @param  array   $aParams   [Optional]<br>Tablica parametrow
	 * @return string             Html &lt;input type=&quot;password&quot; /&gt;
	 */
	public static function inputPassword( $sName, $sValue = NULL, $bRemember = FALSE, array $aParams = array() )
	{
		return self::input( 'password', $sName, $sValue, $bRemember, $aParams );
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
	 * Input type button
	 *
	 * @static
	 * @access public
	 * @param  string $sName   Nazwa pola
	 * @param  string $sValue  Wartosc pola
	 * @param  array  $aParams [Optional]<br>Tablica parametrow
	 * @return string          Html &lt;input type=&quot;submit&quot; /&gt;
	 */
	public static function inputButton( $sName, $sValue, array $aParams = array() )
	{
		return self::input( 'button', $sName, $sValue, FALSE, $aParams );
	}

	/**
	 * Input type radio
	 *
	 * @uses   Request
	 *
	 * @static
	 * @access protected
	 * @param  string    $sName     Nazwa pola
	 * @param  string    $sValue    Wartosc pola
	 * @param  string    $bSelected [Optional]<br>Czy wartosc ma byc zaznaczona
	 * @param  array     $aParams   [Optional]<br>Parametry
	 * @return string               Html &lt;input type=&quot;radio&quot; /&gt;
	 */
	public static function inputRadio( $sName, $sValue, $bSelected = FALSE, array $aParams = array() )
	{
		/**
		 * Wartosc
		 */
		if( Request::isPost() )
		{
			$bSelected = ( Request::getPost( $sName ) == $sValue );
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

		return '<input type="radio" name="' . htmlspecialchars( $sName, ENT_QUOTES ) . '" value="' . htmlspecialchars( $sValue, ENT_QUOTES ) . '"' . ( $bSelected ? ' checked="checked"' : NULL ) . $sParams . ' />';
	}

	/**
	 * Input type checkbox
	 *
	 * @uses   Request
	 *
	 * @static
	 * @access protected
	 * @param  string    $sName     Nazwa pola
	 * @param  string    $sValue    Wartosc pola
	 * @param  string    $bSelected [Optional]<br>Czy wartosc ma byc zaznaczona
	 * @param  array     $aParams   [Optional]<br>Parametry
	 * @return string               Html &lt;input type=&quot;checkbox&quot; /&gt;
	 */
	public static function inputCheckbox( $sName, $sValue, $bSelected = FALSE, array $aParams = array() )
	{
		/**
		 * Wartosc
		 */
		if( Request::isPost() )
		{
			$aData = Request::getPost( $sName );

			if( is_array( $aData ) && in_array( $sValue, $aData ) )
			{
				$bSelected = TRUE;
			}
			else
			{
				$bSelected = FALSE;
			}
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

		return '<input type="checkbox" name="' . htmlspecialchars( $sName, ENT_QUOTES ) . '[]" value="' . htmlspecialchars( $sValue, ENT_QUOTES ) . '"' . ( $bSelected ? ' checked="checked"' : NULL ) . $sParams . ' />';
	}

	/**
	 * Select
	 *
	 * @uses   Request
	 *
	 * @static
	 * @access public
	 * @param  string  $sName     Nazwa pola
	 * @param  array   $aOptions  Opcje wyboru
	 * @param  string  $sSelected [Optional]<br>Zaznaczona wartosc
	 * @param  boolean $bRemember [Optional]<br>Czy po wyslaniu metoda POST pole ma zawierac przeslane dane
	 * @param  array   $aParams   [Optional]<br>Tablica parametrow
	 * @return string             Html &lt;select&gt;&lt;option&gt;&lt;/textarea/option&gt;&lt;select&gt;
	 */
	public static function select( $sName, array $aOptions, $sSelected = NULL, $bRemember = TRUE, array $aParams = array() )
	{
		/**
		 * Wartosc
		 */
		if( ( $bRemember ) && ( Request::getPost( $sName ) !== FALSE ) )
		{
			$sSelected = (string) Request::getPost( $sName );
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

		$sOutput = '<select name="' . htmlspecialchars( $sName, ENT_QUOTES ) . '"' . $sParams . '>';

		/**
		 * Options
		 */
		foreach( $aOptions as $sValue => $sName )
		{
			$sOutput .= '<option value="' . htmlspecialchars( $sValue, ENT_QUOTES ) . '"' . ( $sValue == $sSelected ? ' selected="selected"' : NULL ) . '>' . htmlspecialchars( $sName, ENT_QUOTES ) . '</option>';
		}

		$sOutput .= '</select>';

		return $sOutput;
	}

	/**
	 * Pole wielokrotnego wyboru
	 *
	 * @uses   Request
	 *
	 * @static
	 * @access public
	 * @param  string  $sName     Nazwa pola
	 * @param  array   $aOptions  Opcje wyboru
	 * @param  array   $aSelected [Optional]<br>Wybrane wartosci
	 * @param  boolean $bRemember [Optional]<br>Czy po wyslaniu metoda POST pole ma zawierac przeslane dane
	 * @param  array   $aParams   [Optional]<br>Tablica parametrow
	 * @return string             Html &lt;select&gt;&lt;option&gt;&lt;/textarea/option&gt;&lt;select&gt;
	 */
	public static function multiSelect( $sName, array $aOptions, array $aSelected = array(), $bRemember = TRUE, array $aParams = array() )
	{
		/**
		 * Wartosci
		 */
		if( ( $bRemember ) && ( Request::getPost( $sName ) !== FALSE ) )
		{
			$aSelected = (array) Request::getPost( $sName );
		}

		$aSelected = array_flip( $aSelected );

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

		$sOutput = '<select multiple="multiple" name="' . htmlspecialchars( $sName, ENT_QUOTES ) . '[]"' . $sParams . '>';

		/**
		 * Options
		 */
		foreach( $aOptions as $sValue => $sName )
		{
			$sOutput .= '<option value="' . htmlspecialchars( $sValue, ENT_QUOTES ) . '"' . ( array_key_exists( $sValue, $aSelected ) ? ' selected="selected"' : NULL ) . '>' . htmlspecialchars( $sName, ENT_QUOTES ) . '</option>';
		}

		$sOutput .= '</select>';

		return $sOutput;
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
	 * Zabezpieczenie przed XSRF
	 *
	 * @uses   Session
	 * @uses   Text
	 *
	 * @static
	 * @access public
	 * @param  string $sFormId Nazwa formularza
	 * @return string          Html &lt;input type=&quot;hidden&quot; /&gt;
	 */
	public static function xsrf( $sFormId )
	{
		$sToken = Text::random( 40, 'lI' );

		Session::set( 'xsrf_' . $sFormId, $sToken );

		return self::input( 'hidden', 'xsrf_' . $sFormId, $sToken );
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