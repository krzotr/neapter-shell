<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class Args
{
    /**
     * Surowa lista argumentow
     *
     * @access protected
     * @var    array
     */
    protected $aArgv = array();

    /**
     * Przefiltrowana lista argumentow
     *
     * @access protected
     * @var    array
     */
    protected $aArgs = array();

    /**
     * Lista opcji
     *
     * @access protected
     * @var    array
     */
    protected $aOptions = array();

    /**
     * Lista przelacznikow
     *
     * @access protected
     * @var    array
     */
    protected $aSwitches = array();

    /**
     * RAW input data
     *
     * @access protected
     * @var    mixed
     */
    protected $mArgv;

    /**
     * Konstruktor
     *
     * @uses   Neapter\Core\Request
     *
     * @access public
     * @param  array $aArgv Tablica z lista parametrow
     * @return void
     */
    public function __construct($mArgv = array())
    {
        if ($mArgv === '') {
            return;
        }

        if (is_array($mArgv)) {
            /**
             * Surowa lista argumentow
             */
            $this->aArgv = (($mArgv === array()) ? (array)Request::getServer('argv') : $mArgv);
        } else {
            if (!preg_match_all('~([^ ]*(\'(?:(?:\\\')|.+?)\'|"(?:(?:\\")|.+?)"))|([^ \r\n"\']+)~', $mArgv, $aMatch)) {
                return;
            }

            $this->aArgv = array_map(array($this, 'parseArgv'), $aMatch[0]);
        }

        /**
         * Przefiltrowana lista argumentow
         */
        $aArgs = $this->aArgv;

        /**
         * Rozdzielanie parametrow
         */
        $iArgc = count($this->aArgv);

        for ($i = 0; $i < $iArgc; ++$i) {
            /**
             * Opcje
             */
            if (strncmp($aArgs[$i], '--', 2) === 0) {
                /**
                 * --host=http://neapter.com
                 *
                 * [host] => http://neapter.com
                 */
                if (($iPos = strpos($aArgs[$i], '=')) !== false) {
                    $this->aOptions[substr($aArgs[$i], 2, $iPos - 2)] = substr($aArgs[$i], $iPos + 1);
                } else {
                    $this->aOptions[substr($aArgs[$i], 2)] = true;
                }

                unset($aArgs[$i]);
            } /**
             * Switches
             */
            else if ((strncmp($aArgs[$i], '-', 1) === 0) && (strncmp($aArgs[$i], '--', 2) !== 0)) {
                foreach (str_split(substr($aArgs[$i], 1)) as $sSwitcher) {
                    /**
                     * --vva -c
                     *
                     * [v] => 2
                     * [a] => 1
                     * [c] => 1
                     */
                    if (!isset($this->aSwitches[$sSwitcher])) {
                        $this->aSwitches[$sSwitcher] = 1;
                    } else {
                        $this->aSwitches[$sSwitcher]++;
                    }
                }
                unset($aArgs[$i]);
            }
        }

        $this->aArgs = array_values($aArgs);

        $this->mArgv = $mArgv;
    }

    /**
     * Pobieranie opcji
     *
     * @access public
     * @param  string $sOption Nazwa opcji
     * @return string|boolean
     */
    public function getOption($sOption)
    {
        if (isset($this->aOptions[$sOption])) {
            return $this->aOptions[$sOption];
        }

        return false;
    }

    /**
     * Pobieranie wszystkich opcji
     *
     * @access public
     * @return array  Lista opcji
     */
    public function getOptions()
    {
        return $this->aOptions;
    }

    /**
     * Ilosc opcji
     *
     * @access public
     * @return integer Ilosc parametrow
     */
    public function getNumberOfOptions()
    {
        return count($this->aOptions);
    }

    /**
     * Pobieranie przelacznika
     *
     * @access public
     * @param  string $sOption Nazwa przelacznika
     * @return integer|boolean
     */
    public function getSwitch($sSwitch)
    {
        if (isset($this->aSwitches[$sSwitch])) {
            return $this->aSwitches[$sSwitch];
        }

        return false;
    }

    /**
     * Pobieranie przelacznikow
     *
     * @access public
     * @return array  Lista przelacznikow
     */
    public function getSwitches()
    {
        return $this->aSwitches;
    }

    /**
     * Ilosc przelacznikow
     *
     * @access public
     * @return integer Ilosc parametrow
     */
    public function getNumberOfSwitches()
    {
        return count($this->aSwitches);
    }

    /**
     * Pobieranie parametru
     *
     * @access public
     * @param  string $iParam Parametr
     * @return string|boolean
     */
    public function getParam($iParam)
    {
        if (isset($this->aArgs[$iParam])) {
            return $this->aArgs[$iParam];
        }

        return false;
    }

    /**
     * Pobieranie parametrow
     *
     * @access public
     * @param  boolean $bRaw [Optional]<br>Domyslnie <b>true</b> - pobieranie wszystkich parametrow<br>
     * <b>false</b> - pobieranie parametrow bez opcji i przelacznikow
     * @return array         Lista parametrow
     */
    public function getParams($bRaw = true)
    {
        if ($bRaw) {
            return $this->aArgv;
        }

        return $this->aArgs;
    }

    /**
     * Ilosc parametrow
     *
     * @access public
     * @return integer Ilosc parametrow
     */
    public function getNumberOfParams()
    {
        return count($this->aArgs);
    }

    public function getRawData()
    {
        return $this->mArgv;
    }

    /**
     * Oczyszczanie argumentow ze zbednych znakow
     *
     * @access protected
     * @param  string & $sVar Argument
     * @return void
     */
    protected function parseArgv($sVar)
    {
        $sVar = strtr($sVar, array
            (
                '\\\'' => '\'',
                '\\"' => '"',
                '\\\\' => '\\'
            )
        );

        if (((substr($sVar, 0, 1) === '"') && (substr($sVar, -1) === '"'))
            || ((substr($sVar, 0, 1) === '\'') && (substr($sVar, -1) === '\''))
        ) {
            return substr($sVar, 1, -1);
        }

        return $sVar;
    }

}
