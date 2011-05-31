<?php

/**
 * Neapter Framework
 *
 * @version   $Id: Request.php 533 2011-05-09 10:26:41Z krzotr $
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * class Request - Informacje o zadaniu
 *
 * @package    Core
 * @subpackage Request
 *
 * @uses       Neapter\Core\Arr
 * @uses       Neapter\Core\Url
 */
class Request
{
	/**
	 * Tablica $_GET
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $aGet = array();

	/**
	 * Tablica $_POST
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $aPost = array();

	/**
	 * Tablica $_SERVER
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $aServer = array();

	/**
	 * Tablica $_FILES
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $aFiles = array();

	/**
	 * Inicjacja
	 *
	 * @uses   Neapter\Core\Config
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function init()
	{
		if( get_magic_quotes_gpc() )
		{
			self::striSlashes( $_GET );
			self::striSlashes( $_POST );
			self::striSlashes( $_FILES );
			self::striSlashes( $_SERVER );
		}

		self::$aGet    = $_GET;
		self::$aPost   = $_POST;
		self::$aFiles  = $_FILES;
		self::$aServer = $_SERVER;
	}

	/**
	 * Usuwanie backslashow z tablicy
	 *
	 * @access private
	 * @param  array   $aData Tablica
	 * @return void
	 */
	private static function striSlashes( array & $mData )
	{
		array_walk_recursive( $mData, create_function( '& $sData',
				'$sData = stripslashes( $sData ); '
			)
		);
	}

	/**
	 * Pobieranie aktualnego adresu
	 *
	 * @uses   Neapter\Core\Url
	 *
	 * @static
	 * @access public
	 *
	 * @return string Adres aktualnej strony
	 */
	public static function getCurrentUrl()
	{
		static $sFullPath;

		$sFullPath = 'http' . ( ( strncasecmp( self::getServer( 'HTTPS' ), 'on', 2 ) === 0 ) ? 's' : NULL ) . '://' . self::getServer( 'HTTP_HOST' ) . self::getServer( 'SCRIPT_NAME' ) ;

		return $sFullPath . ( ( ( $sQuery = self::getServer( 'QUERY_STRING' ) ) === '' ) ? '' : '?' . $sQuery );
	}

	/**
	 * Pobieranie klucza ze $_GET
	 *
	 * @uses   Neapter\Core\Arr
	 *
	 * @static
	 * @access public
	 * @param  string $sName Nazwa klucza
	 * @return mixed
	 */
	public static function getGet( $sName )
	{
		return Arr::get( $sName, self::$aGet );
	}

	/**
	 * Pobieranie tablicy $_GET
	 *
	 * @static
	 * @access public
	 * @return array  Tablica $_GET
	 */
	public static function getGetAll()
	{
		return self::$aGet;
	}

	/**
	 * Pobieranie klucza ze $_POST
	 *
	 * @uses   Arr
	 *
	 * @static
	 * @access public
	 * @param  string $sName Nazwa klucza
	 * @return mixed
	 */
	public static function getPost( $sName )
	{
		return Arr::get( $sName, self::$aPost );
	}

	/**
	 * Pobieranie tablicy $_POST
	 *
	 * @static
	 * @access public
	 * @return array  Tablica $_POST
	 */
	public static function getPostAll()
	{
		return self::$aPost;
	}

	/**
	 * Pobieranie klucza ze $_FILES
	 *
	 * @uses   Neapter\Core\Arr
	 *
	 * @static
	 * @access public
	 * @param  string $sName Nazwa klucza
	 * @return mixed
	 */
	public static function getFiles( $sName = NULL )
	{
		return Arr::get( $sName, self::$aFiles );
	}

	/**
	 * Pobieranie tablicy $_FILES
	 *
	 * @static
	 * @access public
	 * @return array  Tablica $_FILES
	 */
	public static function getFilesAll()
	{
		return self::$aFiles;
	}

	/**
	 * Pobieranie klucza ze $_SERVER
	 *
	 * @static
	 * @access public
	 * @param  string         $sName Nazwa klucza
	 * @return string|boolean
	 */
	public static function getServer( $sName )
	{
		if( isset( self::$aServer[ $sName ] ) )
		{
			return self::$aServer[ $sName ];
		}

		return FALSE;
	}

	/**
	 * Pobieranie $_SERVER
	 *
	 * @static
	 * @access public
	 * @return array  Tablica $_SERVER
	 */
	public static function getServerAll()
	{
		return self::$aServer;
	}

	/**
	 * Pobieranie UserAgenta
	 *
	 * @static
	 * @access public
	 * @return string
	 */
	public static function getUerAgent()
	{
		return self::getServer( 'HTTP_USER_AGENT' );
	}

	/**
	 * Pobieranie adresu IP uzytkownika
	 *
	 * @static
	 * @access public
	 * @return string
	 */
	public static function getUserIp()
	{
		return self::getServer( 'REMOTE_ADDR' );
	}

	/**
	 * Pobieranie refereru
	 *
	 * @static
	 * @access public
	 * @return string
	 */
	public static function getReferer()
	{
		return self::getServer( 'HTTP_REFERER' );
	}

	/**
	 * Czy dane wysylane sa metoda GET
	 *
	 * @static
	 * @access public
	 * @return boolean TRUE jesli dane sa wysylane metoda GET
	 */
	public static function isGet()
	{
		return ( self::getServer( 'REQUEST_METHOD' ) === 'GET' );
	}

	/**
	 * Czy dane wysylane sa metoda POST
	 *
	 * @static
	 * @access public
	 * @return boolean TRUE jesli dane sa wysylane metoda POST
	 */
	public static function isPost()
	{
		return ( self::getServer( 'REQUEST_METHOD' ) === 'POST' );
	}

}