<?php

/**
 * ModuleRemove - Usuwanie pliku / katalogu
 */
class ModuleRemove implements ShellInterface
{
	/**
	 * Obiekt Shell
	 *
	 * @access private
	 * @var    object
	 */
	private $oShell;

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
	}

	/**
	 * Dostepna lista komend
	 *
	 * @access public
	 * @return array
	 */
	public function getCommands()
	{
		return array
		(
			'remove',
			'rm',
			'delete',
			'del',
		);
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
		return '1.0 2011-06-04 - <krzotr@gmail.com>';
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
Usuwanie pliku / katalogu. Zawartość katalogu zostanie usunięta rekurencyjnie

	Użycie:
		remove ścieżka_do_katalogu_lub_pliku
DATA;
	}

	/**
	 * Wywolanie modulu
	 *
	 * @access public
	 * @return string
	 */
	public function get()
	{
		/**
		 * Help
		 */
		if( $this -> oShell -> iArgc === 0 )
		{
			return $this -> oShell -> getHelp();
		}

		$sOutput = NULL;

		/**
		 * Jezeli podana sciezka to plik
		 */
		if( is_file( $this -> oShell -> sArgv ) )
		{
			if( ! unlink( $this -> oShell -> sArgv ) )
			{
				return sprintf( 'Plik "%s" <span class="red">nie został usunięty</span>', $this -> oShell -> sArgv );
			}

			return sprintf( 'Plik "%s" <span class="green">został usunięty</span>', $this -> oShell -> sArgv );
		}
		/**
		 * Jezeli podana sciezka to katalog
		 */
		if( is_dir( $this -> sArgv ) )
		{
			try
			{
				$oDirectory = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this -> oShell -> sArgv ), RecursiveIteratorIterator::CHILD_FIRST );

				foreach( $oDirectory as $oFile )
				{
					if( $oFile -> isDir() )
					{
						/**
						 * PHP 5.2.X nie posiada stalej RecursiveDirectoryIterator::SKIP_DOTS
						 */
						if( ( $oFile -> getBasename() === '.' ) || ( $oFile -> getBasename() === '.' ) )
						{
							continue;
						}

						/**
						 * Usuwanie katalogu
						 */
						if( ! rmdir( $oFile -> getPathname() ) )
						{
							$sOutput .= sprintf( "Katalog \"%s\" <span class=\"red\">nie został usunięty</span>\r\n", $oFile -> getPathname() );
						}
					}
					else
					{
						/**
						 * Usuwanie pliku
						 */
						if( ! unlink( $oFile -> getPathname() ) )
						{
							$sOutput .= sprintf( "Plik    \"%s\" <span class=\"red\">nie został usunięty</span>\r\n", $oFile -> getPathname() );
						}
					}
				}

				$oDirectory = NULL;

				/**
				 * Usuwanie ostatniego katalogu
				 */
				if( ! rmdir( $this -> oShell -> sArgv ) )
				{
					return $sOutput . sprintf( 'Katalog "%s" <span class="red">nie został usunięty</span>', $this -> oShell -> sArgv );
				}
			}
			catch( Exception $oException )
			{
				return sprintf( "Nie można otworzyć katalogu \"%s\"\r\n\r\nErro: %s", $sDir, $oException -> getMessage()  );
			}

			return sprintf( 'Katalog "%s" <span class="green">został usunięty</span>', $this -> oShell -> sArgv );
		}

		return sprintf( 'Podana ścieżka "%s" nie istnieje', $this -> oShell -> sArgv );
	}

}