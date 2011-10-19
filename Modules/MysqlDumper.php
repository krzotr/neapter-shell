<?php

/**
 * Neapter Framework
 *
 * @version   $Id: MysqlDumper.php 582 2011-06-02 16:07:16Z krzotr $
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2010-2011, Krzysztof Otręba
 *
 * @link      http://neapter.com
 * @license   http://neapter.com/license
 */

/**
 * class MysqlDumperException - CdnSerwer Wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    Lib
 * @subpackage MysqlDumper
 * @uses       \Exception
 */
class MysqlDumperException extends Exception {}

/**
 * class MysqlDumper - Zrzucanie zawartosci bazy danych
 *
 * Biblioteke mozna uruchomic w srodowisku PHP 5.2.X, wystarczy usunac linie 15-19
 * ----------------------
 *    namespace Neapter\Lib;
 *
 *    use Neapter\Core\SetLib,
 *        \Exception,
 *        \PDO;
 * ----------------------
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @package    Lib
 * @subpackage MysqlDumper
 *
 * @uses       Neapter\Core\SetLib
 * @uses       Neapter\Lib\MysqlDumperException
 */
class MysqlDumper
{
	/**
	 * Obiekt PDO
	 *
	 * @access protected
	 * @var    object
	 */
	protected $oPdo;

	/**
	 * Lista tabel do zrzucenia
	 *
	 * @access protected
	 * @var    array
	 */
	protected $aTables = array();

	/**
	 * Ilosc przetwarzanych rekordow na raz
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $iLimit = 500;

	/**
	 * Rozszerzone dodanie
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bExtendedInsert = TRUE;

	/**
	 * Pobieranie calej struktury danych
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $bDownload = 0;

	/**
	 * Ustawianie obiektu PDO
	 *
	 * @access public
	 * @param  PDO         $oValue Obiekt PDO
	 * @return MysqlDumper         Obiekt MysqlDumper
	 */
	public function setPdo( PDO $oValue )
	{
		$this -> oPdo = $oValue;

		/**
		 * Atrybuty
		 */
		$aAttributes = array
		(
			PDO::MYSQL_ATTR_DIRECT_QUERY       => 0,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1,
			PDO::MYSQL_ATTR_MAX_BUFFER_SIZE    => 1048576,
			PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
			PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS             => PDO::NULL_NATURAL
		);

		/**
		 * Ustawianie atrybutow
		 */
		foreach( $aAttributes as $sVar => $mVar )
		{
			$this -> oPdo -> setAttribute( $sVar, $mVar );
		}

		return $this;
	}

	/**
	 * Ustawianie tabel do zrzucenia
	 *
	 * @access protected
	 * @param  array     $aValue Tablica tabeli
	 * @return                   MysqlDumper
	 */
	public function setTables( array $aValue )
	{
		$this -> aTables = $aValue;

		return $this;
	}

	/**
	 * Ustawianie ilosc przetwarzanych rekordow na raz
	 *
	 * @uses   Neapter\Lib\MysqlDumperException
	 *
	 * @access protected
	 * @param  integer   $iValue Ilosc przetwarzanych rekordow na raz
	 * @return                   MysqlDumper
	 */
	public function setLimit( $iValue )
	{
		if( ( $iValue < 10 ) || ( $iValue > 50000 ) )
		{
			throw new MysqlDumperException( 'Limit musi być z przedziału 10 - 50000' );
		}

		$this -> iLimit = (int) $iValue;

		return $this;
	}

	/**
	 * Ustawianie rozszerzonego dodanie
	 *
	 * @access protected
	 * @param  boolean   $bValue Rozszerzone dodanie
	 * @return                   MysqlDumper
	 */
	public function setExtendedInsert( $bValue )
	{
		$this -> bExtendedInsert = (boolean) $bValue;

		return $this;
	}

	/**
	 * Ustawianie pobierania calej struktury danych
	 *
	 * @access protected
	 * @param  boolean   $bValue Pobieranie calej struktury danych
	 * @return                   MysqlDumper
	 */
	public function setDownload( $bValue )
	{
		$this -> bDownload = (boolean) $bValue;

		return $this;
	}

	/**
	 * Pobieranie listy kolumn w danej tabeli
	 *
	 * @access protected
	 * @param  string    $sTable Nazwa tabeli
	 * @return array             Lista kolumn
	 */
	protected function getFields( $sTable )
	{
		foreach( $this -> aTables[ $sTable ] as $aDesc )
		{
			$aFields[] = $aDesc['Field'];
		}

		return $aFields;
	}

	/**
	 * Pobieranie typu kolumny
	 *
	 * @access    public
	 * @staticvar array  $aMainTypes Lista dostepnych pol i ich odpowiedniki
	 * @param     string $sTable     Nazwa tabeli
	 * @param     string $sField     Nazwa kolumny
	 * @return    string             Typ pola
	 */
	public function getFieldType( $sTable, $sField )
	{
		/**
		 * Lista dostepnych typow pol i ich odpowiedniki
		 */
		static $aMainTypes = array
		(
			'binary' => array
			(
				'binary',
				'varbinary',
				'tinyblob',
				'blob',
				'mediumblob',
				'longblob'
			),
			'int'    => array
			(
				'tinyint',
				'smallint',
				'mediumint',
				'int',
				'bingint',
				'bit'
			),
			'float'  => array
			(
				'float',
				'double',
				'decimal'
			),
			'string' => array
			(
				'char',
				'varchar',
				'tinytext',
				'text',
				'mediumtext',
				'longtext'
			)
		);

		/**
		 * Pobieranie typu kolumny
		 */
		foreach( $this -> aTables[ $sTable ] as $aDesc )
		{
			if( $aDesc['Field'] === $sField )
			{
				$sFieldType = $aDesc['Type'];
				break ;
			}
		}

		if( isset( $sFieldType ) )
		{
			/**
			 * Dopasowanie typu kolumny MySQL do ogolnego ogolnego typu
			 */
			foreach( $aMainTypes as $sMainType => $aTypes )
			{
				foreach( $aTypes as $sType )
				{
					if( strncasecmp( $sFieldType, $sType, strlen( $sType ) ) === 0 )
					{
						return $sMainType;
					}
				}
			}
		}

		return 'string';
	}

	/**
	 * Pobieranie struktury tabel
	 *
	 * @access    public
	 * @staticvar array  $aReplace Tablica znakow do zamienienia
	 * @return    void
	 */
	public function get()
	{
		ob_start( 'ob_gzhandler' );
		$fStart = microtime( 1 );

		/**
		 * Lista tabel uzytkownika
		 */
		if( $this -> aTables !== array() )
		{
			$this -> aTables = array_flip( $this -> aTables );
		}
		else
		{
			/**
			 * Lista wszystkich tabel
			 */
			$oSql = $this -> oPdo -> query( 'SHOW TABLES' );

			while( $aRows = $oSql -> fetch() )
			{
				$sTablename = current( $aRows );

				$this -> aTables[ $sTablename ] = array();
			}
			$oSql -> closeCursor();
		}

		try
		{
			/**
			 * Informacje o kolumnach
			 */
			foreach( $this -> aTables as $sTablename => $sVal )
			{
				$oTableDescSql = $this -> oPdo -> query( sprintf( 'DESC `%s`', $sTablename ) );

				$this -> aTables[ $sTablename ] = $oTableDescSql -> fetchAll() ;

				$oTableDescSql -> closeCursor();
			}
		}
		catch( PDOException $oException )
		{
			throw new MysqlDumperException( $oException -> getMessage() );
		}

		/**
		 * Naglowek - pobieranie pliku
		 */
		if( $this -> bDownload )
		{
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Disposition: attachment; filename="db.sql"' );
		}

		header( 'Content-Type: text/plain' );

		/**
		 * Iteracja tabeli
		 */
		foreach( $this -> aTables as $sTablename => $aDesc )
		{
			/**
			 * Ilosc wszystkich rekordow
			 */
			$oSql = $this -> oPdo -> query( sprintf( 'SELECT COUNT(*) as nums FROM %s', $sTablename ) );
			$aRow = $oSql -> fetch();
			$iNums = $aRow['nums'];

			/**
			 * Struktura tabeli
			 */
			$oSql = $this -> oPdo -> query( sprintf( 'SHOW CREATE TABLE `%s`', $sTablename ) );
			$aRow = $oSql -> fetch();

			/**
			 * Wyswietlanie struktury tabeli + DISABLE KEYS
			 */
			printf( "-- ----------------------------------------------------\r\n" .
				"-- Struktura tabeli %s\r\n" .
				"-- ----------------------------------------------------\r\n" .
				"%s;\r\n\r\n/*!40000 ALTER TABLE `%s` DISABLE KEYS */;\r\n",
				$sTablename, $aRow['Create Table'], $sTablename
			);

			/**
			 * Ilosc etapow wykonania
			 */
			$iLoops = ceil( $iNums / $this -> iLimit );

			$aTypes = array();

			for( $i = 0; $i < $iLoops; $i++ )
			{
				/**
				 * Pobieranie rekordow
				 */
				$oSql = $this -> oPdo -> query( sprintf( 'SELECT * FROM `%s` LIMIT %d OFFSET %d', $sTablename, $this -> iLimit, ( $this -> iLimit ) * $i ) );

				/**
				 * Jezeli rozszerzone dodawanie
				 */
				if( $this -> bExtendedInsert )
				{
					$sRow = sprintf( 'INSERT INTO `%s` (`%s`) VALUES ', $sTablename, implode( '`, `', $this -> getFields( $sTablename ) ) );
				}

				while( $aRow = $oSql -> fetch() )
				{
					/**
					 * Jezeli nie ma okreslonych typow pol
					 */
					if( $aTypes === array() )
					{
						/**
						 * [nazwa_kolumny] => ''
						 */
						$aTypes = array_flip( array_keys( $aRow ) );
						$tthis = $this;

						/**
						 * [nazwa_kolumny] => 'typ'
						 */
						array_walk( $aTypes, create_function( '& $sVal, $sKey, array $aData', '$sVal = $aData[0] -> getFieldType( $aData[1], $sKey );' ), array( $this, $sTablename )
						);
					}

					/**
					 * Jezeli nie ma rozszerzonego dodawania
					 */
					if( ! $this -> bExtendedInsert )
					{
						$sRow = sprintf( 'INSERT INTO `%s` (`%s`) VALUES ', $sTablename, implode( '`, `', $this -> getFields( $sTablename ) ) );
					}

					$sRow .= '(';
					foreach( $aRow as $sField => $mValue )
					{
						/**
						 * Jezeli pole ma wartosc NULL
						 */
						if( ( $mValue === NULL ) )
						{
							/**
							 * Jezeli kolumna zezwala na NULL
							 */
							if( $this -> isNull( $sTablename, $sField ) )
							{
								$sRow .= 'NULL, ';
							}
							else
							{
								$sRow .= '\'\', ';
							}
							continue ;
						}

						/**
						 * Formatowanie rekordu wedlug jego typu
						 */
						switch( $aTypes[ $sField ] )
						{
							/**
							 * Int / Float
							 */
							case 'int':
							case 'float':
								$sRow .= $mValue . ', ';
								break ;
							/**
							 * Bin
							 */
							case 'binary':
								if( $mValue == '' )
								{
									$sRow .= '\'\', ';
								}
								else
								{
									$sRow .= '0x' . bin2hex( $mValue ) . ', ';
								}
								break ;
							/**
							 * String, data itp
							 */
							default :
								static $aReplace = array
								(

									"\r"   => '\r',
									"\n"   => '\n',
									"\t"   => '\t',
									"\x00" => '\0'
								);
								$sRow .= '\'' . strtr( addslashes( $mValue ), $aReplace ) . '\', ';
						}
					}

					/**
					 * Jezeli rozszerzone dodawanie to aktualna wartosc ciagle dopisujemy do
					 * $sRow, jezeli nie ma rozszerzeonego dodawania to wyswietlamy dane
					 */
					if( $this -> bExtendedInsert )
					{
						$sRow = substr( $sRow, 0, -2 ) . "),\r\n";
					}
					else
					{
						echo substr( $sRow, 0, -2 ) . ";\r\n";
					}
				}

				/**
				 * Jezeli rozszerzone dodawanie
				 */
				if( $this -> bExtendedInsert )
				{
					echo substr( $sRow, 0, -3 ) . ";\r\n\r\n";
				}

				/**
				 * Flush
				 */
				if( ob_get_length() > 0 )
				{
					@ ob_flush();
					@ flush();
				}
			}

			/**
			 * ENABLE KEYS - szybszy import pliku SQL
			 */
			echo "/*!40000 ALTER TABLE `{$sTablename}` ENABLE KEYS */;\r\n\r\n";
		}

		printf( '-- Wygenerowano w: %5f', microtime( 1 ) - $fStart );
		@ ob_end_flush();
	}

	/**
	 * Czy kolumna zezwala na wartosc NULL
	 *
	 * @access protected
	 * @param  string    $sTable Nazwa tabeli
	 * @param  field     $sField Nazwa pola
	 * @return boolean           TRUE jesli pole zezwala na na NULL
	 */
	protected function isNull( $sTable, $sField )
	{
		foreach( $this -> aTables[ $sTable ] as $aDesc )
		{
			if( $aDesc['Field'] === $sField )
			{
				return ( $aDesc['Null'] === 'YES' );
			}
		}

		return FALSE;
	}

}

/**
 * =================================================================================================
 */

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * ModuleMysqlDump - Zrzucanie rekordow z bazy danych
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2011, Krzysztof Otręba
 */
class ModuleMysqlDump implements ShellInterface
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
			'mysqldump',
			'mysqldumper',
			'mysqlbackup'
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
		return '1.03 2011-10-19 - <krzotr@gmail.com>';
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
Kopia bazy danych MySQL

	Użycie:
		mysqldump host:port login@hasło nazwa_bazy [tabela1] [tabela2]

	Przykład:
		mysqldump localhost:3306 test@test mysql users
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
		 * Czy modul jest zaladowany
		 */
		if( ! class_exists( 'MysqlDumper' ) )
		{
			return 'mysqldump, mysqldumper, mysqlbackup - !!! moduł nie został załadowany';
		}

		/**
		 * Help
		 */
		if( $this -> oShell -> iArgc < 3 )
		{
			return $this -> getHelp();
		}

		$aHost = $this -> oShell -> getHost( $this -> oShell -> aArgv[0] );

		/**
		 * Domyslny port to 3306
		 */
		if( $aHost[1] === 0 )
		{
			$aHost[1] = 3306;
		}

		/**
		 * login@pass
		 */
		list( $sUsername, $sPassword ) = explode( '@', $this -> oShell -> aArgv[1], 2 );

		/**
		 * PDO jest wymagane
		 */
		if( ! extension_loaded( 'pdo' ) )
		{
			return 'Brak rozszerzenia PDO';
		}

		try
		{
			/**
			 * Polaczenie do bazy
			 */
			$oPdo = new PDO( sprintf( 'mysql:host=%s;port=%d;dbname=%s', $aHost[0], $aHost[1], $this -> oShell -> aArgv[2] ), $sUsername, $sPassword );

			$oDumper = new MysqlDumper();
			$oDumper
				-> setPdo( $oPdo )
				-> setDownload( 1 )
				-> setExtendedInsert( 1 );

			if( $this -> oShell -> iArgc > 3 )
			{
				$oDumper -> setTables( array_slice( $this -> oShell -> aArgv, 3 ) );
			}
			$oDumper -> get();
			exit ;
		}
		/**
		 * Wyjatek
		 */2
		catch( PDOException $oException )
		{
			return sprintf( 'Wystąpił błąd: %s', $oException -> getMessage() );
		}
		catch( MysqlDumperException $oException )
		{
			return sprintf( 'Wystąpił błąd: %s', $oException -> getMessage() );
		}
	}

}