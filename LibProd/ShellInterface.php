<?php
interface ShellInterface{public function __construct(Shell $oShell);public function getCommands();public function getVersion();public function getHelp();public function get();}