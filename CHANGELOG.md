Changelog:
==========

2012-00-00 v0.60
----------------
*	Poprawiono
	*	Moduły
		*	`ls` - `SplFileInfo::getType(): open_basedir restriction in effect. ...` - Daniel
		*	`emailvalidator`
			*	cachowanie hostów
			*	pop3 bez ssl zamieniono na pop3/imap z ssl
			*	fatalne działanie live.com oraz neostrada.pl
			*	metoda setOutputFile
		*	Irc - Copi
			*	Ustanawianie połączenia z IRC (polecenie PING)
			*	Po wykonaniu polecenia `:ircmsg` zwracany był komunikat `Nie ma takiej komendy "ircmsg"`
		*	`md5crack` - API `www.tmto.org` zastąpiono `md5.darkbyte.ru`
		*	`revip` - Zmieniono adres strony na ip-adress.com
		*	`portscanner` - wyświetlanie komunikatów
		*	`info` - pełna ścieżka skryptu w CLI
		*	`ls` - podążanie za dowiązaniami symbolicznymi
		*	`download` - błąd podczas pobierania pliku
		*	`exit` i `quit` to aliasy dla `logout`, `eval` i `php`, wszystkie dostępne jako polecenia podstawowe
		*	Wyświetlanie pomocy `help` zawiera podstawowe informacje, `:help all` wyświetla szczegółową listę
	*	Obfuskacja kodu - problem z `echo`
	*	Technologia AJAX
	*	Rozmiar plików wynikowych jest o ~33% mniejszy
	*	Zarządzanie shellem poprzez CLI
	*	`phpinfo` w CLI
	*	dokumentacja klas, nagłówków
	*	interface do tworzenia modułu został zastąpiony klasa abstrakcyjną
*	Dodano
	*	`emailvalidator`
		*	nowe sterowniki:
			*	Web.de
			*	Gmx.de
			*	Pino.pl
			*	Inmail.pl
			*	Gg.pl
			*	Mail.ru
			*	Hotmail.it, Hotmail.co.jp
		*	Ręczne sprawdzenie przy użyciu`:emailvalidator test@wp.pl:test test2@wp.pl:test2 test3@wp.pl:test3`
		*	Tryb gadatliwy - `-v`
	*	Tworzenie shella
		*	help dla pliku `make.php`
		*	możliwość całkowitego wyłączenia js/css podczas tworzenia shella
		*	`--css=blue` - podmiana styli
		*	wersja zawiera datę utworzenia shella oraz dodatkowe informacje ustawione podczas tworzenia shella
*	Ponadto
	*	`cd` jest natywnym modułem
	*	Dokumentacja klas, metod, właściwości
	*	Testy jednostkowe
	*	Interfejs do tworzenia modułu został zastąpiony klasa abstrakcyjną
	*	Aktualizacna biblioteki `Pack`


2012-01-26 v0.50
----------------

*	Dodano
	*	Blokada bota Google
	*	`portscanner` - prosty skaner portów
	*	`id` - informacje o użytkowniku
	*	`speedtest` - sprawdzanie szybkości łącza
	*	`touch` - zmiana czasu modyfikacji i dostępu pliku
	*	`emailvalidator` - nowe sterowniki:
		*	Yahoo.com
		*	Gmail.com
		*	Live.com
		*	Neostrada.pl
		*	Orange.pl
		*	Plusnet.pl
	*	`autoload` - wczytywanie rozszerzeń
	*	`version` - wyświetlanie wersji shell'a
	*	`remote` - zdalnie wywołanie shella
*	Poprawiono
	*	Parsowanie znaku `+` podczas wysyłania polecenia AJAX'em
	*	`emailvalidator` - niepoprawne dane do gazeta.pl
	*	`passwordrecovery` - błędny plik pomocy podczas wprowadzenia złej ilości parametrów
	*	Klasa XRecursiveDirectoryIterator (rozszerzenie RecursiveDirectoryIterator) nie zwraca wyjątku
	*	System autoryzacji - wystarczy zdefiniować stałą NF_AUTH z wartością `sha1( "user\xffhasło" )`; autoryzacja wyłączona jest w CLI
	*	js
		*	po wpisaniu `:` wyświetlane są wszystkie dostępne polecenia
		*	po wpisaniu pełnego polecenia nie są wyświetlane kolejne, które zawierają daną frazę
	*	`?p` zamieniono na `?pure`
	*	`dos` - usunięto problem z DoS'owaniem na port "0"


2011-10-17 v0.41
----------------

*	Usunięto niedozwolony znak `/` z prefixu (aktualnie `0-9a-f{10}`)
*	Dodano moduł `emailvalidator` - sprawdzanie czy za pomocą odpowiedniego loginu oraz hasła można zalogować się na pocztę
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
