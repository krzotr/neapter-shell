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
 * Access to array through index
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class Arr
{
    /**
     * Pobieranie danych z tablicy za pomoca przyjaznego indeksu
     *
     * @example index1.index2.index3
     *
     * @static
     * @access public
     * @param  string $sData       Indeks
     * @param  array  &$aArrayData Tablica
     * @return mixed               Wartosci tablicy
     */
    public static function get($sData, array &$aArrayData)
    {
        $mConfig = $aArrayData;

        /**
         * Rozdzielanie parametrow
         */
        $aData = explode('.', $sData);

        foreach ($aData as $sParam) {
            if (!isset($mConfig[$sParam])) {
                return false;
            }

            $mConfig = $mConfig{$sParam};
        }

        return $mConfig;
    }
}
