# MiniCRM-inspiráció — alapos kutatás és átvételi javaslatok

> Rob kérése (2026-07-25): "a MiniCRM-nek nagyon alaposan nézz utána, mert nagyon sok hasznos dolgot vehetnénk át belőle... ne csak a kisvállalkozókra [korlátozzuk], bár itt az elején legtöbb az lesz az ügyfelek közül, de ne korlátozzunk arra."
>
> Ez a dokumentum a MiniCRM (`minicrm.hu`) **valós, nyilvános dokumentációjából** (`minicrm.hu/help/*`) ténylegesen lekért, összegyűjtött információn alapul — nem találgatás. Minden pont alatt jelezve van, hogy **átvehető-e kis módosítással a jelenlegi architektúránkba**, és ha igen, milyen általánosított (nem csak kisvállalkozásra szabott) formában.

---

## 1. MiniCRM termékportfólió — teljes áttekintés

A MiniCRM nem egyetlen modul, hanem egy **modulárisan bővíthető termékcsalád** — ez maga is megerősíti a mi architektúránk alapelvét (`architektura.md`, moduláris monolit). A ténylegesen létező MiniCRM-termékek:

**Alap ("fix státuszú") termékek:** Értékesítés (minősítő tölcsérrel), Klasszikus értékesítés (szabadabb folyamat), Számlázás, Megrendelés, Webshop-integráció, Szabadságkezelés.

**Testreszabható státuszú termékek:** Projektek, Projektmenedzsment, HR, Ügyfélszolgálat (helpdesk), Eseményen résztvevők, Beszállítók, Egyedi folyamatok, Belső feladatkezelő, Bejövő számlák, Eszközök (eszköznyilvántartás).

**Kiegészítő funkciók:** Marketing-automatizáció, Business Intelligence, Partnerkezelés, Folyamat-automatizáció, Ügyfél-utánkövetés, **AI Assistant** (ez utóbbi megerősíti, hogy a mi "szabályalapú insights, később AI-val bővíthető" backlog-ötletünk piaci realitás, nem csak ötlet).

**Mit jelent ez nálunk:** a mi `service_types`/`pipelines`/`custom_field_definitions` hármasunk ELVILEG már ma is lehetővé tenné, hogy Rob saját maga hozzon létre egy "Ügyfélszolgálat" vagy "Eszközök" service_type-ot — ez az univerzalitási elvünk helyességét igazolja vissza. Amire nincs még megoldásunk: a MiniCRM **fix, kész sablon-modulokat** ad (pl. kattintható "Számlázás" gomb egy teljes almodullal) — nálunk ez egyelőre "építsd fel magad service_type-ként" szintű. **Javaslat:** hosszabb távon néhány induló sablon-modul (pl. "Ügyfélszolgálat", "Eszköznyilvántartás") előre elkészíthető seederként/sablonként, amit egy account egy kattintással bekapcsolhat — ez a `docs/pipeline-sablonok.md` elvének kiterjesztése.

## 2. Adatlap (rekord) mezőtípusok — közvetlenül átvehető

A MiniCRM adatlap-mezőinek típusai és szabályai nagyon közel állnak a mi `custom_field_definitions` tervünkhöz, de van, amiben **pontosabb/gazdagabb**:

| MiniCRM mezőtípus | Jellemző | Nálunk jelenleg | Javaslat |
|---|---|---|---|
| Legördülő lista | egy érték, kizárólagos | `select` | megvan |
| Jelölőnégyzetek | több érték egyszerre | `multiselect` | megvan |
| Szöveg | max. 1024 karakter, NEM szerepelhet statisztikában | `text` | **karakterkorlát bevezetése javasolt** (validációs szabályként) |
| Szövegdoboz | max. 4096 karakter, hosszabb jegyzet | `textarea` | megvan |
| Dátum és idő | opcionális pontos időponttal | `date` | **hiányzik a "dátum + idő" altípus** — javasolt hozzáadni |
| Egész szám | nem pénzösszeg | `number` | megvan |
| **Fájlmező** | dokumentum/fotó feltöltés, max. 24MB | **HIÁNYZIK** | **új mezőtípus javasolt**: `file` — szerződések, fotók csatolása egyedi mezőként |

**Konfigurációs szabályok, amiket érdemes átvenni:**
- **"Kötelező X státusztól kezdve"** — nálunk jelenleg csak egy globális `is_required` van; a MiniCRM lépés-/státuszfüggő kötelezőséget enged (pl. "a Szerződés mező csak a Szerződéskötés lépéstől kötelező"). Ez jól illeszkedne a mi `pipeline_stage_id`-hez kötött validációhoz.
- **Csak-olvasható mezők** integrációból származó adatokhoz (nálunk ez az `integrations`/API-kulcsok rendszerrel válik majd relevánssá).
- **Mezőcsoportosítás** (színes, összecsukható dobozok, csoportonként eltérő láthatósággal felhasználói csoportonként) — ez egy jó UI-mintázat a jövőbeli admin-felület mező-szerkesztőjéhez (már szerepel a backlogban: "mezők sorrendje/láthatósága szerkeszthető felület").

## 3. Automatizáció-builder — konkrét minta a már backlogolt "ha X, akkor Y" szerkesztőhöz

A MiniCRM automatizmus-rendszere **trigger (szűrő) + akciók (lépések sorban)** felépítésű:

**Triggerek:** bármilyen szűrő-feltétel (pl. "célcsoportban lévő lead, ami X ideje nem lépett tovább").

**Elérhető akciótípusok (mind hasznos mintaként a mi jövőbeli automatizáció-szerkesztőnkhöz):**
1. E-mail az ügyfélnek (sablonból)
2. SMS az ügyfélnek (ha van telefon-integráció)
3. E-mail a munkatársnak (belső értesítés)
4. **Teendő létrehozása**, opcionális határidő-eltolással (pl. "a triggertől számított +2 nap")
5. Dokumentum generálása sablonból (PDF/Word)
6. Mezőmódosítás (adatmódosító linken keresztül)

**Konkrét, valóban használt példák a dokumentációjukból** (ezek egy az egyben átvehetők nálunk is, service_type-tól függetlenül, tehát univerzálisan):
- "Ha egy célcsoportban lévő lead X napig nem konvertál → figyelmeztető e-mail."
- "Ha egy ajánlat 4 napja nincs továbblépve → automatikus utánkövető teendő" (ezt már korábban is kiemeltem, közvetlenül ráépül a mi `stage_entered_at` mezőnkre).
- "Ha egy ügyfélszolgálati ügy túl sokáig feldolgozatlan → riasztás a support-csapatnak."

Ez a három példa mind **általánosítható mintaként** (nem csak kisvállalkozásra): idő-alapú SLA-figyelmeztetés bármilyen pipeline-ra/service_type-ra alkalmazható. **A már meglévő "Automatizáció-szerkesztő" backlog-tételünk (8. szekció, 1. pont) ezekkel a konkrét sablonokkal egészül ki.**

## 4. Riportok/Business Intelligence — konkrét mutatószám-javaslatok

A `riportok-terv.md`-ben már volt egy kezdeti ötlet (pipeline-tölcsér, win rate, bevétel szolgáltatásonként) — a MiniCRM ezt megerősíti és kiegészíti konkrét, valóban használt mutatókkal:

- **Értékesítői dashboard:** kiküldött ajánlatok összértéke, új megszerzett ügyfelek száma, megszerzett bevétel adott időszakban, **lead-vesztési arány** (ez utóbbi nálunk is könnyen számolható a `leads.status` alapján: hány % lett `unqualified`).
- **Kampányriportok:** e-mail megnyitási arány, kattintási arány, leiratkozási arány — ez csak akkor releváns nálunk, ha lesz e-mail-kampány modul (lásd 6. pont), egyelőre backlog.
- **Adatlapok száma riport:** hány rekord van egy adott állapotban, felhasználónként/csoportonként/összesen — ez a mi Dashboard-csempéink (leadek/kontaktok/dealek/projektek/retainerek száma) elve, amit már megvalósítottunk, csak state-enkénti bontás nélkül. **Javaslat:** a meglévő Dashboard bővíthető állapotonkénti bontással (pl. "12 aktív, 3 szüneteltetett projekt" egy csempén belül).
- **Riportok csak menedzser/admin szerepkörnek** — ez megerősíti a már meglévő jogosultsági tervünket (`jogosultsagok-terv.md`).

## 5. Teendők/naptár — közvetlenül átvehető minták

- **Ismétlődő teendők** — előre ütemezhető, automatikusan újra létrejövő feladatok (pl. "minden hónap 5-én emlékeztető a számlaküldésre"). Ez **közvetlenül átvehető és általánosan hasznos** (nem csak retainer-ügyfeleknél) — jelenleg a mi `tasks` táblánk nem támogat ismétlődést. **Konkrét javaslat:** egy `recurrence_rule` mező (pl. "havonta, X. napon") a Task modellhez, vagy egy külön `recurring_task_templates` tábla, ami ütemezetten (napi cronjob) új `tasks` sorokat generál — ez pontosan illik a `retainer_invoices` már meglévő mintájához (hasonló időszakos-generálás logika).
- **Naptárszinkronizáció** (Google Naptár) — a teendők külső naptárral szinkronizálva, hogy a határidő-emlékeztető ne csak a CRM-en belül, hanem a felhasználó tényleges naptárában/telefonján is megjelenjen. Backlog: Google Calendar API-integráció (később, OAuth-ot igényel).
- **Harang/emlékeztető ikon** — vizuális emlékeztető-jelzés, ami X perccel/órával a határidő előtt szól. Nálunk ez az `ertesitesek-terv.md` már tervezett Laravel Notification-rendszerével megoldható.

## 6. Marketing/címkék (tags) — közvetlenül átvehető, ÁLTALÁNOS (nem csak kisvállalkozói) minta

- **Címkék (tags):** szabadon felvehető, kontaktokhoz/szervezetekhez rendelhető, egy rekordhoz több címke is fűzhető, és **szűrőkben/automatizációkban változóként használhatók**. Ez egy klasszikus, minden CRM-méretben (kicsitől nagyig) hasznos, olcsó-megvalósítású minta — **javasolt AZONNAL bevezetni**, mert kevés munka, nagy haszon, és nem szakma-specifikus (tehát nem csak kisvállalkozásoknak, hanem bármilyen méretű/típusú felhasználásnak jó).
- **E-mail kampány modul:** sablon-szerkesztő + szűrt célcsoport + valós idejű riport. Ez egy nagyobb, önálló modul (backlog, később).
- **Automatikus lead-befogás weboldali űrlapról + Facebook Lead Ads integráció:** a weboldalra beágyazott űrlap kitöltői automatikusan `leads` rekordként kerülnek be, mezőleképezéssel. **Ez közvetlenül illeszkedik a már meglévő Leads modulunkhoz** — csak egy beérkező webhook-végpont kell hozzá (`POST /webhooks/leads` egy API-kulccsal védve, ami már elő van készítve az `api_keys`/`integrations` táblákkal). Jó jövőbeli konkrét feladat.

## 7. Telefónia — kisebb prioritású, de általánosan hasznos

Android CallLog-szinkronizáció / VoIP-integráció, ami a hívás metaadatait (név, szám, időtartam) automatikusan a megfelelő adatlapra rögzíti. Nálunk ez egy jövőbeli, integrációt igénylő funkció (backlog, alacsonyabb prioritás, mert konkrét telefónia-szolgáltatót igényelne).

## 8. Jogosultságok — megerősíti a már tervezett mátrixot

- **Korlátozott jogosultságú felhasználó** csak a saját felelősségi körébe tartozó (vagy rá nyitott teendős) rekordokat látja — ez **pontosan megegyezik** a mi `jogosultsagok-terv.md` owner/admin/member elvével, csak konkretizálja: "member csak a sajátját látja" mintaként érdemes bevezetni finomabb szabályként (jelenleg a `role` csak funkció-szintű, nem rekord-szintű korlátozást ad).
- **Felhasználói csoportok** mezőszintű láthatósággal — ez a már backlogolt "finomabb jogosultságok" tétel (8. szekció, 4. pont) konkrét megvalósítási mintája.

## 9. Összegzés — mit érdemes MOST, hamarosan megépíteni (nem csak backlogolni)

A fentiek közül ezek **kis munkával, azonnal, általánosan (nem csak kisvállalkozói szűkítéssel) beépíthetők**, ezért ebben a munkamenetben el is kezdem őket:

1. ✅ **Kontakt-adatlap bővítése** több hasznos mezővel + szabad szöveges jegyzet rögtön felvételkor (Rob konkrét kérése is volt).
2. ✅ **Címkék (tags) rendszer** kontaktokhoz/szervezetekhez — általános, olcsó, nagy haszon.
3. ✅ **Ismétlődő teendők** — általános, nem csak retainer-ügyfeleknél hasznos.

Ami **backlogban marad, később döntendő**: teljes automatizáció-szerkesztő UI, e-mail-kampány modul, webes űrlap+Facebook lead-befogás, Google Naptár-szinkronizáció, telefónia-integráció, riportok bővítése BI-szintre, fájl-melléklet egyedi mezőtípus, mezőcsoportosítás UI, rekord-szintű (nem csak szerepkör-szintű) jogosultság.

## 9b. Kiegészítő kutatási kör (2026-07-25, Rob kérése "nézz utána, ha van még átvehető ötlet")

Rob explicit kérésére még alaposabban átnéztem a MiniCRM dokumentációját, hogy nem maradt-e ki érdemi ötlet. Két új, korábban nem szereplő területet találtam:

- **Teendő-sablon (task template):** rendszeresen ismétlődő, de NEM idő-alapú (tehát nem a már megvalósított `recurrence` mezőnkkel egyező) feladatokhoz — egy admin egyszer megírja a teendő szövegét/típusát/becsült időtartamát/ellenőrzőlistáját, ezután bármelyik kolléga "3 kattintással" felveheti magának vagy delegálhatja. **Ez különbözik a mi ismétlődő teendőnktől**: az idő alapján ismétlődik automatikusan, ez itt egy **újrafelhasználható sablon**, amit manuálisan (vagy automatizmusból) alkalmaznak bármikor, bármelyik rekordra. **Backlog-javaslat:** egy `task_templates` tábla (cím, leírás, típus, becsült időtartam, ellenőrzőlista JSON), és a meglévő `<x-task-list>` komponensen egy "sablonból" gomb. Nem MVP-blokkoló, de olcsó és általánosan hasznos (bármilyen szakmában van rutinfeladat).
- **Ajánlat-sablon + digitális dokumentum-aláírás:** a MiniCRM lehetővé teszi, hogy egy ajánlatot/megrendelést/teljesítési igazolást előre elkészített sablonból generáljanak (PDF/DocX), majd az ügyfél **közvetlenül a rendszerben, kép ernyőn/érintéssel aláírja** (egyszerű elektronikus aláírás — SES —, tanúsítvánnyal: aláíró neve, e-mail, IP-cím, időpont, eszköz-ujjlenyomat). **Ez közvetlenül releváns Rob már meglévő, de a CRM-mel egyelőre tudatosan NEM összekötött ajánlatkészítő/szerződéskészítő eszközéhez** (lásd `crm_projekt.md` 7. szekció, lezárt nyitott kérdés: "előbb legyen egy működőképes alap CRM, aztán bonyolítsuk"). Amikor Rob elérkezettnek látja az integráció idejét, ez egy konkrét, kész mintát ad: (1) sablonból generált dokumentum a `documents` táblában, (2) egyszerű elektronikus aláírás beépítve (nem kell drága, teljes körű e-aláírás-szolgáltató, mint a DocuSign/AVDH, egy SES jogilag elég B2B, bizalmi-viszonyos ügyletekhez). **Nem MVP-blokkoló, a már lezárt nyitott kérdés ütemezését nem változtatja meg**, csak konkrét megvalósítási mintát ad a jövőre.

## 9c. Van-e a MiniCRM-nek GoHighLevel/ConnectSoft-jellegű, "all-in-one marketingplatform" bővítése? (2026-07-26, Rob kérése)

Rob megkérdezte, hogy a MiniCRM-nek van-e hasonló, all-in-one marketingplatform-jellegű bővítése, mint amit a ConnectSoft/GoHighLevel-kutatásban találtam. Célzottan rákerestem a legrelevánsabb GoHighLevel-kategóriákra a MiniCRM-nél:

- **Időpontfoglaló (Appointment Booking)** ✅ **IGEN, valóban megvan, és közvetlenül GoHighLevel-jellegű:** a MiniCRM ügyfelei e-mailben kapnak egy foglalási linket, amin a MiniCRM-felhasználó által megadott feltételek szerint választhatnak időpontot. Támogatja a **Google Naptár-szinkronizációt** (figyelembe veszi a felhasználó egyéb elfoglaltságait), és foglaláskor **automatikusan létrehoz egy teendőt** (a foglalás adataival, vagy egy előre megadott teendő-sablonból — lásd 9b. szakasz). **Ez megerősíti a ConnectSoft-kutatásban már felvetett "Nyilvános időpontfoglaló link" backlog-ötletet két független forrásból** — mindkét valódi CRM-termék fontosnak tartja ezt a funkciót, tehát nem egyedi/hóbortos ötlet, hanem bevett CRM best practice.
- **Weboldal/landing oldal/funnel-builder** ❌ **NINCS.** A MiniCRM ad egy beágyazható **űrlap-szerkesztőt** (amit egy meglévő weboldalba lehet illeszteni, Drag & Drop mezőkkel, saját színvilággal), és blogbejegyzésben ad landing-oldal-tanácsokat — de NEM épít teljes weboldalakat/oldalakat, mint a GoHighLevel. Ez megerősíti a korábbi következtetést: az űrlap-beágyazás (webes lead-befogás) igen, a teljes oldalszerkesztő nem — MiniCRM sem megy ebbe az irányba.
- **Tagság-/kurzusplatform (LMS)** ❌ **NINCS** ilyen, amit a MiniCRM az ügyfeleinek kínálna. (A MiniCRM saját, ügyfeleknek szóló képzései/kurzusai megvannak, de az a MiniCRM HASZNÁLATÁNAK megtanítására való, nem egy funkció, amit a MiniCRM-előfizetők a SAJÁT ügyfeleiknek nyithatnának meg.)
- **Affiliate-/partnerprogram-modul** ⚠️ **RÉSZBEN, de FONTOS különbséggel.** A MiniCRM-nek van egy "meghívói programja" — de ez a MiniCRM SAJÁT ügyfélszerzésére szolgál (aki ajánl egy új MiniCRM-előfizetőt, jutalékot kap), NEM egy olyan eszköz, amit a MiniCRM-ügyfelek a SAJÁT affiliate-/partnerprogramjukhoz használhatnának. Ez alapvetően más, mint a GoHighLevel affiliate-modulja, ami pont ez utóbbira (az ügyfél saját affiliate-rendszerére) való. **2026-07-26, Rob pontosítása:** ez a MiniCRM-féle, "saját termék növekedésére szolgáló" minta is legyen a tervek között — méghozzá NEM a távoli GoHighLevel-scope-ban, hanem a "nem túl távoli" ügyfélszerzési tervek között, mert ez fontos elem lesz. Lásd az önálló, részletes [`docs/ugyfelszerzes-terv.md`](ugyfelszerzes-terv.md) dokumentumot, ami ezt ÉS a Salesforce-mintájú ügyfélszerzési ötleteket is tartalmazza.
- **Közösségimédia-ütemezés** ❌ **NINCS** natív MiniCRM-funkcióként található nyoma.

**Következtetés:** a MiniCRM alapvetően megmarad a "klasszikus CRM" kategóriában, egyetlen kivétellel — az **Időpontfoglaló** funkció valódi GoHighLevel-jellegű bővítés, amit a MiniCRM is fontosnak tartott beépíteni. Ez tovább erősíti (2 független forrásból), hogy az időpontfoglalás egy jó, közepes prioritású backlog-tétel, míg a többi GoHighLevel-jellegű funkció (LMS, affiliate ügyfeleknek, social media ütemezés) a MiniCRM-nél sem jelenik meg — ezek inkább a "tényleg all-in-one marketingplatform akarok lenni" döntés esetén válnának relevánssá, nem "minden komoly CRM ezt csinálja" alapon.

## 10. Kapcsolódó dokumentumok

- [`crm_projekt.md`](../crm_projekt.md) 8. szekció — Ötlet-backlog, ahova a fenti "később döntendő" tételek bekerültek.
- [`architektura.md`](architektura.md) — moduláris monolit elv, amit a MiniCRM termékportfólió-felépítése megerősít.
- [`riportok-terv.md`](riportok-terv.md) — a MiniCRM dashboard-mutatók kiegészítik ezt.
- [`jogosultsagok-terv.md`](jogosultsagok-terv.md) — a MiniCRM jogosultsági minták megerősítik/konkretizálják.
- [`ertesitesek-terv.md`](ertesitesek-terv.md) — emlékeztető/naptár-szinkronizáció kapcsolódik ide.
