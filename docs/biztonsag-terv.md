# Biztonsági terv

> Kiegészíti a `crm_projekt.md` 3. szekció idevágó pontjait (Titkok kezelése, Jogosultságkezelés, Környezetek), egy helyen összegyűjtve minden alap biztonsági intézkedést, amit a Laravel-projektnek induláskor be kell építenie. Ezek nagyrészt Laravel-alapkonvenciók — a cél, hogy tudatosan, kihagyás nélkül kerüljenek be, ne menet közben derüljön ki a hiányuk.
> Utolsó frissítés: 2026-07-25.

## 1. Bemeneti adatok kezelése

- **SQL-injection:** Eloquent ORM + parameterizált lekérdezések alapból védenek — szabály: soha nincs nyers, string-összefűzött SQL felhasználói inputtal (`DB::raw` csak paraméterezve, ha egyáltalán szükséges).
- **XSS:** Blade `{{ }}` szintaxis automatikusan escape-eli a kimenetet — szabály: `{!! !!}` (nem escape-elt kimenet) csak akkor használható, ha az adat forrása biztosan nem felhasználói input (pl. saját generált HTML).
- **Tömeges kitöltés (mass assignment):** minden Eloquent modellen explicit `$fillable` lista, SOHA `$guarded = []`.
- **Validáció:** minden bemenet Form Request osztályokon keresztül validálva (lásd `mappastruktura-terv.md` `Http/Requests`), a `custom_field_definitions` mezőknél a mezőtípusnak megfelelő dinamikus validációs szabállyal (pl. `select` típusnál `in:` szabály a definiált `options` listára).

## 2. Hitelesítés és jogosultság

- Jelszó-tárolás: Laravel alapértelmezett bcrypt/argon2 hash — soha nincs egyedi/gyengébb hashelés.
- Jelszó-minimumkövetelmény: Laravel `Password::min(8)->letters()->numbers()` szabály regisztrációnál/jelszóváltásnál.
- **Rate limiting bejelentkezésnél:** Laravel beépített `throttle` middleware a login route-on (pl. 5 próbálkozás/perc), hogy brute-force támadás ne legyen egyszerű.
- API rate limiting: `api_keys`-alapú kéréseknél is throttle-middleware (pl. 60 kérés/perc kulcsonként), hogy egy hibás/rosszindulatú integráció ne terhelje túl a szervert.
- Minden jogosultsági döntés a `jogosultsagok-terv.md`-ben leírt Policy-kon megy keresztül — kontrollerben SOHA nincs kézzel írt `if ($user->role === 'owner')` elszórva, hanem `$this->authorize(...)` hívás.

## 3. Tenant-elkülönítés (ismételt kiemelés — ez a legkritikusabb pont)

- A `BelongsToAccount` trait (lásd `mappastruktura-terv.md` 3. pont) minden tenant-scope-olt modellen kötelező.
- Ehhez tartozik a `teszterv.md` 1. pontjában felsorolt automatizált teszt — ez az egyetlen olyan terület, ahol egy hiba katasztrofális (más ügyfél adatának kiszivárgása) lenne, ezért itt a tesztlefedettség nem elhagyható.

## 4. Titkok és konfiguráció

- `.env` soha nem kerül verziókezelésbe (már beállítva, lásd `.gitignore`).
- API-kulcsok (`api_keys.token_hash`) csak hash-elve tárolva, a nyers kulcs csak létrehozáskor jelenik meg egyszer (lásd `api-tervek.md`).
- Webhook-titkok (coach-kereső HMAC kulcs, lásd `coach-kereso-integracio.md`) szintén `.env`-ben.
- `integrations.config` mező alkalmazás-szinten titkosítva (Laravel `encrypted` cast) tárolva az adatbázisban is — dupla védelem, ha az adatbázis valahogy kiszivárogna.

## 5. Szállítási réteg és fejlécek

- HTTPS kikényszerítése élesben (`APP_ENV=production` esetén Laravel middleware-rel átirányítás HTTP→HTTPS) — a `deployment-terv.md` Let's Encrypt SSL-jével együtt.
- Alap biztonsági HTTP-fejlécek (pl. `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`) — Laravel csomag (pl. `spatie/laravel-csp` vagy kézzel middleware-ben) hozzáadható, nem MVP-blokkoló, de olcsó és hasznos.
- CSRF-védelem: Laravel beépített, webes formoknál (Blade) alapból bekapcsolva — nem szabad kikapcsolni.

## 6. Naplózás és incidens-kezelés

- Az `activity_log` (audit napló) minden érzékeny művelethez (bejelentkezés, jogosultság-változás, adat-export, törlés) bejegyzést készít.
- Sikertelen bejelentkezési kísérletek naplózása, hogy szükség esetén visszakövethető legyen egy esetleges brute-force próbálkozás.

## 7. Kétfaktoros hitelesítés (2FA) — jövőbeli finomítás

MVP-ben (1 user = Rob, lokális gép) nem prioritás. Multi-user/SaaS fázisban (5-6.) érdemes bevezetni legalább `owner`/`admin` szerepkörre — Laravel Fortify ingyenes, kész megoldást ad erre, ha eljön az ideje.

## 8. Kapcsolódó dokumentumok

- [`jogosultsagok-terv.md`](jogosultsagok-terv.md)
- [`mappastruktura-terv.md`](mappastruktura-terv.md)
- [`teszterv.md`](teszterv.md)
- [`deployment-terv.md`](deployment-terv.md)
- [`coach-kereso-integracio.md`](coach-kereso-integracio.md)
