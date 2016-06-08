<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
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
     * @static
     * @access public
     * @return void
     */
    public static function init()
    {
        if (get_magic_quotes_gpc()) {
            self::stripSlashes($_GET);
            self::stripSlashes($_POST);
            self::stripSlashes($_FILES);
            self::stripSlashes($_SERVER);
        }

        self::$aGet = $_GET;
        self::$aPost = $_POST;
        self::$aFiles = $_FILES;
        self::$aServer = $_SERVER;
    }

    /**
     * Usuwanie backslashow z tablicy
     *
     * @access private
     * @param  array $aData Tablica
     * @return void
     */
    private static function stripSlashes(array &$mData)
    {
        array_walk_recursive($mData, create_function('&$sData',
                '$sData = stripslashes( $sData ); '
            )
        );
    }

    /**
     * Pobieranie aktualnego adresu
     *
     * @static
     * @access public
     *
     * @return string Adres aktualnej strony
     */
    public static function getCurrentUrl()
    {
        if (PHP_SAPI === 'cli') {
            return null;
        }

        return sprintf('http%s://%s%s%s',
            ((strncasecmp(self::getServer('HTTPS'), 'on', 2) === 0) ? 's' : NULL),
            self::getServer('HTTP_HOST'), self::getServer('SCRIPT_NAME'),
            ((($sQuery = self::getServer('QUERY_STRING')) === '') ? '' : '?' . $sQuery)
        );
    }

    /**
     * Pobieranie klucza ze $_GET
     *
     * @static
     * @access public
     * @param  string $sName Nazwa klucza
     * @return mixed
     */
    public static function getGet($sName)
    {
        return Arr::get($sName, self::$aGet);
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
    public static function getPost($sName)
    {
        return Arr::get($sName, self::$aPost);
    }

    /**
     * Pobieranie klucza ze $_FILES
     *
     * @static
     * @access public
     * @param  string $sName Nazwa klucza
     * @return mixed
     */
    public static function getFiles($sName = NULL)
    {
        return Arr::get($sName, self::$aFiles);
    }

    /**
     * Pobieranie klucza ze $_SERVER
     *
     * @static
     * @access public
     * @param  string $sName Nazwa klucza
     * @return string|boolean
     */
    public static function getServer($sName)
    {
        if (isset(self::$aServer[$sName])) {
            return self::$aServer[$sName];
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
     * Request sent via Ajax?
     *
     * @static
     * @access public
     * @return boolean
     */
    public static function isAjax()
    {
        return (strncasecmp(self::getServer('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest', 14) === 0);
    }

}
