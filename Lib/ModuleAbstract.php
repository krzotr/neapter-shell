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
     * Zwracania aliasow dla komendy
     *
     * @abstract
     *
     * @access public
     * @return array
     */
    public static function getCommands();

    /**
     * Zwracanie wersji modulu
     *
     * @abstract
     *
     * @access public
     * @return string
     */
    public static function getVersion();

    /**
     * Zwracanie pomocy modulu
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
     * Obiekt Shell
     *
     * @access protected
     * @var    object
     */
    protected $oShell;

    /**
     * Obiekt Utils
     *
     * @access protected
     * @var    object
     */
    protected $oUtils;

    protected $oArgs;

    /**
     * Konstruktor
     *
     * @access public
     * @param  object $oShell Obiekt Shell
     * @return void
     */
    public function __construct(Shell $oShell, Utils $oUtils = NULL, Args $oArgs = NULL)
    {
        $this->oShell = $oShell;
        $this->oUtils = ($oUtils === NULL ? new Utils() : $oUtils);

        $this->oArgs = ($oArgs === NULL ? $this->oShell->getArgs() : $oArgs);
    }

    public function setArgs($sArgs)
    {
        $this->oArgs = new Args(preg_replace('~^:[^ ]+\s+~', NULL, $sArgs));
    }

    /**
     * Wywolanie modulu
     *
     * @abstract
     *
     * @access public
     * @return string
     */
    abstract public function get();

}
