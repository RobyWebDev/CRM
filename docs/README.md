# docs/ — tartalomjegyzék és indítókalauz

> Ez a fájl köti össze a `docs/` mappa minden tervdokumentumát egyetlen végrehajtható checklistté — arra a napra, amikor Rob gépén elkészül a Laragon és elindul a tényleges Laravel-fejlesztés (2. fázis). Addig ez a mappa a projekt "tervrajza".
> Utolsó frissítés: 2026-07-25.

## Végrehajtási sorrend Laravel-indításkor

1. **Környezet:** Laragon telepítve, D: meghajtóra (lásd `crm_projekt.md` 10. szekció).
2. **Projekt-létrehozás + csomagok:** [`csomag-terv.md`](csomag-terv.md) — `composer create-project`, majd a listázott csomagok.
3. **Adatbázis:** MySQL adatbázis létrehozása, `.env` beállítása.
4. **Váz legenerálása:** [`mappastruktura-terv.md`](mappastruktura-terv.md) — kész `artisan make:...` parancssor, [`schema.sql`](schema.sql) alapján kitöltött migrációk ([`adatmodell.md`](adatmodell.md) a magyarázat).
5. **Migrálás + seedelés:** [`seeder-terv.md`](seeder-terv.md) — Rob accountja, pipeline-sablonok, teszt-personák.
6. **API-réteg:** [`api-tervek.md`](api-tervek.md) — route-ok/kontrollerek a mappastruktúra szerint.
7. **Jogosultságok:** [`jogosultsagok-terv.md`](jogosultsagok-terv.md) — Policy-k.
8. **Kiegészítő funkciók:** [`gdpr-terv.md`](gdpr-terv.md), [`ertesitesek-terv.md`](ertesitesek-terv.md), [`csv-import-terv.md`](csv-import-terv.md).
9. **Admin-felület** (ha ráér az MVP-ben): [`admin-felulet-terv.md`](admin-felulet-terv.md).
10. **Tesztek:** [`teszterv.md`](teszterv.md) — Pest, [`teszt-personak.md`](teszt-personak.md) mindhárom profiljával.
11. **Élesítéskor (később):** [`deployment-terv.md`](deployment-terv.md), [`coach-kereso-integracio.md`](coach-kereso-integracio.md) (csak 6. fázisban).

## Még validálásra vár Robbal (nem technikai, hanem tartalmi kérdés)

- [`pipeline-sablonok.md`](pipeline-sablonok.md) — a lépések Rob tényleges munkafolyamatát kell, hogy tükrözzék.
- `crm_projekt.md` 7. szekció — nyitott kérdések (ajánlatkészítő/szerződéskészítő modul állapota, számlázás iránya).

## Fejlesztői vs. felhasználói dokumentáció

A `crm_projekt.md` 3. szekció "Dokumentáció-struktúra" elve két ágat ír elő: ez a mappa jelenleg a **fejlesztői/AI-ág**. A **felhasználói súgó/onboarding-ág** még nem indult el — csak akkor van értelme megírni, ha már léteznek a tényleges felületi képernyők, amikre hivatkozhat.
