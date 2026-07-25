# Teljesítmény / gyorsítótárazás — terv

> A `crm_projekt.md` 3. szekció "Teljesítmény/gyorsítótárazás" elvének kifejtése: Laravel beépített, ingyenes cache-megoldásaival tervezve, ha nő a felhasználószám. MVP-ben (1 user, néhány száz rekord) ez gyakorlatilag nem érdemi kérdés — ez a terv arra való, hogy tudjuk, mit kell bekapcsolni, amikor már számít.
> Utolsó frissítés: 2026-07-25.

## 1. Adottság: shared hosting korlát

A cweb hosting (lásd `deployment-terv.md`) valószínűleg **nem ad Redis-t/Memcached-et** — ez befolyásolja a cache-driver választást:

- **Cache driver:** `database` vagy `file` (mindkettő beépített, ingyenes, nem igényel külön szolgáltatást). MVP-ben `file` a legegyszerűbb.
- **Session driver:** ugyanígy `database` vagy `file` — nem `redis`, hacsak a hosting utólag nem biztosítja.
- **Queue driver:** lásd `deployment-terv.md` — `sync` vagy cron-triggerelt `database` queue, nem `redis`/`horizon`.

Ha valaha VPS-re költözünk (lásd `crm_projekt.md` 2. szekció, csak indokolt esetben), ott már megéri Redis-t bevezetni — ez a döntés nem MVP-kérdés.

## 2. Amit már most, kódolás közben be kell tartani (nem külön "cache-feature", hanem alapszabály)

- **N+1 lekérdezés elkerülése:** minden listázó végpont/nézet (kontaktok, dealek, projektek) `with(...)` eager loadinggal töltse be a kapcsolódó modelleket (pl. `Contact::with('organization', 'owner')`), ne ciklusban külön lekérdezéssel.
- **Lapozás mindenhol kötelező:** egyetlen lista-végpont se adjon vissza korlátlan rekordszámot — Laravel `paginate()` alapból erre való (lásd `api-tervek.md` 1. pont).
- **Indexek:** a `schema.sql` már tartalmazza a legfontosabb indexeket (FK-k, `account_id`, polimorf `taskable_type`+`taskable_id` stb.) — minden új gyakori szűrési oszlopnál (pl. ha kiderül, hogy sokat szűrünk `deals.status`-ra) érdemes új indexet felvenni, amikor a valós lekérdezési minták kirajzolódnak.

## 3. Mit cache-elünk, ha majd szükséges lesz (MVP után)

- **Konfigurációs/route/view cache:** `php artisan config:cache`, `route:cache`, `view:cache` — ezek élesítéskor amúgy is bekapcsolandók (lásd `deployment-terv.md` 4. pont), függetlenül a felhasználószámtól, mert ingyenes gyorsítás.
- **Ritkán változó, sokat olvasott adat:** pl. `service_types`, `pipelines`+`pipeline_stages`, `custom_field_definitions` — ezek accountonként ritkán változnak, de szinte minden oldalbetöltésnél kellenek. Cache-elhetők account-onkénti kulccsal (pl. `cache()->remember("account:{$id}:pipelines", ...)`), invalidálva, amikor az admin-felületen módosítják őket.
- **Riportok** (lásd `riportok-terv.md`) — ha egyszer nagyobb adatmennyiségnél lassulnának, ezek jó jelöltek a rövid (pl. 5-10 perces) cache-elésre, mivel nem kell másodpercre pontos valós idejű adat.

## 4. Mikor foglalkozzunk ezzel érdemben

Csak akkor, ha ténylegesen mérhető lassulás jelentkezik (pl. multi-user fázisban, sok accounttal/rekorddal) — MVP-ben, Rob egyedüli használatával, ez nem prioritás, csak jegyezve, hogy ne érje meglepetésként a csapatot, amikor aktuálissá válik.

## 5. Kapcsolódó dokumentumok

- [`deployment-terv.md`](deployment-terv.md)
- [`riportok-terv.md`](riportok-terv.md)
- [`schema.sql`](schema.sql)
