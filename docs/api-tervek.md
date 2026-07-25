# Belső REST API — végpont-terv

> Ez a fájl az [`architektura.md`](architektura.md) 4. pontjának ("Belső REST API réteg") részletes kifejtése.
> Cél: mire elkészül a Laravel-alkalmazás, legyen kész terv, amit gyorsan route-okká/kontrollerekké lehet alakítani.
> Az itt felsorolt API-t automatikusan generált dokumentáció (Laravel Scribe) fogja majd kiváltani/kiegészíteni élesben — ez a fájl a tervezési alap, nem a végleges, élő dokumentáció.
> Utolsó frissítés: 2026-07-25.

## Alapelvek

- Minden végpont `api/v1/...` alatt.
- Hitelesítés: Laravel Sanctum, account-hoz kötött `api_keys` token (külső integrációknak) VAGY bejelentkezett user session/token (belső, webes felület AJAX-hívásaihoz).
- Minden válasz automatikusan az aktuális `account_id`-ra szűrve (tenant-scope) — más account adata soha nem látszik.
- Formátum: JSON, UTF-8.
- Standard CRUD végpontok Laravel resource-controller konvenció szerint (`index`, `store`, `show`, `update`, `destroy`).
- Listázó végpontek lapozottak (`?page=`), szűrhetők (`?filter[...]=`) és rendezhetők (`?sort=`) — Laravel alapkonvenció, nem kell egyedi megoldás.

## Modulonkénti végpontok

### Contacts modul

| Metódus | Végpont | Funkció |
|---|---|---|
| GET | `/api/v1/contacts` | lista, szűrhető pl. `organization_id`, `owner_user_id` szerint |
| POST | `/api/v1/contacts` | új kontakt létrehozása |
| GET | `/api/v1/contacts/{id}` | egy kontakt adatai (custom_fields-szel együtt) |
| PUT/PATCH | `/api/v1/contacts/{id}` | módosítás |
| DELETE | `/api/v1/contacts/{id}` | soft delete |
| POST | `/api/v1/contacts/{id}/gdpr-consent` | GDPR-hozzájárulás rögzítése (`gdpr_consent_at` + note) |
| GET | `/api/v1/contacts/{id}/export` | GDPR adatexport (JSON/CSV) — lásd `gdpr-terv.md` |
| POST | `/api/v1/contacts/import` | CSV tömeges import |
| GET | `/api/v1/organizations` / `POST` / `{id}` GET/PUT/DELETE | ugyanaz a mintázat a szervezetekre |

### Pipelines modul

| Metódus | Végpont | Funkció |
|---|---|---|
| GET | `/api/v1/service-types` | szolgáltatás-típusok listája |
| POST | `/api/v1/service-types` | új szolgáltatás-típus (ez teszi lehetővé kódolás nélkül az univerzalitást) |
| PUT/DELETE | `/api/v1/service-types/{id}` | módosítás/törlés |
| GET | `/api/v1/pipelines` | pipeline-ok listája (szűrhető `service_type_id` szerint) |
| POST | `/api/v1/pipelines` | új pipeline |
| GET | `/api/v1/pipelines/{id}` | pipeline + lépései |
| PUT/DELETE | `/api/v1/pipelines/{id}` | módosítás/törlés |
| POST | `/api/v1/pipelines/{id}/stages` | új lépés hozzáadása |
| PUT | `/api/v1/pipeline-stages/{id}` | lépés átnevezése/sorrendezése |
| DELETE | `/api/v1/pipeline-stages/{id}` | lépés törlése |
| GET | `/api/v1/deals` | dealek listája (szűrhető pipeline/stage/status szerint — ez adja a kanban-nézetet) |
| POST | `/api/v1/deals` | új deal |
| GET/PUT/DELETE | `/api/v1/deals/{id}` | deal kezelése |
| POST | `/api/v1/deals/{id}/move-stage` | deal áthelyezése egy másik lépésre → kiváltja a `DealStageChanged` eseményt |
| POST | `/api/v1/deals/{id}/win` | deal lezárása nyertként → `DealStageChanged` (won) + automatikus `projects` létrehozás |
| POST | `/api/v1/deals/{id}/lose` | deal lezárása vesztesként |

### Projects modul

| Metódus | Végpont | Funkció |
|---|---|---|
| GET/POST | `/api/v1/projects` | lista / új projekt |
| GET/PUT/DELETE | `/api/v1/projects/{id}` | projekt kezelése |
| GET/POST | `/api/v1/projects/{id}/tasks` | projekthez tartozó feladatok (a `tasks` polimorf, de a REST-en belül kényelmi wrapper) |
| GET/POST | `/api/v1/tasks` | általános feladat-végpont, `taskable_type`+`taskable_id` paraméterrel bármihez köthető |
| PUT | `/api/v1/tasks/{id}` | módosítás, pl. `status=done` |
| GET/POST | `/api/v1/notes` | jegyzet létrehozása bármely entitáshoz (`noteable_type`+`noteable_id`) |
| GET/POST | `/api/v1/documents` | dokumentum-link hozzáadása bármely entitáshoz |

### CustomFields modul

| Metódus | Végpont | Funkció |
|---|---|---|
| GET | `/api/v1/custom-field-definitions` | szűrhető `entity_type` + `service_type_id` szerint |
| POST | `/api/v1/custom-field-definitions` | új egyedi mező létrehozása — **ez a végpont teszi lehetővé, hogy admin felületről kódolás nélkül bővüljön bármelyik entitás** |
| PUT/DELETE | `/api/v1/custom-field-definitions/{id}` | módosítás/törlés |

### Integrations modul

| Metódus | Végpont | Funkció |
|---|---|---|
| GET/POST | `/api/v1/integrations` | fiókhoz kötött külső eszközök listája/hozzáadása |
| PUT/DELETE | `/api/v1/integrations/{id}` | módosítás/törlés |
| GET/POST | `/api/v1/api-keys` | account saját API-kulcsainak kezelése (kulcs maga csak létrehozáskor jelenik meg egyszer) |
| DELETE | `/api/v1/api-keys/{id}` | kulcs visszavonása |

### Coach-kereső ↔ CRM webhook-fogadó (jövőbeli, 6. fázis)

| Metódus | Végpont | Funkció |
|---|---|---|
| POST | `/api/v1/webhooks/subscription-changed` | a coach-kereső weboldal értesíti a CRM-et előfizetés-változásról → account létrehozás/frissítés + `subscription_tier` |
| POST | `/api/v1/sso/token` | egyszer használatos SSO-token generálása a weboldal → CRM átirányításhoz |
| GET | `/sso/consume/{token}` | (nem API, hanem webes route) a token beváltása, bejelentkeztetés |

### Super admin (csak Robnak)

| Metódus | Végpont | Funkció |
|---|---|---|
| GET | `/api/v1/admin/accounts` | minden account listája, tenant-scope megkerülésével |
| GET | `/api/v1/admin/accounts/{id}` | egy account teljes adatköre |
| POST | `/api/v1/admin/accounts/{id}/impersonate` | belépés egy account nézetébe hibakereséshez (auditnaplózva) |

## Kapcsolódó dokumentumok

- [`architektura.md`](architektura.md)
- [`adatmodell.md`](adatmodell.md)
- [`gdpr-terv.md`](gdpr-terv.md)
