# Admin-felület terv — kódolás nélküli testreszabás felhasználói oldalról

> Ez a fájl azt írja le, MILYEN felületen keresztül tudja majd Rob (és később bármely account-tulajdonos) kódolás nélkül testreszabni a CRM-et. Az [`architektura.md`](architektura.md) 6. pontjában jelzett admin-felület részletes terve.
> Ez szöveges wireframe-leírás, nem grafikai terv — a cél, hogy amint elkezdődik a Blade-sablonok írása, legyen kész user-flow, amit implementálni lehet.
> Fázisbesorolás: MVP-ben (3. fázis) elég lehet egy egyszerű form-alapú megoldás vagy akár seeder-script; a lenti, kényelmesebb felület a 4-5. fázisban válik fontossá, amikor Rob már rendszeresen bővíti/módosítja a saját beállításait, vagy amikor több account lesz.
> Utolsó frissítés: 2026-07-25.

## 1. "Beállítások" főmenü

Egy account beállításain belül 3 fő alszekció:

```
Beállítások
├── Szolgáltatás-típusok
├── Pipeline-ok
└── Egyedi mezők
```

## 2. Szolgáltatás-típusok kezelése

**Lista nézet:** táblázat — Név, Ikon/szín, Aktív pipeline-ok száma, Aktív/inaktív kapcsoló, sorrend (drag handle).

**"Új szolgáltatás-típus" form:**
- Név (kötelező, pl. "Fotózás")
- Leírás (opcionális)
- Ikon/szín választó (egyszerű, előre definiált emoji/szín-lista)
- Mentés → azonnal létrejön a `service_types` sor, a felhasználó azonnal tovább irányítható a "hozz létre hozzá pipeline-t" lépésre.

**Törlés:** csak akkor engedélyezett, ha nincs hozzá aktív deal/projekt — különben figyelmeztetés ("előbb zárd le vagy helyezd át a kapcsolódó elemeket").

## 3. Pipeline-ok kezelése

**Lista nézet:** szolgáltatás-típusonként csoportosítva, melyik pipeline az alapértelmezett.

**Pipeline-szerkesztő (a legfontosabb no-code eszköz):**
- Fejléc: pipeline neve, melyik szolgáltatás-típushoz tartozik (vagy "általános, nem szolgáltatás-specifikus").
- Lépés-lista, sorrendben, drag-and-drop átrendezéssel (vagy MVP-ben egyszerű fel/le nyilak, ha a drag-and-drop első körben túl sok munka lenne).
- Minden lépésnél: név, szín, opcionális "nyertes lépés" / "vesztes lépés" jelölő (checkbox — ez állítja be az `is_won_stage`/`is_lost_stage` mezőt, ami az esemény-hookot vezérli).
- "+ Új lépés hozzáadása" gomb a lista végén.
- Lépés törlése: figyelmeztetés, ha vannak rajta aktív dealek — át kell mozgatni őket egy másik lépésre törlés előtt.

**Ez a felület helyettesíti a fejlesztői munkát**, amikor Rob egy új szakma pipeline-ját akarja megtervezni: a `pipeline-sablonok.md`-ben lévő piszkozatok pontosan ezen a felületen kelnének életre, ha Rob módosítani akarja őket.

## 4. Egyedi mezők kezelése

**Lista nézet:** szűrhető "melyik entitáson" (kontakt / szervezet / deal / projekt) és "melyik szolgáltatáshoz tartozik" szerint.

**"Új egyedi mező" form:**
- Melyik entitásra vonatkozik (select: Kontakt / Szervezet / Deal / Projekt)
- Melyik szolgáltatás-típushoz tartozik (select, vagy "minden szolgáltatásra érvényes")
- Mező címe (amit a felhasználó lát, pl. "Felmérés pontszám")
- Mező kulcsa (technikai azonosító, automatikusan generálva a címből, pl. `felmeres_pontszam`)
- Mező típusa (select: szöveg / hosszú szöveg / szám / dátum / igen-nem / legördülő lista / többválasztós lista / link)
- Ha legördülő/többválasztós: a választható értékek listája (egyszerű, soronkénti beviteli mező)
- Kötelező-e kitölteni

**Hatás:** mentés után a mező azonnal megjelenik a megfelelő entitás szerkesztő űrlapján (kontakt/deal/projekt), a `custom_fields` JSON oszlopban tárolva. **Ehhez nem kell semmilyen fejlesztői munka vagy deploy.**

## 5. Hogyan függ ez össze a "bármilyen szakma kódolás nélkül" elvárással

Ez a 3 admin-képernyő együtt adja a mechanizmust, amivel Rob (vagy egy jövőbeli tenant) teljesen új szakmai profilt hozhat létre:

1. Szolgáltatás-típus létrehozása (2. pont)
2. Hozzá tartozó pipeline + lépések megrajzolása (3. pont)
3. Szükség szerinti egyedi mezők felvétele (4. pont)

Ezt a folyamatot dokumentálja konkrét példával a [`pipeline-sablonok.md`](pipeline-sablonok.md) "Hogyan bővül ez új szakmával" szakasza.

## 6. MVP-egyszerűsítés (ha a teljes admin-UI túl nagy falat lenne elsőre)

Ha a 3. fázisban (MVP) ez a felület még nem fér bele az időbe, ideiglenes megoldásként elég lehet:
- Egy Laravel Artisan konzol-parancs (`php artisan crm:seed-service-type`), ami interaktívan kérdez rá az adatokra és beírja a táblákba.
- Vagy közvetlen adatbevitel egy adatbázis-kezelő felületen (pl. phpMyAdmin, ami már fent van `D:\phpMyAdmin-5.1.1-all-languages` alatt) — ez technikailag ugyanúgy kódolás nélküli, csak nem felhasználóbarát.

A cél középtávon (4-5. fázis) a fenti, tényleges webes admin-felület, hogy Rob ne adatbázis-szinten, hanem a CRM-en belülről tudjon szakmát bővíteni.

## Kapcsolódó dokumentumok

- [`architektura.md`](architektura.md)
- [`adatmodell.md`](adatmodell.md)
- [`api-tervek.md`](api-tervek.md)
- [`pipeline-sablonok.md`](pipeline-sablonok.md)
