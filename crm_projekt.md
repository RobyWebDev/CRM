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

> *(Ez a szekció nem egy fázis, hanem folyamatosan érvényes szabálygyűjtemény. Minden munkamenetnek ellenőriznie kell, hogy az adott lépés megfelel-e ezeknek.)*

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
- **Titkok kezelése:** API-kulcsok, jelszavak `.env` fájlban, `.gitignore`-ral kizárva a Git-verziókezelésből — soha nem kerülnek nyilvánosan a repóba. Ezt és a többi biztonsági alapszabályt (SQL-injection/XSS védelem, rate limiting, HTTPS, 2FA későbbre) egy helyen gyűjti: [`docs/biztonsag-terv.md`](docs/biztonsag-terv.md).
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
- **Akadálymentesség / méretezhető szöveg (2026-07-25, Rob kérése):** a felhasználói kör vegyes látású (Rob maga is, de lesznek fiatal és idősebb felhasználók is) — a felület soha nem épülhet apró, fix méretű betűkre. Kötelező: relatív mértékegységek (`rem`/`em`, nem fix `px`), böngésző-nagyítás sosem tiltható le, minimum 16px-nek megfelelő alap betűméret, kellő kontraszt. Részletek: [`docs/ui-wireframe-terv.md`](docs/ui-wireframe-terv.md) 8. szekció.
  **Pontosítás (2026-07-25, ugyanaznap, Rob):** a fenti (kontraszt, méretezhető szöveg) **szigorúan betartandó** — de ez NEM jelenti azt, hogy minden modern/friss interakciós mintát kerülni kellene mozgássérült felhasználók miatt (nem várható jelentős arányban ilyen felhasználó, bár nem kizárt). Vagyis: modern technológiák (pl. drag-and-drop mint elsődleges interakció) bátran bevezethetők, amíg emellett marad egy egyszerű, kattintásos alternatíva is (ez utóbbi a WCAG 2.5.7 miatt kötelező, de nem kell miatta lemondani a modern megoldásról — lásd a Pipeline/Kanban drag-and-drop-ját a haladási naplóban).
- **Színvilág (2026-07-25, Rob kérése, ugyanaznap kiegészítve):** NEM egyetlen rögzített paletta — **több választható, kész, WCAG-nak megfelelő paletta** rendszere, mindegyik sötét ÉS világos módban is. Induló két paletta: "Forest" (a cib.hu sötétzöldje ihlette, `#0b4a35`-ből OKLCH-alapon újratervezve) és "Salesforce" (a Salesforce Lightning Design System valós, ténylegesen lekért kék/fehér tokenjeiből, `#0176d3`). A paletta fiók-szintű (`accounts.theme_palette`), a sötét/világos mód személyes (`users.theme_mode`) — mindkettő beállítható a Profil oldalon. Minden kontrasztarány ténylegesen kiszámolva, nem becsülve: [`docs/szinvilag-terv.md`](docs/szinvilag-terv.md).
- **Fluid/reszponzív méretezés (2026-07-25, Rob kérése):** ahol lehet, `clamp()`-alapú fluid tipográfia és térköz-skála (nem csak töréspontonként ugráló méretek), container query-k, modern viewport-egységek (`dvh`) — hogy minden eszközön profin, folyamatosan skálázva jelenjen meg. Részletek: [`docs/tipografia-layout-terv.md`](docs/tipografia-layout-terv.md).
- **Onboarding:** ha bárki regisztrálhat, kell egy egyszerű "első lépések" folyamat. Terv (nem MVP-blokkoló, az 5-6. fázisra előkészítve): [`docs/onboarding-terv.md`](docs/onboarding-terv.md).
- **ÁSZF / Adatkezelési tájékoztató:** jogi tartalom, szükséges, ha nyilvánosan regisztrálhatóvá válik — nem technikai feladat, ráér.
- **Teljesítmény/gyorsítótárazás:** Laravel beépített, ingyenes cache-megoldásaival tervezve, ha nő a felhasználószám. Részletes terv (shared hosting korlát, cache-driver választás, N+1 elkerülés): [`docs/teljesitmeny-terv.md`](docs/teljesitmeny-terv.md).
- **Lokalizáció (i18n):** MVP-ben csak magyar felület, de a `locale` mező (`accounts`/`users`) már előkészítve a jövőbeli többnyelvű SaaS-fázisra. Terv: [`docs/lokalizacio-terv.md`](docs/lokalizacio-terv.md).

---

## 4. Adatmodell (jelenlegi terv — oszlop-szintű részletezés kész, Rob validálására vár)

| Tábla | Szerep |
| --- | --- |
| `accounts` | Tenant — egy coach/webdesigner/SEO-s teljes fiókja (a jövőbeli SaaS alapegysége) |
| `users` | Bejelentkező személyek, `account_id`-hoz kötve, szerepkörrel (owner, admin, munkatárs) |
| `leads` | Még nem minősített érdeklődők (CRM best practice) — "konvertáláskor" lesz belőlük `contact` (+ opcionálisan `deal`), döntés: 2026-07-25 |
| `contacts` | Kapcsolattartók/ügyfelek, `account_id`-hoz kötve |
| `contact_fields` | Tetszőleges számú, elnevezhető elérhetőség/mező egy kontakthoz (2026-07-26) — Google Címtár-minta, a fő email/phone/address mező mellett bárki hozzáadhat továbbiakat |
| `organizations` | Cégek/szervezetek, amelyekhez kontaktok tartozhatnak |
| `service_types` | Szolgáltatás-típusok (coaching, szervezetfejlesztés, webdesign, marketing, SEO) — konfigurálható lista, nem hardcode-olt |
| `pipelines` | Szolgáltatás-típusonként testre szabható értékesítési/projekt-folyamat |
| `pipeline_stages` | Egy pipeline lépései (pl. coachingnál: érdeklődés → felmérés → ajánlat → szerződés → ülések → lezárás) |
| `deals` | Folyamatban lévő üzletek/lehetőségek egy pipeline-on belül |
| `projects` | Aktív megbízások, **egyszeri**, határidőkkel, díjazással |
| `retainers` | **Ismétlődő/havi díjas** megbízások (pl. folyamatos marketing/SEO kezelés) — külön a `projects`-től (döntés: 2026-07-25) |
| `retainer_invoices` | Egy `retainer` havi/negyedéves számlázási periódusai, követés-státusszal |
| `tasks` | Feladatok, emlékeztetők |
| `notes` | Szabad szöveges jegyzetek kontaktokhoz/projektekhez |
| `documents` | Linkek szerződésekhez, ajánlatokhoz (pl. Google Docs linkek) |
| `campaigns` | Strukturált kampány-nyilvántartás (2026-07-26) — a `leads`/`deals` szabad szöveges `source` mezője MELLETT, kampányonkénti riporthoz (Salesforce-minta) |
| `saved_filters` | Mentett szűrők/nézetek egy listaoldalhoz (2026-07-26), pl. "Forró leadjeim" — csak a szerzőjéhez tartozik |
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
| --- | --- | --- | --- | --- |
| 1 | Specifikáció + adatmodell | Végleges séma, funkciólista, pipeline-ok szolgáltatásonként | Chat (Sonnet) | 🟡 folyamatban (döntő többség kész, Rob-validálásra vár a pipeline-sablonok.md) |
| 2 | Alapkörnyezet | Laravel projekt inicializálása, cweb hosting előkészítése | Claude Code | ✅ helyi Laravel-váz kész (lásd 6. szekció, 2026-07-25) — cweb-feltöltés még hátra van |
| 3 | MVP — csak Robnak | Kontaktok, 1 pipeline/szolgáltatás, projektek, feladatok — single-user, de tenant-ready DB | Claude Code | ✅ **lényegében kész (2026-07-25, tizenhatodik forduló):** Leadek→Kontaktok→Pipeline(lista+kanban+tölcsér-diagram)→Projektek/Retainerek→Teendők(ismétlődéssel)/Jegyzetek/Saját jegyzetek/Címkék/Globális keresés/Insights-panel mind böngészőben működik, élesben tesztelt tenant-elkülönítéssel. Hátralévő, nem blokkoló finomítás: `pipeline-sablonok.md` Rob-validálása |
| 4 | Saját tesztelés | Rob hetekig éles használja, finomítja | Claude Code | 🟡 **elkezdődhet** — a funkcionális alap készen áll, innentől Rob tényleges, folyamatos használata és visszajelzései viszik tovább a fázist |
| 5 | Multi-user réteg | Regisztráció, jogosultságkezelés, több account | Claude Code | ⏳ nincs elkezdve |
| 6 | Fizetős/SaaS réteg | Előfizetés (pl. Stripe/Barion), publikus regisztráció, coach-kereső integráció | Claude Code | ⏳ nincs elkezdve |

---

## 6. Aktuális állapot / Haladási napló

> *(Kiszervezve saját fájlba 2026-07-26-án, kredit-takarékosság miatt — ez volt a leggyorsabban növő, legtöbbször szerkesztett szekció. Szabály változatlan: minden munkamenet végén új bejegyzés dátummal, a régieket nem töröljük.)*

**Teljes napló:** [`docs/haladasi-naplo.md`](docs/haladasi-naplo.md).

**Legutóbbi állapot röviden:** a 3. fázis (MVP, csak Robnak) lényegében kész — a teljes Lead→Kontakt→Pipeline→Projekt/Retainer→Teendő/Jegyzet-lánc böngészőben működik, élesben tesztelt tenant-elkülönítéssel. A 4. fázis (Rob saját tesztelése) elkezdhető. **2026-07-26, tizennyolcadik forduló:** megépült az ügyfélszerzés B) ága (`docs/ugyfelszerzes-terv.md`) — "ki ajánlotta?" mező a kontaktokon + strukturált kampány-nyilvántartás/riport, automata teszttel (31/31 zöld). **Tizenkilencedik forduló:** mentett szűrők/nézetek (Kontaktok/Leadek listaoldal), automata teszttel (36/36 zöld). **Huszadik forduló:** nem blokkoló duplikátum-felismerés kontakt/lead felvételkor (e-mail/telefon alapján), automata teszttel (41/41 zöld). **Huszonegyedik forduló:** aktivitás-idővonal — kiderült, hogy a `spatie/laravel-activitylog` eddig csak telepítve volt, de nem naplózott; most bekötve Contact/Deal/Lead/Project/Retainer modellekre, automata teszttel (44/44 zöld). **Huszonkettedik forduló (Rob kérése):** kontakt-kártya átrendezve (cégnév/kontaktnév/elérhetőségek), és Google Címtár-mintára tetszőleges számú, elnevezhető elérhetőség/mező adható egy kontakthoz (`contact_fields` tábla) — a plusz mezőkre a keresés is kiterjed, automata teszttel (51/51 zöld). **Huszonharmadik forduló:** "+ Új létrehozása..." minta a Kampány/Szervezet/Ki ajánlotta lenyíló mezőknél (`App\Support\SelectOrCreate`), + minimális Organization-kezelőfelület, + a duplikátum-kereső kiterjesztve a `contact_fields`-re — automata teszttel (60/60 zöld). Ettől a fordulótól kezdve Rob explicit kérésére aktívan jelzem, ha egy kérése ütközik a bevált CRM-gyakorlattal (lásd a session-memóriát). Nyitott, nem blokkoló pont: `pipeline-sablonok.md` Rob-validálása (lásd 7. szekció).

---

## 7. Nyitott kérdések (még nincs döntés)

- ~~A "coach kereső" weboldal és a CRM viszonya~~ → **LEZÁRVA (2026-07-25):** moduláris monolit + belső API + esemény-alapú hook-rendszer (lásd döntési napló).
- Pontos pipeline-lépések szolgáltatásonként (coaching, webdesign, marketing, SEO, szervezetfejlesztés) — **piszkozat kész (2026-07-25):** lásd [`docs/pipeline-sablonok.md`](docs/pipeline-sablonok.md), általános szakmai gyakorlat alapján összeállítva. **Rob validálására/pontosítására vár**, mielőtt véglegesnek tekintenénk — nem ismerem a tényleges munkafolyamatát, csak egy ésszerű kiindulópontot adtam.
- ~~Az ajánlatkészítő és szerződéskészítő modulok jelenlegi állapota~~ → **LEZÁRVA (2026-07-25):** Rob megerősítette, hogy már van kész, működő (bár nem 100%-os), **web/HTML-alapú** eszköze. **Rob döntése: ezt egyelőre NEM kötjük össze a CRM-mel** — előbb legyen egy működőképes alap CRM-rendszer, az integráció (API-hívás/webhook/stb.) egy későbbi, bonyolultabb lépés lesz. Addig a `documents` tábla (polimorf, `type: offer/contract`) elég az egyszerű linkeléshez, az `integrations`/`api_keys` táblák pedig elő vannak készítve a jövőbeli mélyebb bekötéshez (lásd `architektura.md` 4. pont).
- ~~Számlázás: csak követés vagy tényleges számla-generálás~~ → **LEZÁRVA (2026-07-25, Rob döntése):** MVP-ben csak *követés-státusz* — a `deals`/`projects` táblákon (és a `retainers` ismétlődő megbízásoknál a `retainer_invoices` táblán) egy `invoice_status` mező (`not_issued` / `issued` / `paid`). Bekerült a `schema.sql`/`adatmodell.md`-be. Tényleges számla-generálás (pl. Számlázz.hu API) egy jövőbeli `integrations` modul marad.
- ~~`projects` tábla egyszeri vs. ismétlődő (retainer) munka~~ → **LEZÁRVA (2026-07-25, Rob döntése):** külön `retainers` + `retainer_invoices` tábla jött létre a `projects` mellett (nem egy `is_recurring` mező) — lásd `adatmodell.md`, `schema.sql`, `architektura.md` (5. pont, `RetainerCreated`/`RetainerInvoicePeriodDue` események) és a frissített `pipeline-sablonok.md` marketing/SEO szakaszai.

---

## 8. Ötlet-backlog (jövőbeli funkció-ötletek — bármikor bővíthető)

> *(Kiszervezve saját fájlba 2026-07-26-án, kredit-takarékosság miatt. Szabály változatlan: bármikor bővíthető, dátum + rövid leírás formátummal.)*

**Teljes backlog:** [`docs/otlet-backlog.md`](docs/otlet-backlog.md).

---

## 9. Instrukciók jövőbeli AI-munkamenetekhez

1. Olvasd el ezt a fájlt, mielőtt bármihez hozzákezdesz — ha a teljes előzménytörténet is kell, [`docs/haladasi-naplo.md`](docs/haladasi-naplo.md) és [`docs/otlet-backlog.md`](docs/otlet-backlog.md) külön fájlban van (2026-07-26 óta, kredit-takarékosság miatt kiszervezve).
2. Nézd meg a "Nyitott kérdések" szekciót — ha valamelyik érinti a következő lépést, kérdezz rá Robnál, mielőtt döntesz helyette.
3. A "Fázisterv" táblázatban frissítsd a Státusz oszlopot, ahogy haladtok (⏳ → 🟡 → ✅).
4. Munkamenet végén ÍRJ egy új sort a [`docs/haladasi-naplo.md`](docs/haladasi-naplo.md)-ba: dátum + mi történt + mi a következő lépés — **közepes hosszúságú legyen** (pár mondat/tétel, nem hosszú bekezdés, de nem is egysoros távirati stílus). Új backlog-ötlet a [`docs/otlet-backlog.md`](docs/otlet-backlog.md)-ba kerül, ugyanígy tömören.
5. Ne törölj korábbi bejegyzéseket a döntési naplóból, a haladási naplóból vagy a backlogból — ez a projekt "emlékezete".
6. Lásd a **10. szekciót** az együttműködési szabályokért (nyelv, önállóság, telepítési hely, státuszjelentés) — ezek minden munkamenetre érvényesek, géptől függetlenül.
7. **Kredit-takarékosság (2026-07-26, Rob kérése):** kerüld a felesleges nagy fájlbeolvasásokat/újraírásokat; ha csak egy kis részt kell módosítani, célzott szerkesztést használj, ne olvasd/írd újra a teljes fájlt, ha nem szükséges. Markdown-táblázatoknál a `| --- | --- |` (szóközös) formátumot használd, ne a tömörített `|---|---|`-t, mert az ismétlődő lint-figyelmeztetést vált ki minden szerkesztésnél.

---

## 10. Együttműködési szabályok (Rob elvárásai az AI-munkamenetekkel szemben)

> *(Rögzítve: 2026-07-25. Ezek a szabályok minden jövőbeli munkamenetre érvényesek, függetlenül attól, melyik gépen dolgozunk.)*

- **Nyelv:** Az AI mindig **magyarul** válaszol Robnak.
- **Önálló munkavégzés:** Rob nem programozó, nem érti a kód részleteit — ezért az AI **önállóan dolgozik**, és nem vár jóváhagyásra minden apró lépésnél. Nem kritikus döntéseket (pl. csomagválasztás, fájl-/mappaszerkezet, apró implementációs részletek) az AI automatikusan elfogadottnak tekinti, és halad tovább kérdezősködés nélkül. Kritikus, költséggel járó, visszafordíthatatlan vagy a projekt irányát érdemben befolyásoló döntéseknél (lásd Költség-elv és Nyitott kérdések) viszont mindig kérdezzen rá előre.
- **Jobb megoldási javaslatok:** Ha az AI-nak jobb ötlete van, mint amivel eddig terveztünk, jelezze és beszéljék meg Robbal — ne csendben térjen el a tervtől.
- **Telepítési hely:** ez a szabály **gépenként újraellenőrizendő, nem fix elvárás** — az első gépen (`d:\AI\CRM`) azért kellett mindent a D: meghajtóra telepíteni, mert a C: meghajtón kritikusan kevés (5,3 GB) volt a szabad hely. **2026-07-25, Rob megerősítette:** a következő gépen a projektmappa C: alá kerül (a mappastruktúrát Rob maga készíti elő) — ott ez rendben van, mert nincs ugyanaz a helyhiány. **Szabály jövőre:** minden új gépen ellenőrizni kell a tényleges szabad helyet (`Get-PSDrive`), és aszerint dönteni a telepítési meghajtóról — nem szabad vakon a D:-t erőltetni, ha az adott gépen annak nincs értelme.
- **Laragon telepítés ütemezése:** Rob később, munka közben fogja telepíteni/befejezni a Laragont (jelzi, ha készen áll rá). Addig azokkal a feladatokkal kell haladni, amikhez **nem szükséges** a telepített Laravel/Laragon környezet — pl. specifikáció és adatmodell finomítása, pipeline-ok kidolgozása szolgáltatásonként, dokumentáció (`/docs`), tervezési döntések, nyitott kérdések tisztázása.
- **Státuszjelentés:** Az AI **önállóan, kérés nélkül** visszajelez Robnak nagyobb feladatok elvégzése **előtt és után** — mi készült el, hol tartunk, mi a következő lépés.
- **Géptartás / több gép:** Rob több számítógépen is dolgozhat a projekten. Ha gépet vált, ezt jelzi az AI-nak — ekkor az új gépen **újra ellenőrizni kell a környezetet** (Laragon, PHP, Composer, MySQL, szabad hely), mert lehet, hogy ott is telepítés szükséges (lásd a hasonló esetet a 6. szekcióban, 2026-07-25-i bejegyzés).
