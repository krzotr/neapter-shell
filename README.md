Neapter Shell
=============



Tworzenie shella
----------------

Shell składa się z kilkudziesięciu plików, dzięki czemu łatwiej można je edytować. Do utworzenia jednego pliku shella służy polecenie `make.php`

*	`make.php --help` / `make.php -h` - pomoc

*	`make.php` - tworzy shell ze wszystkimi modułami znajdującymi się w katalogu `modules`

*	`make.php type=lite` - wersja okrojona shella, waży ok 10KB, zawiera podstawowe funkcje takie jak:

	*	`autoload`

	*	`cd`

	*	`edit`

	*	`help`

	*	`info`

	*	`exit / logout`

	*	`modules`

	*	`system` / `exec`

	*	`upload`

	*	`eval / php`

	*	`version`

*	`make.php type=modules` - tworzy plik z modułami _Tmp/modules.txt_ znajdującymi się w katalogu `modules`, aby je wczytać należy wykonać polecenie `:modules sciezka_do_pliku_z_modulami`

*	`make.php --no-js` - pliki js nie zostaną dołączone

*	`make.php --no-css` - arkusze stylów nie zostaną dołączone

*	`make.php --no-extended-version` - dodatkowe informacje takie jak data utworzenia shalla nie zostaną dołączone do numeru wersji

Powyższe opcje można łączyć

	*	`make.php --no-js --no-extended-version`

	*	`make.php --no-js --no-css --no-extended-version` - zalecane dla uruchomienia w CLI

Pliki

*	`Tmp/final.php` - finalna wersja shella z użyciem `gzcompress`

*	`Tmp/prod.php` - finalna wersja shella

*	`Tmp/dev.php` - wersja deweloperska - zawiera _złączone_ wszystkie pliki, komentarze, formatowanie itp.

*	`Tmp/modules.txt` - plik z modułami



Obsługa shella
--------------

*	Wszystkie polecenia z modułów shellowych należy poprzedzić znakiem `:` przykład: `:ls`, `:pwd`. W przypadku wykonania polecenia bez znaku `:` zostanie wywołane polecenie systemowe przy użyciu funkcji `exec`, `shell_exec`, `passthru`, `system`, `popen`, `proc_open` lub `pcntl_exec`

*	Dostępna jest pomoc podstawowa `:help` oraz szczegółowa `:help all`. Możliwe jest wyświetlenie pomocy dla konkretnego modułu, wystarczy wywołać `:nazwa_modulu help` np `:md5crack help`

*	Podstawowymi poleceniami są:

	*	help          - Wyświetlanie pomocy

	*	modules       - Informacje o modułach

	*	edit          - Edycja oraz tworzenie nowego pliku

	*	upload        - Wrzucanie pliku na serwer

	*	system, exec  - Uruchomienie polecenia systemowego

	*	info          - Wyświetla informacje o systemie

	*	autoload      - Automatyczne wczytywanie rozszerzeń PHP

	*	cd            - Zmiana aktualnego katalogu

	*	eval, php     - Wykonanie kodu PHP

	*	version       - Wyświetlanie numeru wersji shella

	*	exit / logout - Wylogowanie z shella

	*	cr3d1ts       - Informacje o autorze
*	Istnieje możliwość przełączenia się na wersję deweloperską. Aby to zrobić należy dopisać zmienną `dev` w adresie (http://example.com/?dev)
*	W celu uruchomienia shella z domyślną konfigurację. Aby to zrobić należy w adresie dopisać zmienną `pure` (http://example.com/?pure). Shell w ten sposób pominie wczytywanie dodatkowych modułów (polecenie `modules`) oraz rozszerzeń (polecenie `autoload`)
*	Aby wyłączyć AJAX należy do adresu dodać zmienną nojs (http://example.com/?nojs)

FAQ
---

*	Jak włączyć zabezpieczenie hasłem do shella?

	*	Aby włączyć uwierzytelnianie należy na początku pliku zdefiniować stałą `NF_AUTH` z wartością `sha1( "user\xffpassword" );` Należy pamiętać, aby do stałej przekazać wyłącznie 40 znakowy hash.

*	Czy shell jest indeksowany przez Googlebot?

	*	Nie. Ze względu na bezpieczeństwo Googlebot otrzyma stronę z błędem 404 co uniemożliwia zaindeksowanie shella.

*	Jak mogę zmienić wygląd shella?

	*	W pliku `shell.php` zmodyfikuj linię

		`$this -> sStyleSheet = file_get_contents( 'Styles/dark.css' );`

	W miejsce `Styles/dark.css` należy wstawić ścieżkę do pliku ze stylami oraz uruchom `make.php`



Kontakt
-------

Sugestie, pytania?

Krzychu - krzotr@gmail.com