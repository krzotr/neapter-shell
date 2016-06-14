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
     * Array of $_GET
     *
     * @static
     * @var array
     */
    private static $aGet = array();

    /**
     * Array of $_POST
     *
     * @static
     * @var array
     */
    private static $aPost = array();

    /**
     * Array of $_SERVER
     *
     * @static
     * @var array
     */
    private static $aServer = array();

    /**
     * Array of $_FILES
     *
     * @static
     * @var array
     */
    private static $aFiles = array();

    /**
     * Init
     *
     * @static
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
     * Remove escape charracters
     *
     * @param  array $mData Array
     * @return void
     */
    private static function stripSlashes(array &$mData)
    {
        array_walk_recursive(
            $mData,
            create_function(
                '&$sData',
                '$sData = stripslashes( $sData ); '
            )
        );
    }

    /**
     * Get URL of current resource
     *
     * @static
     * @return string Adres aktualnej strony
     */
    public static function getCurrentUrl()
    {
        if (PHP_SAPI === 'cli') {
            return '';
        }

        return sprintf(
            'http%s://%s%s%s',
            ((strncasecmp(self::getServer('HTTPS'), 'on', 2) === 0) ? 's' : ''),
            self::getServer('HTTP_HOST'),
            self::getServer('SCRIPT_NAME'),
            ((($sQuery = self::getServer('QUERY_STRING')) === '') ? '' : '?' . $sQuery)
        );
    }

    /**
     * Get value in $_GET
     *
     * @uses   Arr
     *
     * @static
     * @param  string $sName Key name
     * @return mixed
     */
    public static function getGet($sName)
    {
        return Arr::get($sName, self::$aGet);
    }

    /**
     * Get value in $_POST
     *
     * @uses   Arr
     *
     * @static
     * @param  string $sName Key name
     * @return mixed
     */
    public static function getPost($sName)
    {
        return Arr::get($sName, self::$aPost);
    }

    /**
     * Get value in $_FILES
     *
     * @uses   Arr
     *
     * @static
     * @param  string $sName Key name
     * @return mixed
     */
    public static function getFiles($sName = null)
    {
        return Arr::get($sName, self::$aFiles);
    }

    /**
     * Get value in $_SERVER
     *
     * @uses   Arr
     *
     * @static
     * @param  string $sName Key name
     * @return string|boolean
     */
    public static function getServer($sName)
    {
        if (isset(self::$aServer[$sName])) {
            return self::$aServer[$sName];
        }

        return false;
    }

    /**
     * Get all values of $_SERVER
     *
     * @uses   Arr
     *
     * @static
     * @return array  Tablica $_SERVER
     */
    public static function getServerAll()
    {
        return self::$aServer;
    }

    /**
     * Is request sent via Ajax?
     *
     * @static
     * @return boolean
     */
    public static function isAjax()
    {
        return strncasecmp(
            self::getServer('HTTP_X_REQUESTED_WITH'),
            'XMLHttpRequest',
            14
        ) === 0;
    }
}
