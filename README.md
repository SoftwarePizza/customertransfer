# Moduł Customer Transfer - Instrukcja instalacji

## Opis
Moduł umożliwia przenoszenie klientów między sklepami w środowisku multishop PrestaShop poprzez dodanie przycisku w panelu administracyjnym.

## Instalacja

1. **Skopiuj moduł**
   Folder `customertransfer` powinien być już w katalogu `/modules/`

2. **Zainstaluj moduł w PrestaShop**
   - Przejdź do panelu administracyjnego PrestaShop
   - Idź do: Moduły > Menedżer modułów
   - Znajdź moduł "Customer Transfer"
   - Kliknij "Zainstaluj"

3. **Sprawdź działanie**
   - Przejdź do: Klienci > Klienci
   - Wybierz dowolnego klienta i kliknij "Edytuj"
   - Na dole formularza powinien pojawić się panel "Przenoszenie klienta między sklepami"

## Użytkowanie

1. **Przenoszenie klienta**
   - Otwórz widok edycji klienta
   - Przewiń na dół do panelu "Przenoszenie klienta między sklepami"
   - Wybierz sklep docelowy z listy rozwijanej
   - Kliknij przycisk "Przenieś klienta"
   - Potwierdź akcję w oknie dialogowym

2. **Funkcje modułu**
   - Przenosi klienta do wybranego sklepu
   - Automatycznie przenosi adresy klienta
   - Wyświetla komunikaty sukcesu/błędu
   - Odświeża stronę po udanym przeniesieniu

## Uwagi techniczne

- Moduł działa tylko w środowisku multishop
- Wymaga uprawnień administratora
- Zamówienia klienta pozostają w oryginalnym sklepie (ze względów księgowych)
- Panel jest widoczny tylko gdy dostępne są co najmniej 2 sklepy

## Bezpieczeństwo

- Wszystkie operacje wymagają tokenu CSRF
- Sprawdzanie uprawnień administratora
- Walidacja danych wejściowych
- Obsługa błędów

## Rozwiązywanie problemów

1. **Panel nie pojawia się**
   - Sprawdź czy moduł jest zainstalowany
   - Sprawdź czy środowisko multishop jest aktywne
   - Sprawdź czy istnieją co najmniej 2 sklepy

2. **Błędy AJAX**
   - Sprawdź uprawnienia plików modułu
   - Sprawdź logi błędów PrestaShop
   - Sprawdź konsolę przeglądarki

3. **Przenoszenie nie działa**
   - Sprawdź czy klient istnieje
   - Sprawdź czy sklep docelowy istnieje
   - Sprawdź logi błędów

## Deinstalacja

1. Przejdź do: Moduły > Menedżer modułów
2. Znajdź moduł "Customer Transfer"
3. Kliknij "Odinstaluj"
4. Opcjonalnie usuń folder `/modules/customertransfer/`
