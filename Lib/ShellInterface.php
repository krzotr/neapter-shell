<?php

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