# Seeder-terv — kezdő adatok

> Cél: amint fut a Laravel-projekt és a migrációk lefutottak, egyetlen `php artisan db:seed` paranccsal legyen használható állapotban a rendszer — Rob valós accountjával és a teszt-personákkal együtt, hogy azonnal tesztelhető legyen az univerzalitás (lásd `teszt-personak.md`).
> Ez a fájl a seeder **tartalmát/logikáját** írja le pszeudo-kóddal, nem a tényleges PHP-fájlt (az a Laravel-projekten belül készül majd el).
> Utolsó frissítés: 2026-07-25.

## 1. `AccountSeeder` — Rob valós fiókja

- 1 `accounts` rekord: name = "CoachLab", slug = "coachlab", subscription_tier = "premium" (Rob a saját fejlesztője, nincs értelme "free"-nek tekinteni).
- 1 `users` rekord hozzá: Rob, role = "owner", is_super_admin = true.

## 2. `ServiceTypeSeeder` + `PipelineSeeder` — Rob 5 induló szolgáltatása

A `pipeline-sablonok.md`-ben lévő 5 piszkozat (coaching, szervezetfejlesztés, webdesign, marketing, SEO) kerül be `service_types` + `pipelines` + `pipeline_stages` sorokként, Rob accountjához kötve. **Fontos:** mivel a `pipeline-sablonok.md` tartalma Rob validálására vár, ez a seeder csak azután fusson éles adatként, hogy Rob megerősítette/pontosította a lépéseket — addig teszt-/demo-adatnak tekintendő.

## 3. `CustomFieldDefinitionSeeder`

A `pipeline-sablonok.md`-ben az egyes szolgáltatásokhoz javasolt egyedi mezők (pl. coachingnál "Felmérés pontszám", webdesignnél "Domain név") kerülnek be `custom_field_definitions` sorokként.

## 4. `TestPersonaSeeder` *(csak lokális/teszt környezetben fusson, élesben SOHA)*

A `teszt-personak.md` 2. és 3. persona-ja (Kovács Anna — webdesigner, Szabó Márk — asztalos) külön demo-accountként kerül be, saját service_type/pipeline/custom_fields kombinációval. Cél: fejlesztés közben gyorsan válthatunk nézetet, hogy lássuk, minden funkció univerzálisan működik-e, nem csak Rob use case-én.

Laravel-konvenció szerint ez a `DatabaseSeeder::run()`-ban feltételesen hívódjon:

```
if (app()->environment('local', 'testing')) {
    $this->call(TestPersonaSeeder::class);
}
```

Így éles (cweb) környezetben véletlenül sem kerül be demo-adat.

## 5. Futtatási sorrend

```
DatabaseSeeder
├── AccountSeeder            (Rob accountja + usere)
├── ServiceTypeSeeder        (5 szolgáltatás-típus, Rob accountjához)
├── PipelineSeeder           (pipeline-ok + lépések, a service_types-ra hivatkozva)
├── CustomFieldDefinitionSeeder
└── TestPersonaSeeder        (csak local/testing env)
```

## 6. Kapcsolódó dokumentumok

- [`pipeline-sablonok.md`](pipeline-sablonok.md) — a seedelt pipeline-tartalom forrása (validálásra vár).
- [`teszt-personak.md`](teszt-personak.md) — a demo-personák forrása.
- [`mappastruktura-terv.md`](mappastruktura-terv.md) — hol élnek majd a seeder-fájlok.
