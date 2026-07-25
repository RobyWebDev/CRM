# Teszt-personák

> A `crm_projekt.md` 3. szekció "Teszt-personák" elvének kifejtése: mivel a rendszer univerzális, a tesztelés több, egymástól eltérő szakmai profillal történik — nem csak Rob saját use case-ével —, hogy a rugalmasság valóban mindenkinek működjön. A 3. persona szándékosan **teljesen más jellegű** szakma (fizikai kézműves munka), hogy stressztesztelje a "bármilyen szakma kódolás nélkül" elvárást ne csak irodai/tanácsadói profilokon.
> Utolsó frissítés: 2026-07-25.

## 1. Persona: Rado Róbert — Coach (valós, elsődleges felhasználó)

- **Szolgáltatás-típus:** Coaching
- **Jellemző folyamat:** lásd [`pipeline-sablonok.md`](pipeline-sablonok.md) 1. pont (érdeklődés → konzultáció → 105 kérdéses felmérés → ajánlat → szerződés → ülések → lezárás).
- **Egyedi mezők:** felmérés pontszám, felmérés kitöltés dátuma, coaching típusa.
- **Mit tesztel:** ez az MVP elsődleges, valós use case-e — minden alapfunkciónak (kontaktok, egy pipeline, projektek, feladatok) ezen kell először működnie.

## 2. Persona: Kovács Anna — freelance webdesigner (kitalált)

- **Szolgáltatás-típus:** Webdesign
- **Jellemző folyamat:** lásd `pipeline-sablonok.md` 3. pont (briefing → árajánlat → design → fejlesztés → élesítés → utókövetés).
- **Egyedi mezők:** domain név, hosting szolgáltató, oldalak száma, CMS/technológia.
- **Mit tesztel:** egy második, párhuzamos szolgáltatás-típus és pipeline egyidejű létezését ugyanazon rendszerben, eltérő egyedi mezőkkel — igazolja, hogy két szakma nem "keveredik össze" (pl. Anna kontaktjain nem jelenik meg a "felmérés pontszám" mező, Rob kontaktjain nem jelenik meg a "domain név").

## 3. Persona: Szabó Márk — egyéni asztalos/bútorkészítő mester (kitalált, szándékosan más jellegű szakma)

- **Szolgáltatás-típus:** Egyedi bútorkészítés (fizikai kézműves munka, NEM tanácsadás/digitális szolgáltatás — ez a lényeges kontraszt a másik két personával szemben)
- **Jellemző folyamat (piszkozat):**
  1. Érdeklődés (telefonos/személyes)
  2. Helyszíni felmérés (méretek, igények)
  3. Tervrajz/látványterv elkészítve
  4. Árajánlat kiküldve
  5. Előleg befizetve, megrendelés visszaigazolva
  6. Anyagbeszerzés
  7. Gyártás folyamatban
  8. Helyszíni beépítés/szállítás
  9. Átadás, végszámla
  10. Lezárva — sikeres / nem lett ügyfél
- **Egyedi mezők:** helyszín címe (text), anyagválasztás (select: tölgy / bükk / MDF / egyéb), előleg összege (number), tervrajz linkje (url).
- **Mit tesztel:** ez a persona bizonyítja, hogy a rendszer **nem csak "coach-szerű" tanácsadói/digitális szolgáltatásokra** működik, hanem fizikai terméket előállító, több hetes gyártási folyamattal járó szakmákra is — ahol a "pipeline" nem értékesítési tölcsér, hanem gyártási munkafolyamat. Ha ez a persona zökkenőmentesen beállítható a `service_types` + `pipelines` + `custom_field_definitions` hármassal, az erős bizonyíték arra, hogy az architektúra tényleg univerzális, nem csak a bemutatott 5 példa-szakmára szabott.

## Hogyan használjuk ezeket tesztelés közben

- MVP kész funkcióit (kontakt létrehozás, pipeline mozgatás, egyedi mező megjelenítés, feladat/jegyzet hozzáadás) mindhárom personával végig kell futtatni, nem csak Robén.
- Ha bármelyik funkció csak Rob (coaching) esetére működik jól, és a másik kettőnél sérül/hiányzik valami, az architekturális hibára utal — vissza kell menni az `adatmodell.md`/`architektura.md` tervhez.
- Automatizált teszteknél (Pest/PHPUnit, lásd `crm_projekt.md` 3. szekció) érdemes legalább egy tesztesetet mindhárom personával lefuttatni a kritikus funkciókra (pl. account-elkülönítés, custom field megjelenítés).

## Kapcsolódó dokumentumok

- [`pipeline-sablonok.md`](pipeline-sablonok.md)
- [`adatmodell.md`](adatmodell.md)
