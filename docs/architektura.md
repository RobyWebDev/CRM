# Architektúra — modulok, API-réteg, esemény-hook rendszer

> Fejlesztői/AI-dokumentáció. Ez a fájl azt írja le, HOGYAN épül fel a rendszer belülről.
> Lásd még: [`adatmodell.md`](adatmodell.md), [`pipeline-sablonok.md`](pipeline-sablonok.md), és a `crm_projekt.md` 2-3. szekcióját a döntések indoklásáért.
> Utolsó frissítés: 2026-07-25.

## 1. Alapelv: moduláris monolit

Egyetlen Laravel-alkalmazás, de tisztán elkülönített modulokra bontva. Nincs mikroszolgáltatás-szétdarabolás (az extra üzemeltetési komplexitás nem indokolt egy shared hostingon futó, kis csapatos rendszernél), de a kód úgy van szervezve, hogy a modulok határai world egyértelműek legyenek, és bármelyik később saját szolgáltatássá válhasson, ha indokolt lesz.

Tervezett modulok (Laravel-en belül, pl. `app/Modules/` alá szervezve, vagy domain-driven mappastruktúrával):

- **Core / Accounts** — tenant-kezelés, userek, jogosultságok, super admin nézet.
- **Contacts** — kontaktok, szervezetek, GDPR-hozzájárulás.
- **Pipelines** — service_types, pipelines, pipeline_stages, deals.
- **Projects** — projektek, feladatok, jegyzetek, dokumentum-linkek.
- **CustomFields** — `custom_field_definitions` kezelése és a JSON-mezők validálása/renderelése.
- **Integrations** — külső eszközök kapcsolatai (API-kulcsok, webhookok).
- **Automation** — esemény-alapú hook-rendszer (lásd lentebb).
- **(jövőbeli) CoachFinder** — a coach-kereső weboldal ↔ CRM webhook-fogadó végpontjai, ha ez a modulon belülre kerül.

## 2. Miért nem kell modult kódolni új szakmánként

Ez a rendszer legfontosabb tervezési döntése (lásd `crm_projekt.md` 3. szekció "Univerzalitás"): **egyetlen szakma sincs hardcode-olva.**

A "modul" szó itt NEM azt jelenti, hogy minden szakmához külön Laravel-modult írunk. A modulhatárok funkció szerint vannak (Contacts, Pipelines, Projects...), NEM szakma szerint. Egy új szakma (pl. fényképész, könyvelő, tetováló) hozzáadása:

1. Új sor a `service_types` táblában (fiók-admin felületről, vagy kezdetben egy seedelő/admin paranccsal).
2. Hozzá tartozó `pipeline` + `pipeline_stages` sorok (a folyamat lépései).
3. Igény szerint `custom_field_definitions` sorok (egyedi mezők, amik csak ennél a szakmánál jelennek meg).

Ehhez **nem kell fejlesztő**, nem kell deploy, nem kell kódmódosítás — csak adatbevitel. A Contacts/Projects/Tasks/Notes modulok kódja teljesen szakma-agnosztikus marad, mert az `entity_type` + `custom_fields` JSON + `pipeline_stage_id` kombináció bármilyen folyamatot le tud írni.

**Gyakorlati következmény Robnak:** amint a CRM-nek van egy admin felülete a service_types/pipelines/custom_fields szerkesztésére, onnantól Rob saját maga hozhat létre teljesen új szakmai profilokat a CRM-ben, fejlesztői beavatkozás nélkül — pontosan ahogy kérte.

## 3. Tenant-elkülönítés (multi-tenancy)

MVP-ben (1 user = Rob) ez még nem élesben számít, de az adatmodell KEZDETTŐL FOGVA úgy épül, hogy ne kelljen utólag újraírni:

- Minden üzleti tábla `account_id` oszlopot hordoz.
- Alkalmazás-szinten egy globális Eloquent scope (pl. `BelongsToAccount` trait + `AccountScope`) automatikusan szűr minden lekérdezést a bejelentkezett user `account_id`-jára.
- A super admin (Rob) nézet kikapcsolhatja ezt a scope-ot, hogy mindent lásson.
- Ha a terhelés/felhasználószám indokolja, később bevezethető a `stancl/tenancy` csomag (adott esetben külön adatbázis/séma accountonként) — ez a döntési naplóban (`crm_projekt.md` 2. szekció) már szerepel mint lehetőség, de MVP-ben az egyszerűbb "egy adatbázis + account_id szűrés" modell is helyes és elég.

## 4. Belső REST API réteg

Cél: külső/jövőbeli eszközök (ajánlatkészítő, szerződéskészítő, Google Docs/Sheets integráció, coach-kereső weboldal) csatlakozni tudjanak a mag-kód ismerete nélkül.

- Minden modul publikál egy REST API-t `api/v1/{modul}` alatt (pl. `api/v1/contacts`, `api/v1/deals`).
- Hitelesítés: Laravel Sanctum, account-hoz kötött API-kulccsal (`api_keys` tábla).
- Minden API-válasz csak az adott `account_id` adatait adja vissza (ugyanaz a tenant-scope érvényes API-n is, mint webes felületen).
- Dokumentáció: automatikusan generálva Laravel Scribe-bal, hogy a kódból mindig naprakész maradjon.

## 5. Esemény-alapú hook-rendszer (automatizáció)

Laravel Events + Listeners párost használunk. Ez köti össze a pipeline-lépéseket jövőbeli automatizációkkal, kódmódosítás nélkül bővíthető módon.

**Példa folyamat:**

```
DealStageChanged esemény kiváltódik
  → ha az új stage `is_won_stage = true`
  → DealWonListener lefut
    → a pipeline/service_type konfigurációja alapján eldönti: egyszeri vagy ismétlődő munkáról van-e szó
    → egyszeri munka esetén létrehoz egy `projects` sort a dealből → ProjectCreated esemény
    → ismétlődő (retainer) munka esetén létrehoz egy `retainers` sort a dealből → RetainerCreated esemény
      → (jövőben) ContractGeneratorListener reagálhat bármelyikre, ha be van kötve a szerződéskészítő integráció
```

*A "projekt vagy retainer?" döntés (2026-07-25, lásd `crm_projekt.md` 7. szekció) nem kódba égetett — a pipeline-hoz/service_type-hoz rendelt beállítás dönti el, hogy a "won" lépés melyiket hozza létre, így ez is admin-oldalon, fejlesztő nélkül állítható.*

**Tervezett alap-események induláshoz:**

| Esemény | Mikor váltódik ki | Belső listener (MVP) |
|---|---|---|
| `DealCreated` | új deal létrehozásakor | activity_log bejegyzés |
| `DealStageChanged` | deal pipeline-lépést vált | activity_log; ha won-stage → project vagy retainer létrehozása (konfigurációtól függően) |
| `ProjectCreated` | új projekt létrejön (kézzel vagy dealből) | activity_log; értesítés a felelősnek |
| `RetainerCreated` | új ismétlődő megbízás létrejön (kézzel vagy dealből) | activity_log; értesítés a felelősnek; ütemezett `retainer_invoices` generálás beállítása |
| `RetainerInvoicePeriodDue` | egy retainer következő számlázási periódusa esedékes (ütemezett job váltja ki `billing_cycle`/`billing_day` alapján) | új `retainer_invoices` sor `not_issued` státusszal |
| `TaskDueSoon` | feladat határideje közeleg (ütemezett job váltja ki) | app-on belüli + e-mail emlékeztető |
| `ContactConsentRecorded` | GDPR-hozzájárulás rögzítésekor | activity_log |

Ez a lista bővíthető, ahogy a rendszer nő — a lényeg, hogy a listenerek regisztrációja konfiguráció (Laravel `EventServiceProvider`), nem kell a core Pipeline/Deal kódot módosítani új automatizáció bevezetésekor.

## 6. Admin felület a no-code testreszabáshoz (tervezett, MVP utáni finomítás)

Az MVP-ben (3. fázis) elég lehet, ha Rob adatbázis-seederrel vagy egyszerű admin form-mal veszi fel a service_types/pipelines/custom_fields sorokat. Középtávon (4-5. fázis) érdemes egy dedikált "Beállítások" admin felületet építeni:

- Szolgáltatás-típusok listázása/szerkesztése/sorrendezése.
- Pipeline-builder: drag-and-drop lépés-szerkesztő.
- Egyedi mező szerkesztő: mezőtípus, kötelezőség, melyik szolgáltatáshoz tartozik.

Ez teszi majd lehetővé, hogy Rob fejlesztői segítség nélkül hozzon létre teljesen új szakmai profilokat a CRM-ben.

## 7. Kapcsolódó dokumentumok

- [`adatmodell.md`](adatmodell.md) — tábla-szintű részletek.
- [`schema.sql`](schema.sql) — nyers DDL.
- [`pipeline-sablonok.md`](pipeline-sablonok.md) — konkrét példa-konfigurációk.
