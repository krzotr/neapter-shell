<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class XRecursiveDirectoryIteratorTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $oDirs = new RecursiveIteratorIterator(
            new XRecursiveDirectoryIterator('/etc')
        );

        foreach ($oDirs as $oDir) {

        }
    }
}
