# Platforma BHP

Kompletny szkielet aplikacji szkoleniowej BHP dla cudzoziemców. Projekt zawiera wielojęzyczny interfejs (PL, EN, RU, ID, VI), rejestrację użytkowników, panel kursanta oraz panel administratora z zarządzaniem kursami, pytaniami i certyfikatami.

## Wymagania

- PHP 8+
- MySQL 8
- Serwer HTTP (np. Apache z XAMPP)

## Instalacja

1. Sklonuj repozytorium do katalogu dostępnego przez serwer WWW (np. `htdocs/BHP`).
2. Utwórz bazę danych MySQL o nazwie `serwer244157_bhp` i zaimportuj plik `sql/schema.sql`.
3. Domyślne połączenie korzysta z danych hostingu LH.pl (`DB_USER=serwer244157_bhp`, `DB_PASS=PAczkos19861986#`). W razie potrzeby zmodyfikuj je w `includes/config.php` lub ustaw zmienne środowiskowe (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
4. Upewnij się, że katalogi `uploads/` oraz `certificates/` mają prawa do zapisu.
5. Uruchom aplikację przez `http://localhost/BHP/public` (panel kursanta) oraz `http://localhost/BHP/admin` (panel administratora).

## Konta testowe

- Administrator: `admin@example.com` / `Password123!`
- Kursanci: `anna@example.com`, `hoa@example.com`, `ivan@example.com` (hasło `Password123!`).

## Funkcjonalności

- Wielojęzyczność z plikami JSON.
- Rejestracja i logowanie pracowników.
- Panel kursanta z materiałami, testami i certyfikatami PDF.
- Panel administratora (użytkownicy, kursy, pytania, wyniki, certyfikaty).
- Generowanie certyfikatów PDF oraz przesyłanie podpisanych skanów.
- Tryb ciemny (localStorage) i automatyczne powiadomienia e-mail (funkcja `mail`).

## Struktura katalogów

```
public/         - warstwa frontowa dla kursantów
admin/          - panel administratora
includes/       - wspólne funkcje, konfiguracja i generowanie certyfikatów
lang/           - pliki tłumaczeń (JSON)
assets/         - style CSS i skrypty JS
uploads/        - materiały szkoleniowe i podpisane skany
certificates/   - wygenerowane certyfikaty PDF
sql/            - struktura bazy danych i dane przykładowe
```

## Testy

Aplikacja nie zawiera testów automatycznych. Uruchom projekt lokalnie i zweryfikuj logowanie, wyświetlanie kursów oraz generowanie certyfikatów po zdaniu testu.
