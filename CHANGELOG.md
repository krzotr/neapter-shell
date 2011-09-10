Changelog:
==========

2011-09-
---------------

*	Dodano moduł `autoremove`
*	Dodano wykonanie polecenia systemowego za pomocą funkcji `pcntl_exec`
*	Dodano możliwość wrzucenia pliku na serwer `:upload`, opcja ta jest dostępna natywnie
*	Dodano możliwość edycji oraz tworzenia nowego pliku
*	Poprawiono wyświetlanie pomocy dla natywnych modułów
*	Poprawiono formatowanie wyniku za pomocą polecenia `hexdump`
*	Poprawiono pobieranie pliku za pomocą kompresji gzip `:down -g`


2011-09-08 v0.31a
-----------------

*	Zamieniono niebieski styl na ciemniejszy
*	Usunięto błąd typu 'Fatal Error' z modułów
	*	socketupload
	*	socketdownload,
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
	*	irc _możliwość powiązania shell'a z kanałem irc_
	*	hexdump
	*	logout
	*	exit
	*	system
	*	info
*	Usunięto problem z helpem w modułach
	*	Cat
	*	Cp
	*	G4m3
	*	Mv
	*	MysqlDumper
	*	Remove


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
	*	ping
	*	mkdir
	*	cp
	*	mv
	*	modules
	*	chmod
	*	mysql
	*	mysqldump
	*	backconnect
	*	bind
	*	proxy
	*	dos
	*	passwordrecovery
	*	cr3d1ts
*	możliwość wczytania danego modułu
*	polecenie `cr3d1ts` nie wyświetla się w help'ie
*	`php` jest aliasem dla `eval`


2011-05-15 v0.1
---------------

*	Pierwsza wersja skryptu, zawiera podstawowe komendy takie jak:
	*	echo
	*	ls
	*	cat
	*	eval
	*	remove
	*	bcat
	*	socketdownload
	*	ftpdownload
	*	download
	*	socketupload
	*	ftpupload
	*	etcpasswd
	*	game
	*	help