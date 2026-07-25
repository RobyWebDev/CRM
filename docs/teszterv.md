# Tesztterv — automatizált alaptesztek

> A `crm_projekt.md` 3. szekció "Automatizált alapteszt" elvének kifejtése: kritikus funkciókhoz (pl. account-elkülönítés) automata teszt, hogy jövőbeli módosítás ne törjön el meglévő működést. Eszköz: **Pest** (lásd `csomag-terv.md`).
> Ez a fájl a legfontosabb tesztesetek listáját adja — nem a tényleges teszt-kódot (az a Laravel-projekten belül készül majd el).
> Utolsó frissítés: 2026-07-25.

## 1. Legkritikusabb: tenant-elkülönítés (account-scope)

Ez a rendszer egyetlen legfontosabb biztonsági garanciája — ha ez elromlik, egyik account látja a másikét.

- Két különböző accounthoz tartozó kontakt/deal/projekt létrehozása után: A account bejelentkezett usereként lekérdezve **soha ne** jöjjön vissza B account rekordja (sem listázásnál, sem közvetlen ID-s lekérdezésnél — utóbbi 404-et adjon, ne 403-at, hogy még a létezést se áruljuk el).
- Új rekord létrehozásakor automatikusan a bejelentkezett user `account_id`-ja kerüljön be, akkor is, ha valaki megpróbálna másik `account_id`-t küldeni a kérésben (ezt a szervernek felül kell írnia, nem szabad megbízni a kliens-oldali inputban).
- `super_admin` usernek viszont látnia kell mindkét account adatát (lásd `jogosultsagok-terv.md`).

## 2. Jogosultsági mátrix (owner/admin/member)

- `member` szerepkörű user ne tudjon másik user által létrehozott kontaktot módosítani/törölni (lásd `jogosultsagok-terv.md` mátrix).
- `member` ne érje el a "Beállítások" (service_types/pipelines/custom_field_definitions) végpontokat — 403.
- `owner` mindent elérjen, beleértve a userkezelést.

## 3. Egyedi mezők (`custom_field_definitions`)

- Egy `select` típusú egyedi mezőnél csak a definiált `options` közül fogadjon el értéket a mentés — érvénytelen érték validációs hibát adjon.
- Egy `service_type_id`-hoz kötött egyedi mező NE jelenjen meg egy másik szolgáltatás-típusú kontakt/deal szerkesztésekor.
- Kötelező (`is_required`) mező hiánya validációs hibát adjon mentéskor.

## 4. Pipeline / esemény-hook rendszer

- Egy deal áthelyezése egy `is_won_stage = true` lépésre kiváltsa a `DealStageChanged` eseményt, ami automatikusan létrehoz egy `projects` rekordot (lásd `architektura.md` 5. pont) — ez a rendszer egyik legfontosabb "varázslat", mindenképpen tesztelendő.
- Egy pipeline_stage törlése, amin még vannak aktív dealek, ne engedélyezett legyen (vagy kényszerítse a dealek áthelyezését előbb).

## 5. GDPR

- Kontakt soft delete után azonnal ne jelenjen meg listázásnál/keresésnél.
- A megőrzési idő (javaslat 30 nap, lásd `gdpr-terv.md`) lejárta után az ütemezett job anonimizálja a személyes mezőket, de a kapcsolódó `deals`/`projects` rekord megmarad.
- Az export végpont (`GET /api/v1/contacts/{id}/export`) tartalmazza a kontakt minden mezőjét + a hozzá kapcsolódó jegyzeteket/feladatokat/dokumentumokat.

## 6. Univerzalitás — a teszt-personákkal

- A `teszt-personak.md` mindhárom profiljával (Rob/coach, Anna/webdesigner, Márk/asztalos) le kell futtatni legalább egy alap "happy path" tesztet (kontakt létrehozás → deal a saját pipeline-jukon → egyedi mezők helyesen jelennek meg) — ha bármelyiknél eltér a viselkedés a másik kettőtől valami architekturális okból, az hibára utal.

## 7. Kapcsolódó dokumentumok

- [`jogosultsagok-terv.md`](jogosultsagok-terv.md)
- [`gdpr-terv.md`](gdpr-terv.md)
- [`architektura.md`](architektura.md)
- [`teszt-personak.md`](teszt-personak.md)
