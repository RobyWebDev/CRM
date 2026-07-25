# Lokalizáció (i18n) — terv

> Az `adatmodell.md`-ben az `accounts` és `users` táblák már tartalmaznak egy `locale` mezőt (alapértelmezett: `hu`) — ez a fájl kifejti, mit jelent ez a gyakorlatban, és mit kell hozzá tenni a jövőbeli SaaS-fázisban, amikor esetleg nem csak magyar anyanyelvű felhasználók lesznek.
> MVP-ben (Rob, magyar felület) ez a téma gyakorlatilag lezárt kérdés — a terv csak arra való, hogy a `locale` mező bekerüljön a sémába előrelátóan (már megtörtént), és ne kelljen később átalakítani.
> Utolsó frissítés: 2026-07-25.

## 1. MVP-állapot

- Az alkalmazás felülete kizárólag magyar (`hu`) — a Blade-sablonokban a szövegek egyelőre közvetlenül magyarul szerepelhetnek, NEM kell rögtön a teljes Laravel-lokalizációs (`lang/` fájlos) rendszert bevezetni, ha az csak felesleges overhead lenne MVP-ben.
- **Kivétel:** érdemes már MVP-ben is Laravel `__()` helper-t / `lang/hu/*.php` fájlokat használni a validációs hibaüzeneteknél és a rendszer-generált szövegeknél (pl. e-mail sablonok), mert ez gyakorlatilag nulla többletmunka Laravelben, és később nem kell utólag szétszedni a keményen bekódolt magyar szövegeket.

## 2. Mikor válik ténylegesen szükségessé a többnyelvűség

Amikor a 6. fázisban (SaaS réteg, coach-kereső integráció) megjelenhetnek nem magyar anyanyelvű tenantok. Ekkor:

- A `users.locale` / `accounts.locale` mező alapján egy middleware állítja be a Laravel `App::setLocale()`-t minden kérésnél.
- A Blade-sablonokban addigra át kell térni a `__('kulcs')` / `lang/{locale}/*.php` mintára mindenhol, ahol még nem így volt (ez a fő migrációs munka, ha MVP-ben elmaradt).
- Validációs üzenetek, e-mail sablonok (`ertesitesek-terv.md`), rendszerüzenetek mind lokalizálva.
- **Egyedi mezők (`custom_field_definitions.label`)** — ezek felhasználó által megadott szövegek, ezeket NEM kell/lehet automatikusan fordítani, ez rendben van, mert account-specifikusak.

## 3. Formátumok (dátum, pénznem, szám)

- Dátum-formázás Laravel Carbon-nal, `locale`-alapú (`Carbon::setLocale()`), hogy magyar felhasználónál `2026.07.25.`, más locale-nál a helyi konvenció szerint jelenjen meg.
- Pénznem: a `deals.currency` mező (lásd `adatmodell.md`) már accountonként/dealenként eltérő pénznemet enged (alapértelmezett HUF) — ez már a séma szintjén elő van készítve.

## 4. Mit NEM kell most eldönteni

- Pontosan mely nyelvekre fordítjuk le a felületet — ez üzleti döntés, amikor a SaaS-fázis konkrét célpiaca kirajzolódik. Nem MVP-kérdés.

## 5. Kapcsolódó dokumentumok

- [`adatmodell.md`](adatmodell.md) — `locale` mezők.
- [`ertesitesek-terv.md`](ertesitesek-terv.md) — e-mail sablonok, amik érintettek lesznek.
