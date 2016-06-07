<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once dirname(__FILE__) . '/../Lib/Arr.php';
require_once dirname(__FILE__) . '/../Lib/Request.php';
require_once dirname(__FILE__) . '/../Lib/ModuleAbstract.php';
require_once dirname(__FILE__) . '/../Lib/XRecursiveDirectoryIterator.php';
require_once dirname(__FILE__) . '/../Lib/LoadModules.php';
require_once dirname(__FILE__) . '/../Lib/Shell.php';

/* Enable debug mode in shell */
$_SERVER['dev'] = 1;
