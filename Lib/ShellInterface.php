<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * interface - ShellInterface interface dla shella
 */
interface ShellInterface
{
	/**
	 * Konstruktor
	 *
	 * @access public
	 * @param  object $oShell Obiekt Shell
	 * @return void
	 */
	public function __construct( Shell $oShell );

	/**
	 * Zwracania aliasow dla komendy
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands();

	/**
	 * Zwracanie wersji modulu
	 *
	 * @access public
	 * @return string
	 */
	public function getVersion();

	/**
	 * Zwracanie pomocy modulu
	 *
	 * @access public
	 * @return string
	 */
	public function getHelp();

	/**
	 * Wywolanie modulu
	 *
	 * @access public
	 * @return string
	 */
	public function get();

}