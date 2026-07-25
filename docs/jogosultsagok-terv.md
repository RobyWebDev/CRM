# Jogosultságkezelés — részletes terv

> A `crm_projekt.md` 3. szekció "Jogosultságkezelés" elvének kifejtése: szerepkör-alapú (ki mit láthat/módosíthat), `account_id`-hoz kötött adatelkülönítés + egy super admin nézet Robnak.
> MVP-ben (1 user = Rob, `role = owner`) ez a réteg még nem számít gyakorlatilag, de az adatmodell és a jogosultsági logika kezdettől erre épül, hogy multi-user fázisban (5.) ne kelljen újratervezni.
> Utolsó frissítés: 2026-07-25.

## 1. Szerepkörök

A `users.role` mező három értéket vehet fel egy accounton belül (lásd `adatmodell.md`):

- **owner** — az account létrehozója/tulajdonosa. Mindent lát és módosíthat az accounton belül, beleértve az előfizetést/számlázást és a többi user kezelését. Egy accountnak (MVP-ben) egy ownere van.
- **admin** — meghívott munkatárs, aki gyakorlatilag mindent lát/kezel a CRM-adatokban (kontaktok, dealek, projektek, beállítások: pipeline-ok, egyedi mezők, integrációk), de NEM fér hozzá az előfizetés/számlázás beállításaihoz, és nem törölheti az accountot.
- **member** — alap munkatárs. Látja a csapat közös adatait (kontaktok, dealek, projektek — MVP-ben nincs elrejtés, mindenki lát mindent egy accounton belül), de csak a saját maga által létrehozott/hozzá rendelt rekordokat módosíthatja/törölheti, és nem fér hozzá a "Beállítások" menühöz (pipeline-szerkesztő, egyedi mezők, integrációk, userek kezelése).

Ezen felül egy accounttól független, rendszerszintű kapcsoló:

- **super_admin** (`users.is_super_admin = true`) — kizárólag Robnak. Ez a jelölő felülír minden account-scope-ot: minden accountot lát/kezel, hibakereséshez "impersonate" móddal be tud lépni bármelyik account nézetébe (auditnaplózva).

## 2. Jogosultsági mátrix

| Funkció | owner | admin | member |
|---|---|---|---|
| Kontaktok/szervezetek megtekintése (accounton belül, mindenkié) | ✅ | ✅ | ✅ |
| Kontakt/szervezet létrehozása | ✅ | ✅ | ✅ |
| Kontakt/szervezet módosítása/törlése | ✅ (bármelyiket) | ✅ (bármelyiket) | csak a saját (`owner_user_id` = én) |
| Dealek/projektek megtekintése | ✅ | ✅ | ✅ |
| Deal/projekt létrehozása | ✅ | ✅ | ✅ |
| Deal/projekt módosítása/törlése | ✅ (bármelyiket) | ✅ (bármelyiket) | csak a saját |
| Feladatok/jegyzetek/dokumentumok kezelése | ✅ | ✅ | saját + rá kiosztott feladatok |
| Szolgáltatás-típusok / pipeline-ok szerkesztése | ✅ | ✅ | ❌ |
| Egyedi mezők (`custom_field_definitions`) szerkesztése | ✅ | ✅ | ❌ |
| CSV-import indítása | ✅ | ✅ | ❌ |
| Userek meghívása/kezelése/törlése | ✅ | ✅ (owner nem törölhető) | ❌ |
| Integrációk / API-kulcsok kezelése | ✅ | ✅ | ❌ |
| Előfizetés/számlázás kezelése | ✅ | ❌ | ❌ |
| Account törlése | ✅ | ❌ | ❌ |
| Audit napló (`activity_log`) megtekintése | ✅ | ✅ | ❌ |
| Más account adatainak elérése | ❌ | ❌ | ❌ |
| **super_admin**: minden fenti, minden accounton, korlátozás nélkül | — | — | — |

*(A "csak a saját" oszlopérték MVP-ben, 1 usernél nem releváns — minden Rob sajátja. Ez a sor a multi-user fázisban (5.) válik ténylegesen érvényessé.)*

## 3. Technikai megvalósítás (javaslat)

- Laravel **Policies** osztályonként (pl. `ContactPolicy`, `DealPolicy`), amik a `users.role` + a rekord `owner_user_id`/`account_id` mezőit vizsgálják.
- Egy egyszerű `Gate`/middleware ellenőrzi minden kérésnél, hogy a bejelentkezett user `account_id`-ja megegyezik-e az elérni kívánt rekord `account_id`-jával (ez a tenant-elkülönítés alapvédelme, minden szerepkörre érvényes — lásd `architektura.md` 3. pont).
- A super_admin jelölő egy külön middleware-ben kapcsolja ki a tenant-scope-ot, csak azoknál a route-oknál, amik kifejezetten a `/admin/...` alá tartoznak (lásd `api-tervek.md` "Super admin" szakasz).

## 4. Nyitott, később finomítandó kérdés

- Az, hogy a "member" szerepkör lásson-e MINDEN kontaktot/dealt az accounton belül, vagy csak a sajátját — ez MVP-ben (1 user) nem eldöntendő kérdés, de az 5. fázis (multi-user) elején Robbal véglegesítendő, üzleti igény alapján (pl. egy nagyobb csapatnál lehet, hogy a munkatársak nem látják egymás ügyfeleit). A fenti mátrix egy ésszerű, könnyen szigorítható alapértelmezés.

## 5. Kapcsolódó dokumentumok

- [`adatmodell.md`](adatmodell.md) — `users.role`, `is_super_admin`.
- [`architektura.md`](architektura.md) — tenant-elkülönítés mechanizmusa.
- [`api-tervek.md`](api-tervek.md) — super admin végpontok.
