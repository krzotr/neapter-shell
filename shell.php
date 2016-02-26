<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once dirname(__FILE__) . '/Lib/Shell.php';

/**
 * Wylaczanie wszystkich bufferow
 */
for ($i = 0; $i < ob_get_level(); ++$i) {
    ob_end_clean();
}

$oShell = new Shell();
$oShell->get();
