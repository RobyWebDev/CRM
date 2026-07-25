# Mappastruktúra és scaffolding-parancsok

> Cél: amint fut a Laravel-projekt, ez a fájl adja a pontos parancssort, amivel egy lépésben legenerálható a teljes modell/migráció/kontroller-váz — ne kelljen fájlról fájlra kitalálni induláskor.
> A `architektura.md` "moduláris monolit" elve úgy valósul meg, hogy a Modellek/Policy-k Laravel-konvenció szerint lapos mappákban élnek (`app/Models`, `app/Policies`), de a HTTP-kontrollerek és API-route-ok modulonkénti almappákba szerveződnek — ez adja a funkcionális elkülönítést anélkül, hogy az Eloquent-kapcsolatok (amik keresztbe-kasul hivatkoznak egymásra) mesterségesen szét lennének darabolva.
> Utolsó frissítés: 2026-07-25.

## 1. Mappafa (a legfontosabb, projekt-specifikus részek)

```
app/
├── Models/
│   ├── Account.php, User.php
│   ├── Contact.php, Organization.php
│   ├── ServiceType.php, Pipeline.php, PipelineStage.php, Deal.php
│   ├── Project.php, Retainer.php, RetainerInvoice.php, Task.php, Note.php, Document.php
│   ├── CustomFieldDefinition.php
│   ├── Subscription.php, Integration.php, ApiKey.php
│   └── Concerns/
│       └── BelongsToAccount.php      ← trait, minden tenant-scope-olt modell ezt használja
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Contacts/ (ContactController, OrganizationController)
│   │   ├── Pipelines/ (ServiceTypeController, PipelineController, PipelineStageController, DealController)
│   │   ├── Projects/ (ProjectController, TaskController, NoteController, DocumentController)
│   │   ├── CustomFields/ (CustomFieldDefinitionController)
│   │   ├── Integrations/ (IntegrationController, ApiKeyController)
│   │   ├── Webhooks/ (CoachFinderWebhookController, SsoTokenController)
│   │   └── Admin/ (AdminAccountController)
│   ├── Requests/                     ← Form Request osztályok, moduláris almappákkal ugyanúgy
│   └── Middleware/
│       └── EnsureAccountScope.php    ← a tenant-elkülönítés fő middleware-je
├── Policies/
│   ├── ContactPolicy.php, DealPolicy.php, ProjectPolicy.php, ...
├── Events/
│   ├── DealCreated.php, DealStageChanged.php, ProjectCreated.php
│   ├── TaskDueSoon.php, ContactConsentRecorded.php
├── Listeners/
│   ├── LogDealActivity.php, CreateProjectFromWonDeal.php
│   ├── SendTaskDueSoonNotification.php
├── Notifications/
│   ├── TaskDueSoonNotification.php, DealWonNotification.php
└── Console/Commands/
    └── SeedServiceType.php           ← MVP-egyszerűsítés, lásd admin-felulet-terv.md 6. pont
```

## 2. Scaffolding-parancsok (végrehajtási sorrend)

Modellek + migrációk egy lépésben (a `-mf` a migráció+factory-t is legenerálja):

```
php artisan make:model Account -m
php artisan make:model User -m
php artisan make:model Contact -mf
php artisan make:model Organization -mf
php artisan make:model ServiceType -m
php artisan make:model Pipeline -m
php artisan make:model PipelineStage -m
php artisan make:model Deal -mf
php artisan make:model Project -mf
php artisan make:model Retainer -mf
php artisan make:model RetainerInvoice -m
php artisan make:model Task -mf
php artisan make:model Note -m
php artisan make:model Document -m
php artisan make:model CustomFieldDefinition -m
php artisan make:model Subscription -m
php artisan make:model Integration -m
php artisan make:model ApiKey -m
```

A migrációk tartalmát a `schema.sql` alapján kell kitölteni (oszlopok, FK-k, indexek).

Policy-k:

```
php artisan make:policy ContactPolicy --model=Contact
php artisan make:policy DealPolicy --model=Deal
php artisan make:policy ProjectPolicy --model=Project
```

Események + listenerek:

```
php artisan make:event DealCreated
php artisan make:event DealStageChanged
php artisan make:event ProjectCreated
php artisan make:event TaskDueSoon
php artisan make:listener LogDealActivity --event=DealCreated
php artisan make:listener CreateProjectFromWonDeal --event=DealStageChanged
php artisan make:listener SendTaskDueSoonNotification --event=TaskDueSoon
```

Értesítések:

```
php artisan make:notification TaskDueSoonNotification
php artisan make:notification DealWonNotification
php artisan notifications:table   ← a beépített notifications tábla migrációja, lásd ertesitesek-terv.md
```

Kontrollerek (API resource, modulonként almappába szervezve):

```
php artisan make:controller Api/V1/Contacts/ContactController --api --model=Contact
php artisan make:controller Api/V1/Contacts/OrganizationController --api --model=Organization
php artisan make:controller Api/V1/Pipelines/ServiceTypeController --api --model=ServiceType
php artisan make:controller Api/V1/Pipelines/PipelineController --api --model=Pipeline
php artisan make:controller Api/V1/Pipelines/DealController --api --model=Deal
php artisan make:controller Api/V1/Projects/ProjectController --api --model=Project
php artisan make:controller Api/V1/Projects/TaskController --api --model=Task
php artisan make:controller Api/V1/CustomFields/CustomFieldDefinitionController --api --model=CustomFieldDefinition
```

Csomag-migrációk (miután a `csomag-terv.md` szerinti csomagok telepítve lettek):

```
php artisan vendor:publish --tag=activitylog-migrations
php artisan vendor:publish --tag=sanctum-migrations
```

## 3. `BelongsToAccount` trait — a tenant-elkülönítés kulcsa

Minden tenant-scope-olt modell (Contact, Organization, Deal, Project, Retainer, RetainerInvoice, Task, Note, Document, ServiceType, Pipeline, CustomFieldDefinition, Integration, ApiKey) ezt a trait-et használja:

- Global scope, ami automatikusan `WHERE account_id = auth()->user()->account_id` szűrést ad minden lekérdezéshez.
- `creating` model-eseménynél automatikusan kitölti az `account_id`-t a bejelentkezett user account-jából.
- Super admin kontextusban (lásd `jogosultsagok-terv.md`) egy külön middleware ideiglenesen kikapcsolja ezt a scope-ot.

Ez egyetlen helyen (`app/Models/Concerns/BelongsToAccount.php`) valósítja meg a legkritikusabb biztonsági szabályt — ide fog irányulni a `teszterv.md`-ben említett első automatizált teszt is.

## 4. Kapcsolódó dokumentumok

- [`schema.sql`](schema.sql) — a migrációk pontos oszlopterve.
- [`architektura.md`](architektura.md)
- [`api-tervek.md`](api-tervek.md)
- [`csomag-terv.md`](csomag-terv.md)
