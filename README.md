Neapter Shell
=============



Tworzenie shella
----------------

Shell składa się z kilkudziesięciu plików, dzięki czemu łatwiej można je edytować. Do utworzenia jednego pliku shella służy polecenie `make.php`

*	`make.php` - tworzy shell ze wszystkimi modułami znajdującymi się w katalogu `modules`

*	`make.php lite` - wersja okrojona shella, waży ok 7KB, zawiera podstawowe funkcje takie jak:

	*	`help`

	*	`modules`

	*	`system` / `exec`

	*	`info`

*	`make.php modules` - tworzy plik z modułami _Tmp/modules.txt_ znajdującymi się w katalogu `modules`, aby je wczytać należy wykonać polecenie `:modules sciezka_do_pliku_z_modulami`


Pliki

*	Tmp/final.php - finalna wersja shella z użyciem `base64_encode` oraz `gzcompress`

*	Tmp/prod.php - finalna wersja shella

*	Tmp/dev.php - wersja deweloperska - zawiera _złączone_ wszystkie pliki, komentarze, formatowanie itp.

*	Tmp/modules.txt - plik z modułami


__Uwaga__: Przed użyciem polecenia `make.php` upewnij się, że wartości `ini_set( 'display_errors', 1 );` oraz `error_reporting( -1 );` w pliku `shell.php` ustawione sa na __0__ !!!



Jak mogę zmienić wygląd shella?
-------------------------------

W pliku `shell.php` zmodyfikuj linię `$this -> sStyleSheet = file_get_contents( 'Styles/dark.css' );`. W miejsce `Styles/dark.css` wstaw ścieżkę do pliku ze stylami i uruchom `make.php`