# Laravel csomag- és függőségterv

> Cél: mire elkészül a Laragon-telepítés, legyen kész lista arról, mit kell telepíteni — ne kelljen menet közben mérlegelni. Minden tétel ingyenes/nyílt forráskódú (Költség-elv, `crm_projekt.md` 3. szekció).
> Utolsó frissítés: 2026-07-25.

## 1. Projekt-létrehozás

```
composer create-project laravel/laravel crm
```

(vagy `laravel new crm`, ha a Laravel telepítő is fel van téve — mindegy melyik, a végeredmény ugyanaz).

## 2. Éles/futásidejű Composer-csomagok

| Csomag | Célja | Kapcsolódó terv |
|---|---|---|
| `laravel/sanctum` | API-token hitelesítés (account API-kulcsok + belső AJAX-session) | `api-tervek.md` |
| `spatie/laravel-activitylog` | `activity_log` audit napló | `adatmodell.md` |
| `spatie/laravel-permission` *(mérlegelendő)* | szerepkör/jogosultság-kezelés — VAGY egyszerű saját Policy-k, ha a 3 szerepkör (owner/admin/member) elég egyszerű ahhoz, hogy ne kelljen külön csomag. **Javaslat: MVP-ben saját Policy-k, a csomag csak akkor kerüljön be, ha a jogosultsági logika bonyolultabbá válik (pl. finomabb, egyedi jogkör-kombinációk).** | `jogosultsagok-terv.md` |
| `maatwebsite/excel` | CSV/Excel import | `csv-import-terv.md` |
| `laravel/scout` *(későbbi fázis)* | ha keresés-teljesítmény indokolja (MVP-ben a MySQL `LIKE`/index is elég) | — |

## 3. Fejlesztői (dev-only) Composer-csomagok

| Csomag | Célja |
|---|---|
| `pestphp/pest` + `pestphp/pest-plugin-laravel` | automatizált tesztek (lásd `teszterv.md`) |
| `knuckleswtf/scribe` | API-dokumentáció automatikus generálása |
| `barryvdh/laravel-debugbar` | fejlesztés közbeni hibakeresés (SQL-lekérdezések, teljesítmény) |
| `laravel/pint` | kódformázás (opcionális, de olcsó módja a konzisztens kódstílusnak) |

## 4. Frontend

A `crm_projekt.md` 2. szekció döntése szerint MVP-hez **szerver-oldali Blade sablonok**, nincs SPA/React. Ehhez ajánlott, könnyű kiegészítők:

- **Tailwind CSS** (ingyenes, nyílt forráskódú) — gyors, reszponzív (mobilbarát) UI-hoz, Laravel-lel jól integrálható (`laravel/breeze` scaffolding is Tailwindet használ alapból).
- **Alpine.js** (ingyenes, ~15kB) — apró interaktivitásokhoz (pl. dropdown, modal, pipeline drag-and-drop előkészítés) React/Vue nélkül, hogy a "nincs külön SPA" elv ne sérüljön.
- **Laravel Breeze** *(mérlegelendő induló csomagnak)* — kész, egyszerű auth-scaffolding (regisztráció, bejelentkezés, jelszó-reset Blade nézetekkel) — időt spórol az alapok megírásán, utólag szabadon testreszabható.

## 5. Mit NEM viszünk be induláskor

- `stancl/tenancy` — MVP-ben az egyszerű `account_id`-szűrés elég (lásd `architektura.md` 3. pont), ez a csomag csak akkor indokolt, ha a terhelés/multi-tenant igény ezt kikényszeríti.
- Fizetési csomag (Stripe/Barion SDK) — csak a 6. fázisban (SaaS réteg) kell.
- `laravel/horizon` (queue-monitorozás) — MVP-ben, alacsony terhelésnél túlzás; a queue maga (`database` driver) elég lesz kezdetben.

## 6. Kapcsolódó dokumentumok

- [`mappastruktura-terv.md`](mappastruktura-terv.md)
- [`architektura.md`](architektura.md)
