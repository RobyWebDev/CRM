# CRM Projekt — Univerzális CRM (Coach / Szervezetfejlesztő / Webdesigner / Marketing / SEO)

> **Ez a fájl a projekt "single source of truth"-ja.**
> Minden AI-munkamenet (chat vagy Claude Code) ELSŐ lépése ennek elolvasása.
> Minden munkamenet VÉGÉN kötelező frissíteni az "Aktuális állapot / Haladási napló" szekciót.
> Ha kifogynak a kreditek, és új munkamenettel/modellel folytatod: töltsd fel ezt a fájlt, és mondd:
> "Folytassuk a CRM projektet, itt a crm_projekt.md, olvasd el és folytasd onnan, ahol abbahagytuk."

---

## 1. Vízió és cél

- **Rövid táv (MVP):** saját használatra épül, Rado Róbert (CoachLab) napi munkájának támogatására — kontaktok, projektek, pipeline-ok az öt szolgáltatási ágban (coaching, szervezetfejlesztés, webdesign, marketing, SEO).
- **Hosszú táv:** multi-tenant SaaS termék — más coachok, webdesignerek, marketingesek, SEO szakemberek regisztrálnak és fizetős előfizetéssel használják. Lehetséges kapcsolódás egy "coach kereső" weboldalhoz (regisztráló és fizető coachok automatikusan CRM-fiókot kapnak).
- **Alapelv:** kicsiben indulunk (egy felhasználó = Rob), de az adatmodellt KEZDETTŐL FOGVA multi-tenant-ready módon építjük, hogy ne kelljen utólag újraírni.

---

## 2. Technológiai döntések (döntési napló — időrendben, ne töröld a régieket, csak adj hozzá újakat)

- **2026-07-25:** Backend: **PHP + Laravel** framework.
  - Indoklás: Rob már ismeri a PHP-t (coachlab.hu, saját fejlesztésű 105 kérdéses felmérő rendszer), a Laravel modern, jól karbantartható struktúrát ad, van bevált multi-tenancy csomagja (`stancl/tenancy`), és fut olcsó shared hostingon (cweb) — nem kell drágább VPS.
- **2026-07-25:** Adatbázis: **MySQL** (amit a cweb hosting is támogat, és Rob már használ).
- **2026-07-25:** Frontend MVP-hez: szerver-oldali renderelt Blade sablonok (Laravel saját sablonrendszere) — NEM külön SPA/React, amíg nincs valódi több-felhasználós igény. Ha a SaaS-fázis élesedik, akkor mérlegelhető egy modernebb frontend.
- **2026-07-25:** Hosting: marad a meglévő **cweb** shared hosting, amíg a terhelés ezt bírja. VPS-váltás csak akkor téma, ha a felhasználószám ezt indokolja. Részletes deployment-checklist (document root, `.env`, cron-alapú scheduler/backup, SSL): [`docs/deployment-terv.md`](docs/deployment-terv.md).
- **2026-07-25:** Fejlesztői környezet: Rob **VS Code**-ot használ, a Claude Code VS Code-kiterjesztéssel dolgozunk.
- **2026-07-25:** Fejlesztési stratégia: MVP és saját tesztelés (3-4. fázis) **lokálisan fut** Rob notebookján (Laravel Herd vagy Laragon — PHP+MySQL egyszerű helyi futtatásra). A **cweb hostingra való feltöltés** akkor esedékes, amikor napi/több-eszközös élet használat indul, vagy amikor a multi-user fázis (5-6.) elkezdődik. Kódolási alapszabály: **minden funkció/metódus kapjon minimum rövid kommentet**, ami elmagyarázza, mit csinál — Rob nem napi szinten programozó, szükség van rá, hogy vissza tudjon nézni a kódba és értse, mi történik.
- **2026-07-25:** Ha egy adott résznél (pl. adatelemzés, AI-integráció) Python jobban illik, az külön mikroszolgáltatásként bevonható — az alaprendszer nyelve marad PHP/Laravel.
- **2026-07-25:** Coach-kereső weboldal(ak) ↔ CRM kapcsolat: a CRM külön subdomain-en fut, API-val elérhető. A weboldal **webhookon** keresztül értesíti a CRM-et előfizetés-változásról (létrehoz/frissít egy `account`-ot + `subscription_tier` mezőt). Belépés: **egyszer használatos token-alapú átirányítás** a weboldalról a CRM-be (egyszerű SSO-minta, nincs külön jelszó). Funkció-korlátozás: `subscription_tier` alapú "feature gate" a kódban (pl. `free`/`basic`/`premium`). Pontos tier-határok még nincsenek meghatározva — ez később finomítandó, nem blokkolja az MVP-t. A CRM (és a coach-kereső modul) egy Laravel-alkalmazáson belül él, de tisztán elkülönített modulokra bontva (`Contacts`, `Pipelines`, `Projects`, `Integrations` stb.). Belső **REST API réteg** biztosítja, hogy külső/jövőbeli eszközök (ajánlatkészítő, szerződéskészítő, Google Docs, Google Sheets, bármi más) csatlakozni tudjanak anélkül, hogy a mag-kódot ismerniük kellene. **Esemény-alapú hook-rendszer** (Laravel Events/Listeners) köti össze a pipeline-lépéseket a jövőbeli automatizációkkal (pl. "deal elérte a szerződéskötés fázist" → esemény → szerződéskészítő modul reagálhat rá). Részletes webhook-payload és SSO-token folyamatterv: [`docs/coach-kereso-integracio.md`](docs/coach-kereso-integracio.md).

---

## 3. Tervezési alapelvek — MINDEN fejlesztés során kötelezően figyelembe veendő

*(Ez a szekció nem egy fázis, hanem folyamatosan érvényes szabálygyűjtemény. Minden munkamenetnek ellenőriznie kell, hogy az adott lépés megfelel-e ezeknek.)*

- **Testreszabható mezők:** `custom_fields` definíciós tábla + JSON-alapú rugalmas tárolás a rekordokon (kontaktok, projektek), hogy fiókonként/szolgáltatás-típusonként eltérő egyedi mezőket lehessen felvenni kód-módosítás nélkül.
- **GDPR:** hozzájárulás-nyilvántartás kontaktonként, soft delete + ütemezett végleges törlés, adatexport (CSV/JSON) funkció minden kontakt/account adatáról. Részletes folyamatterv (megőrzési idő, anonimizálás, export tartalma): [`docs/gdpr-terv.md`](docs/gdpr-terv.md).
- **Verziókezelés:** Git + GitHub (ingyenes privát repó) — kötelező minden fejlesztési lépésnél, hogy visszakövethető legyen a történet.
- **Környezetek:** lokális fejlesztői környezet ≠ éles környezet; csak tesztelt kód kerül élesbe.
- **Jogosultságkezelés:** szerepkör-alapú (ki mit láthat/módosíthat), `account_id`-hoz kötött adatelkülönítés + egy **super admin** nézet Robnak, aki mindent lát/kezel. Részletes jogosultsági mátrix (owner/admin/member): [`docs/jogosultsagok-terv.md`](docs/jogosultsagok-terv.md).
- **Audit log:** `activity_log` tábla — ki, mikor, mit módosított (pl. `spatie/laravel-activitylog` ingyenes csomag).
- **Biztonsági mentés:** automatikus, ingyenes MySQL-mentés cron jobbal, már a fejlesztés alatt is bevezetve szokásként.
- **E-mail:** a meglévő SMTP-szolgáltatás újrahasznosítása (amit a coachlab.hu kontaktűrlapjai is használnak); fejlesztéshez Mailtrap (ingyenes teszt-eszköz).
- **Automatizáció:** az esemény-alapú hook-rendszerre épülő "ha X történik, akkor Y" szabályok előkészítése (már a 2. pontban eldöntött architektúra része).
- **Költség-elv:** minden eszközválasztásnál előnyben az ingyenes/nyílt forráskódú megoldás. Ha valamihez nincs jó ingyenes megoldás, VAGY egy fizetős/nem-ingyenes opció lényegesen jobb/gyorsabb/hatékonyabb lenne, azt Rob-bal **előre jelezni és megbeszélni kell**, mielőtt bevezetésre kerül — nincs automatikus fizetős döntés.
- **Karakterkódolás:** UTF-8 következetesen mindenhol (adatbázis, fájlok, API) — kiemelten a magyar ékezetes karakterek miatt (CSV-import, minden szövegmező).
- **Titkok kezelése:** API-kulcsok, jelszavak `.env` fájlban, `.gitignore`-ral kizárva a Git-verziókezelésből — soha nem kerülnek nyilvánosan a repóba.
- **Dokumentáció-struktúra:** `/docs` mappa a projektben, MD-fájlokkal, egyszerű szövegszerkesztővel is bővíthető. Két ág: (1) fejlesztői/AI-dokumentáció — technikai leírás a rendszer felépítéséről, ezt olvassa minden jövőbeli AI-munkamenet is; (2) felhasználói dokumentáció/súgó — ha mások is használják a CRM-et.
- **API-dokumentáció:** automatikusan generált, ingyenes eszközzel (pl. Laravel Scribe), hogy a kódból mindig naprakész maradjon külső modulok (ajánlatkészítő, szerződéskészítő stb.) számára. Tervezési alap (végpont-lista modulonként, a tényleges kódolás előtt összeállítva): [`docs/api-tervek.md`](docs/api-tervek.md).
- **Automatizált alapteszt:** kritikus funkciókhoz (pl. account-elkülönítés) automata teszt, ingyenes Laravel-eszközzel (Pest/PHPUnit), hogy jövőbeli módosítás ne törjön el meglévő működést. Részletes tesztlista: [`docs/teszterv.md`](docs/teszterv.md).
- **Verziószámozás:** a rendszer fejlődését verziószámmal követjük (pl. v0.1.0 = MVP), ahogy a TextBuilder projektnél is. Elindítva: [`CHANGELOG.md`](CHANGELOG.md).
- **Teszt-personák:** mivel univerzális a rendszer, a fejlesztés/tesztelés több kitalált felhasználói profillal történik (pl. coach, webdesigner, "egészen más szakma"), hogy a rugalmasság ténylegesen mindenkinek működjön, ne csak Rob use case-ének. Kidolgozott personák (Rob mint coach, egy webdesigner, és egy kézműves asztalos a szándékos kontraszt kedvéért): [`docs/teszt-personak.md`](docs/teszt-personak.md).
- **Univerzalitás:** a rendszer NEM korlátozódik az 5 példa-szakmára (coach, szervezetfejlesztő, webdesigner, marketinges, SEO) — bárki bármilyen szakmai profillal használhatja. A `service_types` teljesen szabadon bővíthető/definiálható, nincs hardcode-olt lista. **Kiemelt elvárás (2026-07-25, Rob):** ez ne csak adatmodell-szinten legyen igaz, hanem gyakorlatban is — Robnak **fejlesztői beavatkozás/kódolás nélkül** kell tudnia új szakmai profilt (szolgáltatás-típus + pipeline + egyedi mezők) létrehozni a CRM-ben, hogy a rendszerből saját maga tudjon "bármi mást" is konfigurálni, nem csak az 5 induló szakmát. A modulhatárok (Contacts, Pipelines, Projects...) funkció szerint vannak, NEM szakma szerint — lásd [`docs/architektura.md`](docs/architektura.md) 2. pont a pontos mechanizmusért.
- **Git-stratégia:** Rob nem kezeli manuálisan a Git-et — a **Claude Code automatikusan commitol** minden lényegesebb változtatás után (visszaállítható mentési pontok). Rob feladata csak egy **ingyenes GitHub-fiók** létrehozása felhőbeli biztonsági mentés céljából; a GitHub felületét nem kell használnia.
- **Domain/SSL:** a CRM-hez később külön domain vagy aldomain szükséges (pl. `crm.sajatdomain.hu`), ingyenes Let's Encrypt SSL-lel a cweb hostingon keresztül. Nem sürgős — csak élesítéskor esedékes.
- **CSV-import:** kontaktok/adatok tömeges bevitele meglévő Excel/Sheet listákból. Részletes terv: [`docs/csv-import-terv.md`](docs/csv-import-terv.md).
- **Értesítési rendszer:** app-on belüli + e-mail emlékeztetők (határidők, események). Részletes terv: [`docs/ertesitesek-terv.md`](docs/ertesitesek-terv.md).
- **Mobilbarát/reszponzív felület:** alapkövetelmény, hogy telefonról is használható legyen. Fő képernyők wireframe-terve (dashboard, kontaktok, kanban pipeline, projektek, feladatok) reszponzivitási szabályokkal: [`docs/ui-wireframe-terv.md`](docs/ui-wireframe-terv.md).
- **Onboarding:** ha bárki regisztrálhat, kell egy egyszerű "első lépések" folyamat. Terv (nem MVP-blokkoló, az 5-6. fázisra előkészítve): [`docs/onboarding-terv.md`](docs/onboarding-terv.md).
- **ÁSZF / Adatkezelési tájékoztató:** jogi tartalom, szükséges, ha nyilvánosan regisztrálhatóvá válik — nem technikai feladat, ráér.
- **Teljesítmény/gyorsítótárazás:** Laravel beépített, ingyenes cache-megoldásaival tervezve, ha nő a felhasználószám.

---

## 4. Adatmodell (jelenlegi terv — oszlop-szintű részletezés kész, Rob validálására vár)

| Tábla | Szerep |
|---|---|
| `accounts` | Tenant — egy coach/webdesigner/SEO-s teljes fiókja (a jövőbeli SaaS alapegysége) |
| `users` | Bejelentkező személyek, `account_id`-hoz kötve, szerepkörrel (owner, admin, munkatárs) |
| `contacts` | Kapcsolattartók/ügyfelek, `account_id`-hoz kötve |
| `organizations` | Cégek/szervezetek, amelyekhez kontaktok tartozhatnak |
| `service_types` | Szolgáltatás-típusok (coaching, szervezetfejlesztés, webdesign, marketing, SEO) — konfigurálható lista, nem hardcode-olt |
| `pipelines` | Szolgáltatás-típusonként testre szabható értékesítési/projekt-folyamat |
| `pipeline_stages` | Egy pipeline lépései (pl. coachingnál: érdeklődés → felmérés → ajánlat → szerződés → ülések → lezárás) |
| `deals` | Folyamatban lévő üzletek/lehetőségek egy pipeline-on belül |
| `projects` | Aktív megbízások, határidőkkel, díjazással |
| `tasks` | Feladatok, emlékeztetők |
| `notes` | Szabad szöveges jegyzetek kontaktokhoz/projektekhez |
| `documents` | Linkek szerződésekhez, ajánlatokhoz (pl. Google Docs linkek) |
| `subscriptions` | *(jövőbeli fázis)* — mit fizet az adott account a SaaS-ért |
| `integrations` | *(előkészítve, jövőbeli fázis)* — egy accounthoz kapcsolt külső eszköz (ajánlatkészítő, szerződéskészítő, Google Docs/Sheets stb.), API-kulcsokkal/beállításokkal |
| `api_keys` | *(előkészítve)* — accounthoz tartozó API-kulcsok, amikkel külső modulok hitelesítve elérik a CRM API-t |
| `custom_field_definitions` | Account/szolgáltatás-típus szerint definiálható egyedi mezők (pl. coachnál "felmérés pontszám", webdesignernél "domain név") — **ez a tábla teszi lehetővé, hogy fejlesztő nélkül bármilyen új szakma testreszabható legyen** |
| `activity_log` | Audit napló — ki, mikor, mit módosított (GDPR és nyomonkövetés miatt) — a `spatie/laravel-activitylog` csomag saját táblája |

**MINDEN táblában kötelező az `account_id` mező** (tenant-elkülönítés), még akkor is, ha most csak Rob egy account-ját használjuk.

**Részletes, oszlop-szintű terv és a "kódolás nélküli univerzalitás" magyarázata:** [`docs/adatmodell.md`](docs/adatmodell.md). Nyers MySQL DDL (a jövőbeli Laravel-migrációk alapja): [`docs/schema.sql`](docs/schema.sql). Modulok, API-réteg, esemény-hook rendszer: [`docs/architektura.md`](docs/architektura.md). Pipeline-lépés javaslatok szolgáltatásonként (piszkozat, Rob validálására vár): [`docs/pipeline-sablonok.md`](docs/pipeline-sablonok.md).

**Laravel-projekt gyors elindításához előkészítve (Laragon telepítése után azonnal használható):** csomag-/függőségterv [`docs/csomag-terv.md`](docs/csomag-terv.md), mappastruktúra + kész artisan-parancsok [`docs/mappastruktura-terv.md`](docs/mappastruktura-terv.md), kezdő adatok seeder-terve [`docs/seeder-terv.md`](docs/seeder-terv.md). **A teljes `docs/` mappa tartalomjegyzéke és végrehajtási sorrendje egy helyen: [`docs/README.md`](docs/README.md).**

---

## 5. Fázisterv / Roadmap

| # | Fázis | Cél | Eszköz | Státusz |
|---|---|---|---|---|
| 1 | Specifikáció + adatmodell | Végleges séma, funkciólista, pipeline-ok szolgáltatásonként | Chat (Sonnet) | 🟡 folyamatban |
| 2 | Alapkörnyezet | Laravel projekt inicializálása, cweb hosting előkészítése | Claude Code | 🟡 folyamatban |
| 3 | MVP — csak Robnak | Kontaktok, 1 pipeline/szolgáltatás, projektek, feladatok — single-user, de tenant-ready DB | Claude Code | ⏳ nincs elkezdve |
| 4 | Saját tesztelés | Rob hetekig éles használja, finomítja | Claude Code | ⏳ nincs elkezdve |
| 5 | Multi-user réteg | Regisztráció, jogosultságkezelés, több account | Claude Code | ⏳ nincs elkezdve |
| 6 | Fizetős/SaaS réteg | Előfizetés (pl. Stripe/Barion), publikus regisztráció, coach-kereső integráció | Claude Code | ⏳ nincs elkezdve |

---

## 6. Aktuális állapot / Haladási napló

*(Minden munkamenet végén ide kerül egy új bejegyzés dátummal — a régieket nem töröljük, csak bővítjük.)*

- **2026-07-25:** Projekt elindult. Technológiai döntések meghozva (PHP+Laravel, MySQL, cweb hosting). Adatmodell első vázlata kész. Következő lépés: a pipeline-ok részletes kidolgozása szolgáltatásonként, és a coach-kereső oldal ↔ CRM viszonyának tisztázása.
- **2026-07-25 (2. munkamenet — Claude Code, `d:\AI\CRM` gépen):** Nekiálltunk a 2. fázisnak (Laravel projekt inicializálása Laragon alatt), de a gépen **nem volt telepítve Laragon** (sem XAMPP/WAMP, sem Composer, sem MySQL). Talált egy önálló, PATH-on kívüli **PHP 8.5.0**-t a `D:\php` alatt — ez nincs használatban, figyelmen kívül hagyható. Rob a wingetes automatikus telepítést választotta. A `winget` telepítve volt, de a `winget.azureedge.net` CDN nem volt elérhető erről a gépről/hálózatról (hálózati hiba, `laragon.org` és a GitHub-hostok viszont elérhetők voltak) — ezért a Laragon 8.6.1 teljes WAMP telepítőjét (`laragon-wamp.exe`, ~238 MB) közvetlenül letöltöttük GitHubról: `https://github.com/leokhoa/laragon/releases/download/8.6.1/laragon-wamp.exe` (mindig az `https://github.com/leokhoa/laragon/releases/latest` a friss verzió forrása). **Telepítés előtt kiderült, hogy mindkét meghajtón kritikusan kevés a szabad hely** ezen a gépen: `C:` 5,3 GB szabad (90 GB foglalt), `D:` 5,9 GB szabad (121,9 GB foglalt) — ez nem elég biztonságosan egy Laravel projekthez (vendor/, node_modules/, MySQL-adatok idővel több GB-ot is felemészthetnek). **Döntés:** Rob átvált egy másik notebookra, ahol van elég szabad hely, és ott folytatja a Laragon telepítést és a Laravel projekt inicializálását. Ezen a gépen (`d:\AI\CRM`) semmilyen telepítés/módosítás nem történt a rendszerben, csak a telepítő lett letöltve egy ideiglenes (session-scratchpad) mappába, ami nem került be a projektbe.
  **Következő lépés az új gépen:** (1) Laragon telepítése — előbb próbálkozz wingettel (`winget install --id LeNgocKhoa.Laragon -e --source winget`), ha a CDN ott sem elérhető, közvetlen letöltés a fenti GitHub-linkről és csendes telepítés (`laragon-wamp.exe /VERYSILENT /SUPPRESSMSGBOXES /NORESTART`) — **mindig a D: meghajtóra telepítve** (lásd 10. szekció); (2) ellenőrizni, hogy a Laragon PATH-ba teszi-e a PHP-t, Composert, MySQL-t; (3) Laravel projekt létrehozása a Laragon `www` mappájában (`composer create-project laravel/laravel crm` vagy `laravel new crm`); (4) MySQL adatbázis létrehozása + `.env` beállítása; (5) Git inicializálás + első commit (lásd Git-stratégia a 3. szekcióban — Claude Code automatikusan commitol); (6) ez a napló frissítése.
- **2026-07-25 (3. munkamenet):** Rob rögzítette az együttműködési elvárásait — lásd új **10. szekció**: mindig magyarul válaszoljunk, az AI dolgozzon önállóan (nem kritikus döntéseket automatikusan elfogadottnak tekintve, nem vár jóváhagyásra), jobb megoldási javaslat esetén beszéljük meg, minden telepítés a D: meghajtóra kerüljön, a Laragon telepítése elhalasztva (Rob később/munka közben telepíti), addig azokkal a feladatokkal haladunk, amikhez nem kell a telepített környezet, és az AI önállóan, kérés nélkül adjon státuszfrissítést nagyobb feladatok előtt/után. Következő lépés: a Laragon/környezet-függő munka szüneteltetve marad, helyette a 7. szekció (Nyitott kérdések) egyikén — a pipeline-lépések szolgáltatásonkénti kidolgozásán — érdemes továbbhaladni.
- **2026-07-25 (4. munkamenet — önálló munka, Laravel/Laragon nélkül):** Rob megerősítette: elmegy, egy másik notebookról folytatja majd (jelezni fogja), addig az AI **megállás nélkül, jóváhagyás-kérés nélkül** dolgozzon tovább mindazon, amihez nem kell telepített Laravel/Laragon. Külön kiemelte: a CRM-nek **kódolás nélkül** kell testreszabhatónak lennie bármilyen szakmára — ez nem új elv (lásd 3. szekció "Univerzalitás"), de a hangsúly erősödött, ezért a 3. szekció ki lett egészítve ezzel a konkrét elvárással.
  **Elkészült ebben a munkamenetben (Laravel-független tervezőmunka):**
  - [`docs/adatmodell.md`](docs/adatmodell.md) — a 4. szekció oszlop-szintű kifejtése, minden tábla mezőivel, azzal a mechanizmussal, ahogy a `service_types` + `pipelines`/`pipeline_stages` + `custom_field_definitions` hármas kódolás nélkül biztosítja az univerzalitást.
  - [`docs/schema.sql`](docs/schema.sql) — nyers MySQL DDL az egész sémára, hogy amint kész a Laravel-környezet, gyorsan Artisan-migrációkká alakítható legyen.
  - [`docs/architektura.md`](docs/architektura.md) — moduláris monolit felépítés (Contacts/Pipelines/Projects/CustomFields/Integrations/Automation modulok, funkció szerint, NEM szakma szerint), tenant-elkülönítés terve, belső REST API réteg terve, esemény-alapú hook-rendszer (Events/Listeners) konkrét példákkal (pl. `DealStageChanged` → won stage → automatikus `projects` létrehozás).
  - [`docs/pipeline-sablonok.md`](docs/pipeline-sablonok.md) — piszkozat pipeline-lépések mind az 5 induló szolgáltatáshoz (coaching, szervezetfejlesztés, webdesign, marketing, SEO) + javasolt egyedi mezők mindegyikhez + egy konkrét példa arra, hogyan bővülne a rendszer egy hatodik, teljesen új szakmával (fotózás) kódolás nélkül. **Ez Rob validálására vár**, nem végleges.
  - A 7. szekció (Nyitott kérdések) frissítve: a pipeline-kérdés "piszkozat kész" állapotba került; a számlázás kérdésre javasolt egy alapértelmezett irányt (MVP-ben csak követés-státusz, tényleges számlagenerálás későbbi integrációs modul) — nem kritikus, felülbírálható döntésként, hogy ne blokkolja a haladást.
  - **Git verziókövetés elindítva:** a `d:\AI\CRM` mappa eddig nem volt Git-repó — most inicializálva lett (`git init`), `.gitignore` létrehozva (kizárva `.env`, `/vendor`, `/node_modules` stb. a jövőbeli Laravel-projekthez), és ez a munkamenet lett az első commit. Ezután minden lényegesebb változtatás után az AI automatikusan commitol (lásd 3. szekció Git-stratégia).
  **Következő lépés:** további nyitott kérdéseken/dokumentáción lehet haladni Laravel nélkül (pl. REST API végpont-lista részletes kidolgozása, admin-felület wireframe terve a service_types/pipelines/custom_fields szerkesztéséhez), amíg Rob nem jelzi, hogy másik gépen folytatja és telepíthető a Laragon.
  **Folytatás, még ugyanebben a munkamenetben, tovább dolgozva:**
  - [`docs/api-tervek.md`](docs/api-tervek.md) — az `architektura.md`-ben vázolt belső REST API modulonkénti végpont-listája (Contacts, Pipelines, Projects, CustomFields, Integrations, jövőbeli coach-kereső webhook/SSO, super admin), hogy amint kész a Laravel-váz, gyorsan route-okká/kontrollerekké alakítható legyen.
  - [`docs/gdpr-terv.md`](docs/gdpr-terv.md) — konkrét folyamatterv: hozzájárulás-rögzítés, adatexport tartalma és végpontja, kétlépcsős törlés (azonnali soft delete + **javasolt 30 napos** megőrzés utáni automatikus, ütemezett végleges anonimizálás), azzal a feltételezéssel, hogy az üzleti/pénzügyi rekordok (deals/projects) a személyes adat törlése után is megmaradnak számviteli okból — **ezt érdemes lesz könyvelővel/jogásszal megerősíttetni élesítés előtt, nem blokkolja az MVP-t**.
  - [`docs/admin-felulet-terv.md`](docs/admin-felulet-terv.md) — szöveges wireframe-terv arról, milyen "Beállítások" felületen (Szolgáltatás-típusok / Pipeline-ok / Egyedi mezők szerkesztő) tudja majd Rob ténylegesen, kattintgatva, kódolás nélkül létrehozni egy vadonatúj szakmai profilt — ez a konkrét válasz a "ne kelljen fejlesztés" elvárásra. MVP-ben egyszerűsítésként javasolva egy Artisan konzol-parancs vagy közvetlen adatbázis-szerkesztés (phpMyAdmin, ami már elérhető `D:\phpMyAdmin-5.1.1-all-languages` alatt) is elég lehet, amíg a teljes admin-UI el nem készül.
  Mind a négy új `docs/` fájl és a `crm_projekt.md` kapcsolódó bővítései (3., 4. szekció linkek) egy második commitban kerülnek be a git-történetbe.
  **Következő lépés:** ha Rob visszatér és jelzi a gépváltást, először a környezet-ellenőrzés (10. szekció) jön, utána a `pipeline-sablonok.md` és a nyitott kérdések közös átbeszélése, majd a tényleges Laravel-projekt inicializálása a most lefektetett `schema.sql`/`api-tervek.md` alapján.
  **Még ugyanebben a munkamenetben, harmadik körben:** elkészült [`docs/teszt-personak.md`](docs/teszt-personak.md) — a 3. szekció "Teszt-personák" elvéhez 3 kidolgozott profil: Rob mint coach (valós, elsődleges), Kovács Anna mint kitalált webdesigner (második, párhuzamos szakma-teszt), és Szabó Márk mint kitalált asztalos/bútorkészítő (szándékosan más jellegű, fizikai-gyártási folyamat — ez teszteli legerősebben, hogy az architektúra valóban nem csak tanácsadói/digitális szakmákra univerzális). Ezzel lezárult ez a Laravel-független önálló munkaszakasz; összesen 8 új `docs/` fájl készült (`adatmodell.md`, `schema.sql`, `architektura.md`, `pipeline-sablonok.md`, `api-tervek.md`, `gdpr-terv.md`, `admin-felulet-terv.md`, `teszt-personak.md`), a `crm_projekt.md` több szekciója frissült, és a git-történet 3 commitra bővült.
  **Negyedik kör (Rob kérésére: a nyitott kérdések maradjanak a következő közös körre, addig önállóan tovább):** elkészült [`docs/csv-import-terv.md`](docs/csv-import-terv.md) (mezőtérképezés, duplikátum-kezelés, `maatwebsite/excel` csomag javaslat), [`docs/ertesitesek-terv.md`](docs/ertesitesek-terv.md) (Laravel beépített Notification-rendszerére épülő terv, egyedi tábla nem kell, esemény-alapú triggerek táblázata), [`docs/coach-kereso-integracio.md`](docs/coach-kereso-integracio.md) (konkrét webhook JSON payload + HMAC-aláírás, 5 perces egyszer használatos SSO-token folyamat lépésről lépésre), és [`CHANGELOG.md`](CHANGELOG.md) (verziószámozás elindítva, `[Unreleased] — tervezési fázis (0.0.x)`, következő cél v0.1.0 = MVP). A `crm_projekt.md` érintett bullet-jei (CSV-import, Értesítési rendszer, Verziószámozás, coach-kereső döntési napló) linkelve. Ez lesz a 4. git-commit. **Ötödik kör:** elkészült [`docs/jogosultsagok-terv.md`](docs/jogosultsagok-terv.md) — részletes jogosultsági mátrix owner/admin/member szerepkörökre, funkciónként (kontaktok, dealek, beállítások, userkezelés, számlázás, audit napló), Laravel Policy-alapú megvalósítási javaslattal. Ez lesz az 5. git-commit.
  **Hatodik kör (Rob kérésére: még van munkamenet-kapacitás, tovább kellett haladni nélküle):** a Laravel-projekt majdani gyors elindítását előkészítő anyagok készültek el, hogy amint fut a Laragon, ne kelljen menet közben tervezni: [`docs/csomag-terv.md`](docs/csomag-terv.md) (Composer/npm-csomaglista indoklással — Sanctum, activitylog, maatwebsite/excel, Tailwind+Alpine.js, mit NEM viszünk be még), [`docs/mappastruktura-terv.md`](docs/mappastruktura-terv.md) (konkrét mappafa + végrehajtható `php artisan make:...` parancssor minden modellre/kontrollerre/eseményre, és a `BelongsToAccount` trait mint a tenant-elkülönítés technikai kulcsa), [`docs/seeder-terv.md`](docs/seeder-terv.md) (kezdő adatok: Rob valós accountja + az 5 pipeline-sablon + a teszt-personák demo-accountként, csak local/testing környezetben), [`docs/teszterv.md`](docs/teszterv.md) (Pest-tesztlista: account-elkülönítés, jogosultsági mátrix, egyedi mezők validálása, esemény-hook, GDPR-folyamat, mindhárom teszt-personával), és [`docs/deployment-terv.md`](docs/deployment-terv.md) (cweb shared hosting checklist: document root, `.env`, cron-alapú scheduler/backup, SSL — nem MVP-blokkoló, csak előre elkészítve). Ez lesz a 6. git-commit.
  **Következő lépés:** a nyitott kérdések (7. szekció) és a `pipeline-sablonok.md` validálása Robbal közösen, amikor visszatér; a Laravel-független tervezőmunka ezzel gyakorlatilag lefedte mindazt, amit kódolás nélkül érdemes volt előkészíteni (adatmodell, architektúra, API, GDPR, admin-UI, teszt-personák, CSV-import, értesítések, coach-kereső integráció, jogosultságok, csomagok, mappastruktúra, seederek, tesztek, deployment) — a következő érdemi lépés már a tényleges Laravel-projekt inicializálása, ami Laragon-t igényel.
  **Hetedik kör:** elkészült [`docs/README.md`](docs/README.md) — a teljes `docs/` mappa tartalomjegyzéke és egy sorrendbe rendezett, végrehajtható checklist a Laravel-indítás napjára. Ez a 7. git-commit.
  **Nyolcadik kör (Rob kérésére: még tovább, amíg Laragon nélkül lehet):** elkészült [`docs/ui-wireframe-terv.md`](docs/ui-wireframe-terv.md) — a fő alkalmazás-képernyők (dashboard, kontaktok lista+részletek, kanban pipeline nézet drag-and-drop-pal, projektek, feladatok) szöveges wireframe-terve, konkrét reszponzív/mobil-viselkedési szabályokkal (táblázat → kártyás nézet mobilon), ez egészíti ki az `admin-felulet-terv.md`-t, ami csak a Beállítások képernyőket fedte le. Emellett [`docs/onboarding-terv.md`](docs/onboarding-terv.md) — "első lépések" folyamat új accountoknak, jelölve, hogy ez csak az 5-6. fázisban válik szükségessé, MVP-ben nem blokkoló. A `docs/README.md` checklist is frissítve a két új fájllal (10. és a bővített 12. pont). Ez a 8. git-commit. **Ezzel a Laravel-független tervezőmunka a gyakorlatban a teljes `crm_projekt.md` 3. szekciót lefedte** — a további érdemi haladáshoz már tényleg Laragon/Laravel szükséges.

---

## 7. Nyitott kérdések (még nincs döntés)

- ~~A "coach kereső" weboldal és a CRM viszonya~~ → **LEZÁRVA (2026-07-25):** moduláris monolit + belső API + esemény-alapú hook-rendszer (lásd döntési napló).
- Pontos pipeline-lépések szolgáltatásonként (coaching, webdesign, marketing, SEO, szervezetfejlesztés) — **piszkozat kész (2026-07-25):** lásd [`docs/pipeline-sablonok.md`](docs/pipeline-sablonok.md), általános szakmai gyakorlat alapján összeállítva. **Rob validálására/pontosítására vár**, mielőtt véglegesnek tekintenénk — nem ismerem a tényleges munkafolyamatát, csak egy ésszerű kiindulópontot adtam.
- Az ajánlatkészítő és szerződéskészítő modulok jelenlegi állapota (mennyire készek, milyen technológiával íródtak) — továbbra is nyitott, ezt csak Rob tudja megválaszolni. Megbeszélendő, ha visszatér.
- Számlázás: csak követés (állapot: kiállítva/fizetve) vagy tényleges számla-generálás is kellene? **Javasolt alapértelmezés (2026-07-25, nem kritikus döntés, felülbírálható):** MVP-ben csak *követés* — a `deals`/`projects` táblákon egy egyszerű számla-státusz mező (pl. `invoice_status`: nincs kiállítva / kiállítva / fizetve) elég. Tényleges számla-generálás (pl. Számlázz.hu API-integráció) egy jövőbeli `integrations` modul lehet — ezt nem érdemes az MVP-t blokkolva most eldönteni, mert jogi/technikai többletmunkát adna hozzá indokolatlanul korán.

---

## 8. Ötlet-backlog (jövőbeli funkció-ötletek — bármikor bővíthető)

*(Ide kerül minden felmerülő ötlet, gondolat, még ha nem is dolgozzuk fel most. Cél: semmi ne vesszen el, de ne is szakítsa meg a jelenlegi munkát. Formátum: dátum + rövid leírás + [még nincs kategorizálva / MVP-be való / későbbi fázis].)*

- *(egyelőre üres — ide gyűjtjük a jövőben felmerülő ötleteket)*

---

## 9. Instrukciók jövőbeli AI-munkamenetekhez

1. Olvasd el ezt a teljes fájlt, mielőtt bármihez hozzákezdesz.
2. Nézd meg a "Nyitott kérdések" szekciót — ha valamelyik érinti a következő lépést, kérdezz rá Robnál, mielőtt döntesz helyette.
3. A "Fázisterv" táblázatban frissítsd a Státusz oszlopot, ahogy haladtok (⏳ → 🟡 → ✅).
4. Munkamenet végén ÍRJ egy új sort az 5. szekcióba: dátum + mi történt + mi a következő lépés.
5. Ne törölj korábbi bejegyzéseket a döntési naplóból vagy a haladási naplóból — ez a projekt "emlékezete".
6. Lásd a **10. szekciót** az együttműködési szabályokért (nyelv, önállóság, telepítési hely, státuszjelentés) — ezek minden munkamenetre érvényesek, géptől függetlenül.

---

## 10. Együttműködési szabályok (Rob elvárásai az AI-munkamenetekkel szemben)

*(Rögzítve: 2026-07-25. Ezek a szabályok minden jövőbeli munkamenetre érvényesek, függetlenül attól, melyik gépen dolgozunk.)*

- **Nyelv:** Az AI mindig **magyarul** válaszol Robnak.
- **Önálló munkavégzés:** Rob nem programozó, nem érti a kód részleteit — ezért az AI **önállóan dolgozik**, és nem vár jóváhagyásra minden apró lépésnél. Nem kritikus döntéseket (pl. csomagválasztás, fájl-/mappaszerkezet, apró implementációs részletek) az AI automatikusan elfogadottnak tekinti, és halad tovább kérdezősködés nélkül. Kritikus, költséggel járó, visszafordíthatatlan vagy a projekt irányát érdemben befolyásoló döntéseknél (lásd Költség-elv és Nyitott kérdések) viszont mindig kérdezzen rá előre.
- **Jobb megoldási javaslatok:** Ha az AI-nak jobb ötlete van, mint amivel eddig terveztünk, jelezze és beszéljék meg Robbal — ne csendben térjen el a tervtől.
- **Telepítési hely:** Minden telepítendő szoftvert (Laragon, egyéb fejlesztői eszközök, függőségek, ahol van választási lehetőség) a **D: meghajtóra** kell telepíteni — a C: meghajtón nincs elég szabad hely.
- **Laragon telepítés ütemezése:** Rob később, munka közben fogja telepíteni/befejezni a Laragont (jelzi, ha készen áll rá). Addig azokkal a feladatokkal kell haladni, amikhez **nem szükséges** a telepített Laravel/Laragon környezet — pl. specifikáció és adatmodell finomítása, pipeline-ok kidolgozása szolgáltatásonként, dokumentáció (`/docs`), tervezési döntések, nyitott kérdések tisztázása.
- **Státuszjelentés:** Az AI **önállóan, kérés nélkül** visszajelez Robnak nagyobb feladatok elvégzése **előtt és után** — mi készült el, hol tartunk, mi a következő lépés.
- **Géptartás / több gép:** Rob több számítógépen is dolgozhat a projekten. Ha gépet vált, ezt jelzi az AI-nak — ekkor az új gépen **újra ellenőrizni kell a környezetet** (Laragon, PHP, Composer, MySQL, szabad hely), mert lehet, hogy ott is telepítés szükséges (lásd a hasonló esetet a 6. szekcióban, 2026-07-25-i bejegyzés).
