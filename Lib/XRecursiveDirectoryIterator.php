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
 * Ponizsza modyfikacja nie rzuca wyjatku UnexpectedValueException
 *
 * @ignore
 *
 * @link http://www.php.net/manual/en/class.recursivedirectoryiterator.php#101654
 */
class XRecursiveDirectoryIterator extends RecursiveDirectoryIterator
{
    public function getChildren()
    {
        try {
            return parent::getChildren();
        } catch (UnexpectedValueException $oException) {
            return new RecursiveArrayIterator(array());
        }
    }

}