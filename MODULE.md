Jak mogę stworzyć własny moduł do shella?
=========================================

Założenia przykładowego modułu
------------------------------

Powiedzmy, że chcemy stworzyć moduł do obliczania sumy kontrolnej MD5 dla podanych plików. Oto kilka założeń

1.	Moduł będzie dostępny pod nazwami `md5` oraz `md5sum`

2.	Moduł będzie przyjmował przynajmniej 1 argument - nazwę pliku

3.	Jeżeli nie podano żadnego parametru w poleceniu to wyświetlony zostanie plik pomocy



Szkielet
--------

W celu ujednolicenia wszystkich modułów każda klasa pluginu musi rozszerzać abstrakcyjną klasę `ModuleAbstract`. Ponadto nazwa klasy musi zaczynać się od wyrazu `Module` np `ModuleMd5`. W skład abstrakcji wchodzą 4 publiczne metody, które muszą zostać przeciążone

*	`getCommands()` - array - metoda ta zwraca tablicę z listą poleceń, w naszym przypadku definicją będzie `return array( 'md5', 'md5sum' );`

*	`getVersion()` - string - metoda zwraca aktualną wersję oraz adres email autora; wersja w formacie `\d{1}\.\d{2}` natomiast data `Y-m-d`; zawartość metody to `return '1.00 2013-01-23 - <krzotr@gmail.com>';`

*	`getHelp()` - string - help czyli informacja na temat modułu, pomoc składać musi się z nagłówka - opisu modułu oraz rozszerzonych informacji

*	`get()` - string - metoda, która realizuje nasze zadanie



Przydatne zmienne
-----------------

Prócz powyższych metod istnieją jeszcze zmienne bardzo przydatne przy tworzeniu modułu. Są nimi:

*	`$this -> oShell -> bSafeMode` - boolean

	*	Jeżeli safe_mode jest włączone zmienna będzie miała wartość `TRUE`

*	`$this -> oShell` -> bWindows` - boolean

	*	Jeżeli działamy w środowisku Windows wartością będzie `TRUE`

*	`$this -> oShell` -> aArgv` - array

	*	Zmienna przechowuje parametry z jakimi został wywołany moduł, argumenty numerowane są od 0, przykładowo po wywołaniu polecenia `:test param1 param2` tablica będzie miała postać

		 *	[0] => param1

		 *	[1] => param2

*	`$this -> oShell -> iArgc` - integer

	*	Zmienna przechowuje ilość argumentów przekazanych do modułu, , przykładowo po wywołaniu polecenia `:test param1 param2` otrzymamy wartość `2`, ponieważ pierwszym argumentem jest `param1` a drugin `param2`

*	`$this -> oShell -> aOptv` - array

	*	tablica przechowuje zbiór przełączników np `-v - verbose`, po wywołaniu `:test -ab -c param1 param2` zawartością tablicy będzie
		 *   [0] => a

		 *   [1] => b

		 *   [2] => c

*	`$this -> oShell -> sArgv` - string

	*	Zmienna przehowuje całe polecenie jakie zostało wywołane bez nazwy modułu, przykładowo po wywołaniu polecenia `:test -ab -c param1 param2` otrzymamy `-ab -c param1 param2`.



Szkielet
--------

Gotowy schemat wraz z dokładnym opisem można znaleźć w lokalizacji `Modules/Dummy.php`.

Opierając się na zdobytych wcześniej informacjach plik klasy zapisujemy w `Modules/Md5.php`

	<?php

	class ModuleMd5 extends ModuleAbstract
	{
		public function getCommands()
		{
			return array
			(
				'md5',
				'md5sum'
			);
		}

		public function getVersion()
		{
			return '1.00 2013-01-23 - <krzotr@gmail.com>';
		}

		public function getHelp()
		{
			return <<<DATA
	Obliczanie sumy kontrolnej dla pliku

		Użycie:
			md5sum plik.php [plik2.php] [plik3.php]
	DATA;
		}
		public function get()
		{
		}

	}

Test tymczasowego modułu
------------------------

Zawartość oczywiście nie jest kompletna, jednak częściowo działa. W celu przetestowania pliku pomocy wywołujemy `:md5 help` lub `:md5sum help`. W wyniku otrzymamy

	md5, md5sum - Obliczanie sumy kontrolnej dla pliku

		Użycie:
			md5sum plik.php [plik2.php] [plik3.php]

Na początku ciągu widzimy nazwy naszych poleceń, które możemu użyć. Są nimi `md5` oraz `md5sum`. Obie wskazują na ten sam moduł. Należy zwrócić uwagę, że po przekazaniu jako argument wartości `help` nie jest wywoływana metoda użytkownika `get()`



Metoda get()
------------

### Założenie 3

Pierwsze założenie zostało już spełnione. Przejdę teraz do 3.

Z oferowanych zmiennych wykorzystam `$this -> oShell -> iArgc`. Jeżeli wartość będzie równa `0` to będzie znaczyło, że nie podanego żadnego paramtru

	public function get()
	{
		if( $this -> oShell -> iArgc === 0 )
		{
			return $this -> getHelp();
		}
	}

Szybki test polecenie `:md5` i w wyniku mam plik pomocy

	Obliczanie sumy kontrolnej dla pliku

		Użycie:
			md5sum plik.php [plik2.php] [plik3.php]


### Założenie 3

Moduł będzie przyjmował wiele parametrów. Należy też sprawdzić, czy plik istnieje. Posłużę się w szczególności `$this -> oShell` -> aArgv`.

	$sOutput = NULL;

	// pobieranie nazw plików
	foreach( $this -> oShell -> aArgv as $sFile )
	{
		// bezwzględne dalsze sprawdzanie
		if( ! is_file( $sFile ) )
		{
			return sprintf( 'Podany plik %s nie istnieje', $sFile );
		}

		// suma kontrolna
		$sOutput .= sprintf( "%s  %s\r\n", md5_file( $sFile ), basename( $sFile ) );
	}

	if( $sOutput === NULL )
	{
		$sOutput = 'Podane pliki nie istnieją';
	}

	// zwracanie wyniku
	return $sOutput;

W ten oto sposób został stworzony moduł. Nadszedł czas na test

`:md5 shell.php`

	2eae3a7c433062966774e97502cd6cdc  shell.php

`:md5sum shell.php TODO.md CHANGELOG.md`

	2eae3a7c433062966774e97502cd6cdc  shell.php
	37bb661d4f727a3798f7fc2c1fc2b75c  TODO.md
	61da3c2ca64f4452d3c0507c3af772d1  CHANGELOG.md

`:md5sum file file2`

	Podany plik file nie istnieje

Warto zwrócić uwagę, że polecenie linuxowe `md5sum` działa w identyczny sposób

md5sum shell.php TODO.md CHANGELOG.md

	2eae3a7c433062966774e97502cd6cdc  shell.php
	37bb661d4f727a3798f7fc2c1fc2b75c  TODO.md
	61da3c2ca64f4452d3c0507c3af772d1  CHANGELOG.md

Nie będę tworzył funkcjonalności odwrotnej czyli sprawdzanie sumy kontrolnej na podstawie danych zawartych w pliku .md5 (polecenie `md5sum -d`). Powiem tylko, że należy sprawdzić czy zmienna `$this -> oShell -> aOptv` zawiera wartość 'd'. Jeśli tak to wykonywana jest funkcja odwrotna a w przeciwnym razie przeliczana jest suma kontrolna.

Kompletny kod modułu
--------------------

	<?php

	class ModuleMd5 extends ModuleAbstract
	{
		public function getCommands()
		{
			return array
			(
				'md5',
				'md5sum'
			);
		}

		public function getVersion()
		{
			return '1.00 2013-01-23 - <krzotr@gmail.com>';
		}

		public function getHelp()
		{
			return <<<DATA
	Obliczanie sumy kontrolnej dla pliku

		Użycie:
			md5sum plik.php [plik2.php] [plik3.php]
	DATA;
		}

		public function get()
		{
			$sOutput = NULL;

			// pobieranie nazw plików
			foreach( $this -> oShell -> aArgv as $sFile )
			{
				// bezwzględne dalsze sprawdzanie
				if( ! is_file( $sFile ) )
				{
					return sprintf( 'Podany plik %s nie istnieje', $sFile );
				}

				// suma kontrolna
				$sOutput .= sprintf( "%s  %s\r\n", md5_file( $sFile ), basename( $sFile ) );
			}

			if( $sOutput === NULL )
			{
				$sOutput = 'Podane pliki nie istnieją';
			}

			// zwracanie wyniku
			return $sOutput;
		}

	}