Changelog:
==========

*	`ls`
	*	Problem podczas listowania plików / katalogów
		*	Jeżeli Iterator nie może odczytać jakiejkolwiek ścieżki, rzuca wyjątek

*	Rozmiar shella
	*	Okrojona wersja przybiera na wadze, 9KB to zdecydowanie za dużo

*	Bind - nie wiadomo z jakiego powodu kończy połączenie

*	komenda `proxy`, `mysqldump` powinna zostać dodana do wyłączonych funkcji w js

*	Funkcja `filectime` została wyłączona na jednym z serwerów, trzeba zastąpić ją inną

*	OpenBasedir zwraca `Tak` w przypadku, kiedy dyrektywa nie jest pusta