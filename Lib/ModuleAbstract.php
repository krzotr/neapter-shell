<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
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
 * interface - ShellInterface interface dla shella
 *
 * @package    NeapterShell
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
     * @access public
     * @param  object $oShell Obiekt Shell
     * @return void
     */
    public function __construct(
        Shell $oShell,
        Utils $oUtils = NULL,
        Args $oArgs = NULL
    ) {
        $this->oShell = $oShell;
        $this->oUtils = ($oUtils === NULL ? new Utils() : $oUtils);
        $this->oArgs = ($oArgs === NULL ? $this->oShell->getArgs() : $oArgs);
    }

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
