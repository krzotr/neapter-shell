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
 * interface - ShellInterface interface dla shella
 *
 * @package    NeapterShell
 */
abstract class ModuleAbstract
{
	/**
	 * Obiekt Shell
	 *
	 * @access protected
	 * @var    object
	 */
	protected $oShell;

	/**
	 * Obiekt Args, skrocony zapis $oShell -> getArgs()
	 *
	 * @access protected
	 * @var    object
	 */
	protected $oArgs;

	/**
	 * Konstruktor
	 *
	 * @access public
	 * @param  object $oShell Obiekt Shell
	 * @return void
	 */
	public function __construct( Shell $oShell )
	{
		$this -> oShell = $oShell;
		$this -> oArgs = $this -> oShell -> getArgs();
	}

	/**
	 * Zwracania aliasow dla komendy
	 *
	 * @abstract
	 *
	 * @access public
	 * @return array
	 */
	abstract public function getCommands();

	/**
	 * Zwracanie wersji modulu
	 *
	 * @abstract
	 *
	 * @access public
	 * @return string
	 */
	abstract public function getVersion();

	/**
	 * Zwracanie pomocy modulu
	 *
	 * @abstract
	 *
	 * @access public
	 * @return string
	 */
	abstract public function getHelp();

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