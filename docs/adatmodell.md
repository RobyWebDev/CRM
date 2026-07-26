# Adatmodell — részletes, oszlop-szintű terv

> Ez a fájl a `crm_projekt.md` 4. szekciójának ("Adatmodell") részletes kifejtése.
> A `crm_projekt.md` a rövid áttekintést tartalmazza, ez a fájl az oszlop-szintű tervet.
> Utolsó frissítés: 2026-07-25.

## Vezérelv: kódolás nélküli univerzalitás

A rendszer NEM tartalmaz szakma-specifikus kódot (nincs `CoachController`, nincs `WebdesignPipeline` osztály stb.). Minden szakma-specifikus dolog **adat**, amit egy account (fiók) tulajdonosa admin felületen keresztül maga hoz létre és szabhat testre:

- **Milyen szolgáltatás-típusaim vannak** → `service_types` tábla (szabadon bővíthető).
- **Milyen lépésekből áll a folyamatom** → `pipelines` + `pipeline_stages` tábla (szabadon szerkeszthető, szolgáltatás-típusonként más lehet).
- **Milyen egyedi adatokat akarok rögzíteni** → `custom_field_definitions` tábla (tetszőleges mező felvehető bármelyik entitáshoz).

Ez azt jelenti: ha Rob a coachlab.hu-n túl pl. egy fényképészt vagy egy könyvelőt is fel akar venni a rendszerbe, ahhoz **nem kell új kódot írni** — csak egy új `service_type`-ot, hozzá pipeline-t és egyedi mezőket kell létrehozni a felületen (vagy admin seederrel, amíg nincs UI).

---

## Tábla-szintű terv

### `accounts` (tenant)

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| name | varchar | fiók/cég neve |
| slug | varchar unique | subdomain/URL-hez |
| owner_user_id | bigint FK → users.id nullable | |
| subscription_tier | varchar | `free` / `basic` / `premium` — később bővíthető |
| locale | varchar | alapértelmezett nyelv (pl. `hu`) |
| timezone | varchar | |
| theme_palette | varchar default 'forest' | `forest` (zöld) / `salesforce` (kék/fehér) — fiók-szintű vizuális identitás, lásd `szinvilag-terv.md` |
| created_at / updated_at | timestamp | |
| deleted_at | timestamp nullable | soft delete (GDPR) |

### `users`

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK → accounts.id | **kötelező minden usernél** |
| name, email, password | | Laravel auth alap |
| role | varchar | `owner` / `admin` / `member` — egyszerű, később finomítható jogosultsági mátrixra |
| is_super_admin | boolean default false | csak Robnak — mindent lát/kezel, account-határok felett |
| locale | varchar | |
| theme_mode | varchar nullable | `NULL` = a paletta alapértelmezése, vagy `dark`/`light` — személyes felülbírálás a fiók palettáján belül |
| created_at / updated_at / deleted_at | | |

### `service_types`

Szolgáltatás-típusok — a rendszer NEM tartalmaz hardcode-olt listát, ez a tábla adja a rugalmasságot.

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | egy accounton belül szabadon definiálható lista |
| name | varchar | pl. "Coaching", "Webdesign" |
| slug | varchar | |
| description | text nullable | |
| icon | varchar nullable | UI-hoz (pl. emoji vagy ikon-kulcs) |
| color | varchar nullable | UI megkülönböztetéshez |
| is_active | boolean default true | |
| sort_order | int | |
| created_at / updated_at / deleted_at | | |

### `pipelines`

Egy service_type-hoz tartozhat egy vagy több folyamat (pl. "Új ügyfél pipeline" és "Meglévő ügyfél upsell pipeline" ugyanahhoz a szolgáltatáshoz).

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| service_type_id | bigint FK nullable | ha null, szolgáltatás-független (általános) pipeline |
| name | varchar | |
| description | text nullable | |
| is_default | boolean | ha egy service_type-hoz csak egy van, ez legyen az alapértelmezett |
| sort_order | int | |
| won_creates | varchar default 'project' | `project` / `retainer` / `none` — amikor egy deal erre a pipeline-ra "won" lesz, mit hozzon létre automatikusan (2026-07-25, megvalósítva) |
| created_at / updated_at / deleted_at | | |

### `pipeline_stages`

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| pipeline_id | bigint FK | |
| name | varchar | pl. "Érdeklődés", "Ajánlat kiküldve" |
| sort_order | int | lépések sorrendje |
| color | varchar nullable | UI-hoz (kanban oszlop színe) |
| probability | int nullable | 0-100, várható lezárási esély (forecast-hoz, opcionális) |
| is_won_stage | boolean default false | "nyert" lezáró állapot jelölése |
| is_lost_stage | boolean default false | "elvesztett" lezáró állapot jelölése |
| created_at / updated_at | | |

### `organizations`

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| name | varchar | |
| website | varchar nullable | |
| industry | varchar nullable | |
| custom_fields | json nullable | lásd `custom_field_definitions` |
| created_at / updated_at / deleted_at | | |

### `contacts`

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| organization_id | bigint FK nullable | |
| referred_by_contact_id | bigint FK nullable → contacts.id | melyik meglévő kontakt ajánlotta — Salesforce referral-partner minta egyszerűsítve (2026-07-26), lásd `ugyfelszerzes-terv.md` 3.1. pont |
| owner_user_id | bigint FK nullable | melyik munkatárshoz tartozik |
| first_name, last_name | varchar | |
| job_title | varchar nullable | beosztás/pozíció (2026-07-25, MiniCRM-inspiráció) |
| email, phone | varchar nullable | |
| birthday | date nullable | |
| website | varchar nullable | |
| address | text nullable | |
| source | varchar nullable | honnan jött (pl. "ajánlás", "weboldal űrlap") |
| gdpr_consent_at | timestamp nullable | mikor adott hozzájárulást |
| gdpr_consent_note | text nullable | mihez, milyen formában (GDPR nyilvántartás) |
| custom_fields | json nullable | |
| created_at / updated_at / deleted_at | | soft delete kötelező (GDPR törlési igény miatt is) |

### `contact_fields` (tetszőleges számú, elnevezhető elérhetőség — 2026-07-26)

*Google Címtár-minta (Rob kérése): a fenti fő `email`/`phone`/`address` mező MELLETT bárki (nem csak admin) hozzáadhat továbbiakat, saját elnevezéssel — pl. egy cégügyfélnek 2 telefonszáma ("Mobil"/"Vezetékes"), 2 címe ("Helyszín"/"Számlázási cím"), adószáma, vagy bármilyen egyéb szabadon elnevezett mező. Amíg egy `custom` típusú mező nincs elnevezve, a felület "Egyedi mező 1", "Egyedi mező 2" stb. néven jeleníti meg (lásd `Contact::contactFieldsWithDisplayLabels()`) — ez NEM tárolt érték, hanem megjelenítéskor számolt sorszám. A `value` mezőre a helyi kontakt-keresés és a globális gyorskeresés (`/search`) is keres.*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| contact_id | bigint FK → contacts.id, cascade delete | |
| type | varchar | `email` / `phone` / `address` / `custom` |
| label | varchar nullable | pl. "Mobil", "Számlázási cím", "Adószám" — üres, amíg a felhasználó el nem nevezi |
| value | text | |
| sort_order | unsigned int default 0 | megjelenítési sorrend (a felvétel sorrendje) |
| created_at / updated_at | | nincs soft delete — a kontakttal együtt törlődik (cascade) |

### `tags` / `taggables` (címkék — 2026-07-25, MiniCRM-inspiráció)

*Szabadon felvehető, kontaktokhoz/szervezetekhez rendelhető jelölők, amiket a felhasználó egyszerűen begépel (a nem létező címke automatikusan létrejön) — lásd `docs/minicrm-inspiracio.md` 6. pont.*

| Tábla | Oszlop | Típus | Megjegyzés |
| --- | --- | --- | --- |
| `tags` | id, account_id, name, color, created_at/updated_at | | account-szinten egyedi név |
| `taggables` | tag_id, taggable_type, taggable_id | | polimorf pivot-tábla (Laravel `morphToMany` konvenció) |

### `campaigns` (strukturált kampány-nyilvántartás — 2026-07-26, ügyfélszerzés B) ág)

*Salesforce Lead Source/Campaign Influence minta egyszerűsítve, lásd `ugyfelszerzes-terv.md` 3.2. pont. A `leads.source`/`contacts.source` szabad szöveges mező MELLETT (nem helyette), hogy "melyik hirdetésem térül meg valójában" kérdésre riport is épülhessen (lásd `CampaignController::index`/`show`, leadek/nyert üzletek/nyert bevétel kampányonként).*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| name | varchar | |
| type | varchar nullable | pl. "Facebook-hirdetés", "hideghívás", "ajánlás" |
| started_at | date nullable | |
| cost | decimal nullable | |
| created_at / updated_at | | nincs soft delete — egyszerű lookup-tábla, mint a `service_types` |

### `leads` (még nem minősített érdeklődők)

*CRM best practice (2026-07-25, Rob kérése) — a klasszikus Salesforce Lead objektum egyszerűsített megfelelője: egy lead még NEM Contact, amíg ki nem derül, hogy valódi, munkára érdemes kapcsolat-e. A "konvertálás" (`LeadController::convert`) Contactot hoz létre belőle, és ha van megadva érdeklődési terület (`service_type_id`), az adott szolgáltatás alapértelmezett pipeline-jának első lépésén egy Dealt is.*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| owner_user_id | bigint FK nullable | |
| service_type_id | bigint FK nullable | milyen szolgáltatás iránt érdeklődik |
| first_name, last_name | varchar | |
| email, phone | varchar nullable | |
| company | varchar nullable | |
| project_title | varchar nullable | konkrét projekt/feladat megnevezése, a `service_type_id` kategóriájánál pontosabb (2026-07-26, Rob kérése) |
| source | varchar nullable | pl. "weboldal", "ajánlás", "hideg hívás" |
| campaign_id | bigint FK nullable → campaigns.id | strukturált kampány-attribúció a szabad szöveges `source` mellett (2026-07-26) |
| status | varchar default 'new' | `new` / `contacted` / `qualified` / `unqualified` / `converted` |
| current_status_note | text nullable | szabad szöveg: hol tart most a projekt (2026-07-26) |
| next_step | text nullable | mindig kitölthető, de NEM kötelező mező — mi a várható következő lépés (2026-07-26, Rob explicit kérése) |
| next_step_due_at | date nullable | a következő lépés várható időpontja (2026-07-26) |
| win_probability | tinyint nullable | 0-100, "mennyire érzed nyerhetőnek %" (korábbi neve: `score`, átnevezve 2026-07-26, hogy pontosan tükrözze a célját) |
| comment | text nullable | szabad megjegyzés, egyéb infók (korábbi neve: `notes`, átnevezve 2026-07-26 — VALÓDI HIBA javítva: a `notes` oszlopnév ütközött a `Lead::notes()` polimorf relációval, ami nem teljesen hidratált modell-példányon a relációt adta vissza a szöveg helyett) |
| custom_fields | json nullable | |
| converted_at | timestamp nullable | |
| converted_contact_id | bigint FK nullable → contacts.id | |
| converted_deal_id | bigint FK nullable → deals.id | |
| created_at / updated_at / deleted_at | | |

### `deals` (üzletek/lehetőségek egy pipeline-on belül)

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| pipeline_id | bigint FK | |
| pipeline_stage_id | bigint FK | jelenlegi lépés |
| contact_id | bigint FK nullable | |
| organization_id | bigint FK nullable | |
| campaign_id | bigint FK nullable → campaigns.id | strukturált kampány-attribúció (2026-07-26) — a Lead→Deal konverziónál átöröklődik, ha a leadnek volt kampánya |
| owner_user_id | bigint FK nullable | |
| title | varchar | |
| description | text nullable | mit ajánlunk/tárgyalunk — a Lead `project_title`-jánál részletesebb; ha az üzlet nyer és Projectet/Retainert hoz létre, ez öröklődik tovább azok `description` mezőjébe (2026-07-26) |
| value | decimal(10,2) nullable | várható/tárgyalt összeg |
| currency | varchar default 'HUF' | |
| status | varchar | `open` / `won` / `lost` |
| expected_close_date | date nullable | |
| closed_at | timestamp nullable | |
| stage_entered_at | timestamp nullable | mikor került a JELENLEGI lépésére — "hány napja ebben a lépésben" (elakadt üzlet) jelzéshez, CRM best practice (2026-07-25) |
| invoice_status | varchar default 'not_issued' | `not_issued` / `issued` / `paid` — **MVP: csak követés-státusz**, nincs tényleges számla-generálás (lásd `crm_projekt.md` 7. szekció, lezárt nyitott kérdés) |
| lost_reason | text nullable | miért veszett el — CRM best practice (2026-07-25), hogy tanulni lehessen a bukott üzletekből |
| custom_fields | json nullable | |
| created_at / updated_at / deleted_at | | |

### `projects` (aktív megbízások — EGYSZERI, határidős munka)

*Fontos elhatárolás: a `projects` kizárólag egyszeri, kezdő- és záródátummal rendelkező megbízásokra való (pl. webdesign, coaching-program). Az ismétlődő, havi díjas munkákhoz (pl. folyamatos marketing/SEO kezelés) külön a `retainers` tábla szolgál — lásd lent. Ez a döntés 2026-07-25-én született, a `crm_projekt.md` 7. szekciójában dokumentált nyitott kérdés lezárásaként.*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| deal_id | bigint FK nullable | melyik "megnyert" dealből lett |
| contact_id | bigint FK nullable | |
| organization_id | bigint FK nullable | |
| service_type_id | bigint FK nullable | |
| owner_user_id | bigint FK nullable | |
| title | varchar | |
| description | text nullable | |
| status | varchar | pl. `active` / `on_hold` / `completed` / `cancelled` — testreszabható lista lehet később enum helyett egy `project_statuses` konfig-táblával, ha kell |
| start_date, due_date | date nullable | |
| budget | decimal(10,2) nullable | |
| invoice_status | varchar default 'not_issued' | `not_issued` / `issued` / `paid` — MVP: csak követés-státusz |
| custom_fields | json nullable | |
| created_at / updated_at / deleted_at | | |

### `retainers` (ismétlődő/havi díjas megbízások)

*Külön entitás a `projects` mellett — ide tartozik pl. a marketing/SEO pipeline-okban szereplő "folyamatos kezelés / havi riportolás" lépés utáni munka, ami nem egyszeri, hanem visszatérő havi díjas szolgáltatás. Döntés: 2026-07-25, Rob választása a `crm_projekt.md` 7. szekció nyitott kérdésére (retainer vs. egyszerű mező a `projects`-en) — a tisztább, önálló fogalmat választotta.*

*Pontosítás (2026-07-25, Rob): a retainer NEM csak a marketing/SEO ágra vonatkozik — webdesignnál is van havi díjas karbantartás, ugyanígy retainerként kezelendő (lásd `pipeline-sablonok.md` webdesign 9. lépés). Minden retainerhez tartozhat egy visszatérő teendő-lista: a `tasks` tábla polimorf kapcsolata (`taskable`) már ma is lehetővé teszi, hogy egy `retainer`-hez feladatokat rendeljünk (`Retainer::tasks()`). Ha a gyakorlatban kiderül, hogy minden hónapban ugyanazok a teendők ismétlődnek (pl. "havi riport elkészítése", "biztonsági mentés ellenőrzése"), egy jövőbeli finomítás lehet egy "teendő-sablon" mechanizmus, ami minden új `retainer_invoices` periódusnál automatikusan létrehozza a szokásos feladatokat — ez MVP-ben még nem szükséges, egyelőre kézzel is felvehetők a teendők.*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| deal_id | bigint FK nullable | melyik "megnyert" dealből lett |
| contact_id | bigint FK nullable | |
| organization_id | bigint FK nullable | |
| service_type_id | bigint FK nullable | |
| owner_user_id | bigint FK nullable | |
| title | varchar | |
| description | text nullable | |
| monthly_fee | decimal(10,2) nullable | |
| billing_cycle | varchar default 'monthly' | `monthly` / `quarterly` / `other` |
| billing_day | tinyint nullable | a hónap melyik napján esedékes a számlázás |
| status | varchar | `active` / `paused` / `ended` |
| started_at, ended_at | date nullable | |
| custom_fields | json nullable | |
| created_at / updated_at / deleted_at | | |

### `retainer_invoices` (egy retainer havi/negyedéves számlázási periódusai)

*Mivel a retainer havonta (vagy más ciklusban) ismétlődően számlázandó, egyetlen `invoice_status` mező nem elég (mint az egyszeri `projects`/`deals` esetén) — időszakonként kell nyomon követni. MVP-ben ez is csak követés-státusz, nincs tényleges PDF-számla-generálás.*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| retainer_id | bigint FK | |
| period_start, period_end | date | az adott számlázási időszak |
| amount | decimal(10,2) nullable | |
| invoice_status | varchar default 'not_issued' | `not_issued` / `issued` / `paid` |
| issued_at, paid_at | timestamp nullable | |
| created_at / updated_at | | |

### `tasks`

Polimorf kapcsolat, hogy bármihez (contact, deal, project) köthető legyen feladat.

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| taskable_type, taskable_id | | polimorf FK (Laravel morphs) |
| assigned_user_id | bigint FK nullable | |
| title | varchar | |
| description | text nullable | |
| due_date | datetime nullable | |
| status | varchar | `open` / `done` / `cancelled` |
| recurrence | varchar nullable | `NULL` / `daily` / `weekly` / `monthly` — MiniCRM-inspiráció (2026-07-25): készre jelöléskor automatikusan létrejön a következő előfordulás (lásd `TaskController::toggle`) |
| completed_at | timestamp nullable | |
| created_at / updated_at / deleted_at | | |

### `notes`

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| noteable_type, noteable_id | nullable | polimorf FK — `NULL` esetén "saját jegyzet" (nincs semmilyen rekordhoz kötve), lásd `crm_projekt.md` 8. szekció 9. pont, megvalósítva 2026-07-25 |
| user_id | bigint FK | ki írta |
| body | text | |
| created_at / updated_at | | |

### `saved_filters` (mentett szűrők/nézetek — 2026-07-26)

*Rob saját AI-javaslata (crm_projekt.md 8. szekció) — egy listaoldal (Kontaktok/Leadek) szűrés-kombinációja névvel elmenthető, hogy egy kattintással újra alkalmazható legyen. Csak a szerzőjéhez tartozik, mint a `notes` tábla "saját jegyzet" (user_id) ága.*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| user_id | bigint FK | csak ő láthatja/törölheti |
| resource | varchar | melyik listaoldalra vonatkozik (`contacts` / `leads`) |
| name | varchar | pl. "Forró leadjeim" |
| query_string | text | a lista URL-jének lekérdezés-része (pl. `status=qualified`) |
| created_at / updated_at | | |

### `documents`

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| documentable_type, documentable_id | | polimorf FK |
| title | varchar | |
| url | varchar | pl. Google Docs link |
| type | varchar nullable | `offer` / `contract` / `other` stb. |
| created_at / updated_at / deleted_at | | |

### `custom_field_definitions`

Ez a tábla teszi lehetővé, hogy **fejlesztő nélkül** bármilyen egyedi mezőt fel lehessen venni bármelyik entitáshoz, akár szolgáltatás-típusonként eltérőt.

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| service_type_id | bigint FK nullable | ha null, minden szolgáltatásra érvényes; ha kitöltött, csak arra a szakmára jelenik meg |
| entity_type | varchar | `contact` / `organization` / `deal` / `project` / `retainer` — melyik táblán jelenik meg a mező |
| field_key | varchar | pl. `felmeres_pontszam` — ez a kulcs a `custom_fields` JSON-ban |
| label | varchar | felhasználónak látszó név, pl. "Felmérés pontszám" |
| field_type | varchar | `text` / `textarea` / `number` / `date` / `boolean` / `select` / `multiselect` / `url` |
| options | json nullable | `select`/`multiselect` esetén a választható értékek |
| is_required | boolean default false | |
| sort_order | int | |
| created_at / updated_at | | |

### `activity_log`

A `spatie/laravel-activitylog` csomag saját tábláját használjuk (nem kézzel tervezett séma) — audit naplózáshoz minden lényeges modellváltozásról.

### `subscriptions` *(jövőbeli fázis)*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| tier | varchar | |
| status | varchar | `active` / `canceled` / `past_due` |
| started_at, renewed_at, canceled_at | timestamp nullable | |
| external_ref | varchar nullable | Stripe/Barion azonosító |

### `integrations` *(előkészítve)*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| provider | varchar | pl. "google_docs", "ajanlatkeszito" |
| config | json (titkosítva tárolva) | API-kulcsok, beállítások |
| status | varchar | `active` / `inactive` / `error` |

### `api_keys` *(előkészítve)*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| name | varchar | |
| token_hash | varchar | csak hash tárolva |
| scopes | json | milyen jogosultságokkal |
| last_used_at | timestamp nullable | |
| revoked_at | timestamp nullable | |

### `gdpr_consent_log` *(új javaslat — a `contacts.gdpr_consent_at` mellé, ha részletesebb történet kell)*

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| id | bigint PK | |
| account_id | bigint FK | |
| contact_id | bigint FK | |
| consent_type | varchar | pl. "hírlevél", "adatkezelés" |
| granted | boolean | |
| granted_at / revoked_at | timestamp nullable | |
| note | text nullable | |

*Megjegyzés: MVP-ben elég lehet a `contacts.gdpr_consent_at` + `gdpr_consent_note` is; ez a tábla csak akkor kell, ha többféle, egymástól független hozzájárulást (pl. hírlevél vs. adatkezelés) külön kell nyomon követni. Javaslat: MVP-ben induljunk az egyszerűbb megoldással, ez a tábla a multi-user fázisban (5.) kerülhet be, ha valós igény lesz rá.*

---

## Kapcsolatok összefoglalása

- Minden tábla `account_id`-t hordoz → tenant-elkülönítés (kivéve a globális Laravel rendszertáblák: `migrations`, `jobs`, `cache`, `sessions` stb.)
- `contacts` / `organizations` / `deals` / `projects` / `retainers` mind hordozhat `custom_fields` JSON-t, amit a `custom_field_definitions` ír le.
- `tasks`, `notes`, `documents` polimorf kapcsolattal bármihez köthetők (nem kell külön `contact_tasks`, `project_tasks` stb. tábla) — ez érvényes a `retainers`-re is.
- `deals` → `projects` VAGY `retainers`: amikor egy deal "won" állapotba kerül, a pipeline-sablon/szolgáltatás-típus jellegétől függően vagy egy egyszeri `project`, vagy egy ismétlődő `retainer` jön létre belőle (ez az esemény-alapú hook egyik konkrét használata, lásd `architektura.md`). Melyiket hozza létre? — a `service_types`/`pipelines` konfigurációjában (nem kódban) eldönthető alapértelmezés, hogy egy adott pipeline "won" lépése projektet vagy retainert generáljon-e.
- `retainers` → `retainer_invoices`: egy retainerhez időszakonként (havonta/negyedévente) tartozik egy-egy számlázási rekord, amit ütemezett (pl. napi cron) job generálhat automatikusan a `billing_cycle`/`billing_day` alapján.

---

## Kapcsolódó dokumentumok

- [`schema.sql`](schema.sql) — ugyanez a modell nyers MySQL DDL formában, hogy amint elkészül a Laravel-környezet, gyorsan migrációkká alakítható legyen.
- [`architektura.md`](architektura.md) — modulhatárok, API-réteg, esemény-hook rendszer.
- [`pipeline-sablonok.md`](pipeline-sablonok.md) — példa pipeline-konfigurációk (seed-adat) az öt induló szolgáltatáshoz.
