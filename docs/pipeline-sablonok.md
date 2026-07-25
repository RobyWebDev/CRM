# Pipeline-sablon javaslatok (piszkozat — Rob validálására vár)

> **FONTOS:** ez a fájl **javaslat**, nem végleges döntés. A tényleges lépéseket csak Rob tudja pontosan megmondani, mert a saját munkafolyamatát tükrözik. Ez a piszkozat általános szakmai gyakorlat alapján készült, kiindulópontnak — validáld, módosítsd, vagy dobd el bármelyiket.
>
> Technikailag ez a modell nem "hardcode": mindegyik lenti pipeline a `pipelines` + `pipeline_stages` táblák sorai lesznek (lásd [`adatmodell.md`](adatmodell.md)), tehát bármikor, kód nélkül szerkeszthetők/átnevezhetők/átrendezhetők lesznek a felületen.
>
> Kapcsolódó nyitott kérdés: `crm_projekt.md` 7. szekció — "Pontos pipeline-lépések szolgáltatásonként".

---

## 1. Coaching

*Feltételezés: egyéni coaching-folyamat, a coachlab.hu 105 kérdéses felmérő rendszerével.*

| # | Lépés | Megjegyzés |
|---|---|---|
| 1 | Érdeklődés / első kapcsolatfelvétel | weboldal űrlap, ajánlás, telefon stb. |
| 2 | Ingyenes konzultáció egyeztetve | időpont lefoglalva |
| 3 | Ingyenes konzultáció megtörtént | |
| 4 | Felmérés kiküldve | 105 kérdéses eszköz |
| 5 | Felmérés kiértékelve | |
| 6 | Ajánlat kiküldve | |
| 7 | Szerződés aláírva | |
| 8 | Coaching-ülések folyamatban | maga a megbízás — ez már inkább `projects`-be tartozik, lásd lent |
| 9 | Lezárva — sikeres (won) | |
| 9b | Lezárva — nem lett ügyfél (lost) | |

*Megjegyzés: a 8. lépéstől kezdve érdemes lehet a `deals`-ből egy `projects` rekordot nyitni (a "won" pillanatban, az esemény-hook automatikusan létrehozza — lásd `architektura.md` 5. pont), és onnantól az ülések/feladatok a projekten belül követve.*

**Javasolt egyedi mezők (`custom_field_definitions`, entity_type=contact vagy deal):**
- Felmérés pontszám (number)
- Felmérés kitöltés dátuma (date)
- Coaching típusa (select: életvezetési / vezetői / karrier / egyéb)

---

## 2. Szervezetfejlesztés

*Feltételezés: hosszabb, több döntéshozós B2B értékesítési ciklus.*

| # | Lépés | Megjegyzés |
|---|---|---|
| 1 | Érdeklődés / megkeresés | |
| 2 | Igényfelmérő beszélgetés | döntéshozóval |
| 3 | Szervezeti helyzetfelmérés / diagnózis | |
| 4 | Ajánlat/koncepció kidolgozása | |
| 5 | Ajánlat prezentálása | |
| 6 | Szerződéskötés | |
| 7 | Program megvalósítása | → `projects` |
| 8 | Zárás és eredményértékelés | |
| 9 | Lezárva — sikeres / nem lett ügyfél | won / lost |

**Javasolt egyedi mezők:**
- Szervezet mérete (létszám) (number, entity_type=organization)
- Döntéshozó neve/pozíciója (text, entity_type=contact)
- Program időtartama (hónapban) (number, entity_type=deal)

---

## 3. Webdesign

| # | Lépés | Megjegyzés |
|---|---|---|
| 1 | Érdeklődés | |
| 2 | Igényfelmérés (briefing) | |
| 3 | Árajánlat kiküldve | |
| 4 | Ajánlat elfogadva / szerződés | |
| 5 | Design fázis | → `projects` |
| 6 | Fejlesztés fázis | |
| 7 | Ügyfél review / módosítások | |
| 8 | Élesítés (launch) | |
| 9 | Utókövetés / karbantartási szerződés | opcionális, külön deal is lehet |
| 10 | Lezárva — sikeres / nem lett ügyfél | won / lost |

**Javasolt egyedi mezők:**
- Domain név (text, entity_type=project)
- Tárhely/hosting szolgáltató (text, entity_type=project)
- Oldalak száma (number, entity_type=deal)
- CMS/technológia (select: WordPress / egyedi / egyéb)

---

## 4. Marketing

| # | Lépés | Megjegyzés |
|---|---|---|
| 1 | Érdeklődés | |
| 2 | Igényfelmérés / marketing audit | |
| 3 | Stratégia/ajánlat kidolgozása | |
| 4 | Ajánlat kiküldve | |
| 5 | Szerződéskötés | |
| 6 | Kampány/tevékenység beindítása | → `projects` |
| 7 | Folyamatos kezelés / riportolás | havi ismétlődő munka |
| 8 | Megújítás vagy lezárás | |
| 9 | Lezárva — sikeres / nem lett ügyfél | won / lost |

**Javasolt egyedi mezők:**
- Havi büdzsé (number, entity_type=deal)
- Csatornák (multiselect: Facebook / Google Ads / Instagram / e-mail / egyéb)

---

## 5. SEO

| # | Lépés | Megjegyzés |
|---|---|---|
| 1 | Érdeklődés | |
| 2 | SEO audit elkészítése | |
| 3 | Audit prezentálása + ajánlat | |
| 4 | Szerződéskötés | |
| 5 | Onpage/technikai optimalizálás | → `projects` |
| 6 | Tartalom/linképítés fázis | |
| 7 | Havi riportolás / folyamatos munka | |
| 8 | Megújítás vagy lezárás | |
| 9 | Lezárva — sikeres / nem lett ügyfél | won / lost |

**Javasolt egyedi mezők:**
- Célkulcsszavak (textarea, entity_type=project)
- Havi riport gyakorisága (select: heti / havi / negyedéves)
- Induló organikus forgalom (number, entity_type=deal)

---

## Hogyan bővül ez új szakmával (példa)

Ha később pl. egy fényképész account jön létre, a fenti mintát követve, kód nélkül:

1. `service_types`: "Fotózás"
2. `pipelines`: "Fotózás megrendelés folyamat", `service_type_id` = Fotózás
3. `pipeline_stages`: Érdeklődés → Egyeztetés/időpont → Fotózás megtörtént → Válogatás/retusálás → Átadás → Lezárva
4. `custom_field_definitions`: "Fotózás helyszíne" (text), "Képek száma" (number), "Csomag típusa" (select)

Semmilyen fejlesztői munka nem kell hozzá — csak adatbevitel az admin felületen (vagy induláskor egy seeder scripttel).

---

## Kapcsolódó dokumentumok

- [`adatmodell.md`](adatmodell.md)
- [`schema.sql`](schema.sql)
- [`architektura.md`](architektura.md)
