# Értesítési rendszer — terv

> A `crm_projekt.md` 3. szekció "Értesítési rendszer" alapelvének kifejtése: app-on belüli + e-mail emlékeztetők (határidők, események).
> Utolsó frissítés: 2026-07-25.

## 1. Technikai alap: Laravel beépített Notification rendszere

Nem szükséges egyedi `notifications` táblát tervezni — a Laravel saját, beépített, ingyenes Notification-rendszere (`php artisan notifications:table` migrációval létrejövő `notifications` tábla: `id` UUID, `type`, `notifiable_type`+`notifiable_id` polimorf címzett, `data` JSON, `read_at`, időbélyegek) pontosan erre való, és két csatornát egyszerűen támogat egyazon értesítés-osztályból:

- **`database` csatorna** → app-on belüli értesítés (haranghangikon, olvasatlan lista).
- **`mail` csatorna** → e-mail emlékeztető, a meglévő SMTP-szolgáltatáson keresztül (lásd `crm_projekt.md` 3. szekció "E-mail" elve).

Ez megfelel a Költség-elvnek (nincs extra fizetős szolgáltatás), és nem igényel egyedi séma-tervezést.

## 2. Mikor váltódik ki értesítés (triggerek)

Az `architektura.md` 5. pontjában felsorolt eseményekre épül:

| Esemény | Értesítés címzettje | Csatorna(k) |
|---|---|---|
| `TaskDueSoon` (feladat határideje közeleg — ütemezett job váltja ki, pl. 1 nappal előtte) | a feladathoz rendelt user (`assigned_user_id`) | database + mail |
| `TaskOverdue` (határidő lejárt, még nincs `done`) | a feladathoz rendelt user | database + mail |
| `DealStageChanged` (deal új lépésre került) | a deal felelőse (`owner_user_id`), ha nem ő maga mozgatta | database |
| `DealWon` | a deal felelőse + (multi-user fázisban) a csapatvezető/owner szerepkörű userek | database + mail |
| `ProjectCreated` (dealből automatikusan) | a projekt felelőse | database |
| `ContactConsentRecorded` | nincs user-értesítés, csak `activity_log` | — |

## 3. Ütemezés

Az idő-alapú triggerek (pl. `TaskDueSoon`) a Laravel Scheduler-rel futnak (`php artisan schedule:run`, cron-hoz kötve a szerveren — cweb hostingon ez egy egyszerű cPanel cron-bejegyzés lesz). MVP-ben egyetlen, naponta egyszer (pl. reggel) lefutó job is elég; finomabb (óránkénti) ütemezés csak akkor indokolt, ha valós igény mutatkozik rá.

## 4. Felhasználói beállítások (későbbi finomítás, nem MVP-blokkoló)

Középtávon hasznos lehet egy egyszerű "Értesítési beállítások" képernyő, ahol a user ki/bekapcsolhatja csatornánként, mely eseménytípusokra kér e-mailt (hogy ne legyen túl sok levél). MVP-ben (1 user = Rob) ez nem prioritás — minden esemény minden csatornán mehet alapból.

## 5. Kapcsolódó dokumentumok

- [`architektura.md`](architektura.md) — esemény-katalógus.
- [`adatmodell.md`](adatmodell.md) — `tasks`, `deals` táblák, amikhez az értesítések kötődnek.
