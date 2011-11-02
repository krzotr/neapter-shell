Changelog:
==========

2011-00-00 v0.00
----------------

*	Dodano
	*	`portscanner` - prosty skaner portów
	*	`id` - informacje o użytkowniku
	*	`speedtest` - sprawdzanie szybkości łącza
	*	Dostęp dla bota Google jest zablokowany
	*	`touch` - zmiana czasu modyfikacji i dostępu pliku
*	Poprawiono
	*	Parsowanie znaku `+` podczas wysyłania polecenia AJAX'em
	*	`passwordrecovery` - błędny plik pomocy podczas wprowadzenia złej ilości parametrów
	*	Klasa XRecursiveDirectoryIterator (rozszerzenie RecursiveDirectoryIterator) nie zwraca wyjątku


2011-10-17 v0.41
----------------

*	Usunięto niedozwolony znak `/` z prefixu (aktualnie `0-9a-f{10}`)
*	Dodano moduł `EmailValidation` - sprawdzanie czy za pomocą odpowiedniego loginu oraz hasła można zalogować się na pocztę
*	Moduł `download` wspiera zdalne pobieranie z pliku z prokotołu http oraz ftp


2011-10-06 v0.40
----------------

*	Dodano
	*	Wykonywanie poleceń odbywa się bez przeładowania strony - AJAX
		*	Wyświetlanie podpowiedzi dla poleceń
	*	Moduły
		*	`destroy` - usuwanie shella
		*	`mail` - wysyłanie emaili
		*	`upload` - natywny moduł
		*	`edit` - tworzenie oraz edycja pliku; moduł natywny
		*	`cd` - przejście do katalogu
		*	`pwd` - wyświetlanie katalogu, w którym aktualnie się znajdujemy
		*	`revip` - Reverse Ip
		*	`pack` / `unpack` - pakowanie / rozpakowywanie plików oraz katalogów
	*	Wykonanie polecenia systemowego za pomocą funkcji `pcntl_exec`
	*	`mysqldump` - wsparcie dla gzip
	*	Możliwość przełączenia się na wersję deweloperską (włączenie wyświetlania błądów)
	*	`Logout` jest natywnym modułem
*	Poprawiono
	*	parsowanie argumentów, które są objęte w `'` oraz `"` i zawierają spacje
	*	Wyświetlanie pomocy dla natywnych modułów
	*	Formatowanie wyniku za pomocą polecenia `hexdump`
	*	Pobieranie pliku za pomocą kompresji gzip `:down -g`
	*	Nadawanie uprawnień poprzez moduł `chmod`
	*	Parsowanie argumentów ujętych w `'` lub `"`
	*	Moduł szyfrowania pliku z modułami
	*	Informację o `open_basedir`
	*	System uwierzytelniania nie jest oparty na sesjach
	*	Moduł `ls` wyświetla prawidłowo ścieżkę do katalogu, w którym się znajdujemy
	*	API w `md5crack` zamieniono z hashkiller.com na tmto.org
*	Listę wszystkich modułów przeniesiono do `modules loaded`


2011-09-08 v0.31a
-----------------

*	Zamieniono niebieski styl na ciemniejszy
*	Usunięto błąd typu 'Fatal Error' z modułów
	*	`socketupload`
	*	`socketdownload`
*	Poprawiono wyświetlanie shella w rozdzielczości 1024


2011-09-07 v0.31
----------------

*	Poprawiono wyszukiwanie katalogu tymczasowego
*	Kiedy użyjemy polecenia `cd /var/www`, `pwd` zwróci `/var/www` (dotyczy `bind` oraz `backconnect`)
*	Poprawiono wyświetlanie output'u w `bind` i `backconnect`


2011-07-09 v0.30
----------------

*	Dodano możliwość zmiany styli
*	Dodano panel logowania #1
*	Dodano wykonanie polecenia systemowego za pomocą funkcji `proc_open()`
*	Wyświetlanie pomocy dla natywnych modułów `help`, `system`, `modules`
*	Usunięto problem z importem modułów (Permission Denied jeżeli katalog TMP jest inny niż `/tmp/`)
*	Utworzono publiczną metodę `getCommandSystem()` za pomocą której można wykonać polecenie systemowe
*	Poprawiono "przełączanie" między funkcjami systemowymi `system()`, `shell_exec()`, `passthru()`, `exec()`, `popen()`
*	Poprawiono wyświetlanie outputu za pomocą funkcji exec (zwracana była tylko ostatnia linia)
*	Dodano komendy
	*	`irc` - możliwość powiązania shell'a z kanałem irc
	*	`hexdump`
	*	`logout`
	*	`exit`
	*	`system`
	*	`info`
*	Usunięto problem z helpem w modułach
	*	`cat`
	*	`cp`
	*	`g4m3`
	*	`mv`
	*	`mysqldumper`
	*	`remove`


2011-06-18 v0.21
----------------

*	Usunięto błąd związany z umieszczeniem parametru w cudzysłowie lub apostrofie `:down "/sciezka/ do/ katalogu /"`
*	Usunięto XSS w lini poleceń
*	Poprawiono pobieranie plików
*	DoS na HTTP z użyciem CURL'a jest efektywniejszy


2011-06-03 v0.2
---------------

*	Wsparcie dla CLI
*	Shella rozszerzono o następujące komendy:
	*	`ping`
	*	`mkdir`
	*	`cp`
	*	`mv`
	*	`modules`
	*	`chmod`
	*	`mysql`
	*	`mysqldump`
	*	`backconnect`
	*	`bind`
	*	`proxy`
	*	`dos`
	*	`passwordrecovery`
	*	`cr3d1ts`
*	możliwość wczytania danego modułu
*	polecenie `cr3d1ts` nie wyświetla się w help'ie
*	`php` jest aliasem dla `eval`


2011-05-15 v0.1
---------------

*	Pierwsza wersja skryptu, zawiera podstawowe komendy takie jak:
	*	`echo`
	*	`ls`
	*	`cat`
	*	`eval`
	*	`remove`
	*	`bcat`
	*	`socketdownload`
	*	`ftpdownload`
	*	`download`
	*	`socketupload`
	*	`ftpupload`
	*	`etcpasswd`
	*	`game`
	*	`help`