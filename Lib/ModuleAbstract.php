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
 * Interface for all modules
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
interface ModuleInterface
{
    /**
     * Get list of all available commands
     *
     * @abstract
     *
     * @return array
     */
    public static function getCommands();

    /**
     * Get version of module
     *
     * @abstract
     *
     * @return string
     */
    public static function getVersion();

    /**
     * Get information about module
     *
     * @abstract
     *
     * @access public
     * @return string
     */
    public static function getHelp();
}

/**
 * Abstract method for all modules
 *
 * @abstract
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
abstract class ModuleAbstract implements ModuleInterface
{
    /**
     * Shell Object
     *
     * @var Shell
     */
    protected $oShell;

    /**
     * Utils Object
     *
     * @var Utils
     */
    protected $oUtils;

    /**
     * Args Object
     *
     * @var Args
     */
    protected $oArgs;

    /**
     * Create new object of module
     *
     * @param Shell $oShell Shell object
     * @param Utils $oUtils Utils object
     * @param Args  $oArgs  Args object
     */
    public function __construct(
        Shell $oShell,
        Utils $oUtils = null,
        Args $oArgs = null
    ) {
        $this->oShell = $oShell;
        $this->oUtils = ($oUtils === null ? new Utils() : $oUtils);
        $this->oArgs = ($oArgs === null ? $this->oShell->getArgs() : $oArgs);
    }

    /**
     * Set command to execute
     *
     * @example
     * <code>
     * :ls -la
     * </code>
     *
     * @param $sArgs Args List of arguments
     * @return void
     */
    public function setArgs($sArgs)
    {
        $this->oArgs = new Args(preg_replace('~^:[^ ]+\s+~', null, $sArgs));
    }

    /**
     * Execute module
     *
     * @abstract
     *
     * @access public
     * @return string
     */
    abstract public function get();
}
