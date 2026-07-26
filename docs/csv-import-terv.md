# CSV-import — terv

> A `crm_projekt.md` 3. szekció "CSV-import" alapelvének kifejtése: kontaktok/adatok tömeges bevitele meglévő Excel/Sheet listákból.
> Utolsó frissítés: 2026-07-25.

## 0. Állapot (2026-07-26) — MEGVALÓSÍTVA, tudatos MVP-egyszerűsítésekkel

A funkció élesben megvan (`/contacts/import`, három lépés: feltöltés → mezőtérképezés+előnézet → import+riport), a lenti terv 1-3. pontja gyakorlatilag pontosan úgy valósult meg, ahogy tervezve volt (UTF-8/Windows-1250 automatikus felismerés+konverzió, vessző/pontosvessző auto-detektálás, fejléc-alapú okos mezőtérképezés-tipp, előnézet, duplikátum-kihagyás e-mail alapján).

**Két tudatos eltérés a 4. pont technikai javaslatától:**

- **Nincs `maatwebsite/excel` csomag** — natív PHP `str_getcsv()`-vel dolgozunk (`App\Support\ContactCsvImporter`), hogy ne kelljen új Composer-függőséget bevezetni egy olyan funkcióhoz, aminek a neve is "CSV" (nem XLSX) — ha valaha tényleges Excel-fájl (.xlsx) feltöltés is kellene, ez a pont bővítendő.
- **Nincs Queue Job** — szinkron feldolgozás egy kérésen belül, mert Rob egyfelhasználós, feltehetően néhány száz soros listáival ez bőven elég gyors, és nem igényel folyamatosan futó queue-workert az üzemeltetéshez.

Egyedi mezőkre (`custom_field_definitions`, `entity_type=contact`) is lehet mappelni egy CSV-oszlopot. Tesztelve: `tests/Feature/ContactCsvImportTest.php`.

## 1. Cél és elsődleges entitás

MVP-ben a legfontosabb (és valószínűleg egyetlen szükséges) import-célpont a **kontaktok** (`contacts`), mivel Robnak feltehetően van egy meglévő Excel/Sheet listája az ügyfelekről/érdeklődőkről. Szervezetek (`organizations`) importja hasonló mintát követhet, de csak akkor kell megépíteni, ha ténylegesen van rá adat.

## 2. Fájlformátum-elvárások

- **Karakterkódolás:** UTF-8 kötelező (lásd `crm_projekt.md` 3. szekció "Karakterkódolás" elve) — magyar ékezetek miatt. Ha a feltöltött fájl más kódolású (pl. Windows-1250, tipikus régi Excel-exportnál), a rendszer automatikusan detektálja és konvertálja, vagy egyértelmű hibaüzenettel jelzi, hogy a fájl kódolása nem felismerhető.
- **Elválasztó:** vessző vagy pontosvessző — magyar Excel gyakran pontosvesszőt használ tizedesvessző miatt; a rendszer mindkettőt támogatja, auto-detektálással.
- **Fejléc-sor:** kötelező első sorként, ez adja az oszlopneveket a mezőtérképezéshez.

## 3. Folyamat (felhasználói oldalról)

1. **Fájl feltöltése** — a felhasználó kiválasztja a CSV-t.
2. **Mezőtérképezés (mapping) képernyő** — a rendszer megmutatja a CSV oszlopfejléceit, és mindegyikhez egy legördülőt ad, ahol a felhasználó kiválasztja, melyik `contacts` mezőre (vagy melyik `custom_field_definitions` egyedi mezőre) illesztse. Nem kötelező minden oszlopot leképezni — a le nem képezett oszlopok kimaradnak.
3. **Előnézet (dry-run)** — az első néhány sor (pl. 5) megjelenik a leképezés alapján feldolgozva, hogy a felhasználó ellenőrizhesse, jó-e a mapping, mielőtt ténylegesen elindítja az importot.
4. **Duplikátum-kezelés** — ha egy beérkező sor e-mail címe már létezik a rendszerben (ugyanazon accounton belül), a felhasználó választhat: kihagyás / felülírás / új rekordként hozzáadás mindenképp. Alapértelmezés: kihagyás + jelzés a hibalistán.
5. **Import futtatása** — háttérfolyamatként (Laravel Queue Job), hogy nagyobb fájlnál se blokkolja a felületet.
6. **Eredmény-riport** — hány sor importálódott sikeresen, hány lett kihagyva/hibás, és soronkénti hibaüzenet-lista letölthető formában (pl. "12. sor: hiányzó e-mail cím").

## 4. Technikai megvalósítás (javaslat)

- **Csomag:** `maatwebsite/excel` (ingyenes, nyílt forráskódú, jól karbantartott Laravel-csomag CSV/Excel importhoz) — megfelel a Költség-elvnek.
- Az import egy `ImportContactsJob` (queued job) formájában fut, soronként validálva a Laravel Validator-ral.
- Az egyedi mezőkre (`custom_field_definitions`) leképezett oszlopok a `contacts.custom_fields` JSON oszlopba kerülnek, a mező típusának megfelelő validálással (pl. `select` típusnál csak a definiált `options` közül fogadható el érték).
- Minden importált kontakthoz `source = "csv_import"` kerül beállításra (nyomon követhetőség).

## 5. Kapcsolódó dokumentumok

- [`adatmodell.md`](adatmodell.md) — `contacts` és `custom_field_definitions` táblák.
- [`api-tervek.md`](api-tervek.md) — `POST /api/v1/contacts/import` végpont.
