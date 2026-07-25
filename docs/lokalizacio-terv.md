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

## 2b. Névsorrend nyelv szerint (2026-07-25, megvalósítva)

Rob kérésére ez már MVP-ben, ténylegesen implementálva van, nem csak terv: magyar nyelvi konvenció szerint a **vezetéknév áll elöl** (pl. "Kovács János"), angol (US) konvenció szerint a **keresztnév** (pl. "John Smith"). Ez minden `first_name`/`last_name` párost tartalmazó modellre vonatkozik (`Contact`, `Lead`).

- **`App\Models\Concerns\HasPersonName`** trait — `full_name` accessort ad (`$contact->full_name`), ami a bejelentkezett user (vagy account) `locale` mezője alapján dönti el a sorrendet (`hu*` → vezetéknév elöl, egyébként → keresztnév elöl).
- **`<x-name-fields>`** Blade-komponens — a kereszt-/vezetéknév beviteli mezőket ugyanígy, nyelv szerint helyes sorrendben (és a vizuálisan első mezőn `autofocus`-szal) jeleníti meg minden űrlapon (Kontaktok, Leadek létrehozása/szerkesztése).
- Ez a mechanizmus már MOST is működik magyar nyelven (mivel `locale = 'hu'` mindenhol), és **készen áll** arra, hogy amint egy account/user `locale`-ja `en`-re (vagy `en_US`-re) vált, a sorrend automatikusan angol konvencióra váltson — nem kell hozzá új kód, csak a `locale` mező értéke.

## 2c. Kapcsolódás a jövőbeli admin-szintű testreszabáshoz (backlog, lásd `crm_projekt.md` 8. szekció)

Rob kifejezte, hogy admin-ként (ő) nagyon széles szabadságot szeretne a testreszabásban — "mint egy profi CRM-ben a fejlesztőnek vagy az adminnak". Ez két konkrét, később megvalósítandó ötletben csapódott le:

1. **Mezőnevek/címkék admin általi átírása** — nem csak fordítás, hanem szabad átnevezés bármely mezőhöz.
2. **Angol (US) nyelvi változat választhatósága** — alapértelmezésként a nemzetközileg jól bevált CRM-terminológiát (Lead, Deal, Pipeline, Contact stb.) érdemes használni, de admin-ként ezek is felülírhatók legyenek.

Ez a két pont túlmutat a jelen dokumentum "sima fordítás" fókuszán — egy jövőbeli, admin-felületen szerkeszthető címke-rendszert igényelne (hasonlóan a `custom_field_definitions.label`-hez, csak a RENDSZER saját mezőire is kiterjesztve, nem csak az egyedi mezőkre). Nem MVP-blokkoló, később építendő.

## 3. Formátumok (dátum, pénznem, szám)

- Dátum-formázás Laravel Carbon-nal, `locale`-alapú (`Carbon::setLocale()`), hogy magyar felhasználónál `2026.07.25.`, más locale-nál a helyi konvenció szerint jelenjen meg.
- Pénznem: a `deals.currency` mező (lásd `adatmodell.md`) már accountonként/dealenként eltérő pénznemet enged (alapértelmezett HUF) — ez már a séma szintjén elő van készítve.

## 4. Mit NEM kell most eldönteni

- Pontosan mely nyelvekre fordítjuk le a felületet — ez üzleti döntés, amikor a SaaS-fázis konkrét célpiaca kirajzolódik. Nem MVP-kérdés.

## 5. Kapcsolódó dokumentumok

- [`adatmodell.md`](adatmodell.md) — `locale` mezők.
- [`ertesitesek-terv.md`](ertesitesek-terv.md) — e-mail sablonok, amik érintettek lesznek.
