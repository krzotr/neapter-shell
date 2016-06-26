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
 * Modification of RecursiveDirectoryIterator, do no throw
 * UnexpectedValueException exception
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 * @link    http://www.php.net/manual/en/class.recursivedirectoryiterator.php#101654
 */
class XRecursiveDirectoryIterator extends RecursiveDirectoryIterator
{
    /**
     * Returns an iterator for the current entry if it is a directory
     *
     * @return mixed
     */
    public function getChildren()
    {
        try {
            return parent::getChildren();
        } catch (UnexpectedValueException $oException) {
            return new RecursiveArrayIterator(array());
        }
    }
}
