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
 * Wczytywanie modulow, poprawka dla UnitTestow
 *
 * @package    Neapter
 * @subpackage Core
 */
class LoadModules
{
    public function __construct()
    {
        /* @todo, Why not Recirsive Directory Iterator and .php files? */

        $aDirectories = array(
            'Modules',
            'Modules/Basic',
            'Modules/Trash',
        );

        foreach ($aDirectories as $sDir) {
            $oDirectory = new DirectoryIterator(__DIR__ . '/../' . $sDir);

            foreach ($oDirectory as $oFile) {
                if ($oFile->isFile()) {
                    require_once $oFile->getPathname();
                }
            }
        }
    }

}

new LoadModules();
