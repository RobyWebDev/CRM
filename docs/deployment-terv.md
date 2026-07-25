# Deployment terv — cweb shared hosting

> A `crm_projekt.md` 2. szekció döntése szerint a hosting a meglévő **cweb** shared hosting marad, amíg a terhelés bírja. Ez a fájl azt a checklistet adja, amit majd akkor kell végigvinni, amikor a 4. fázis végén (saját tesztelés után) élesítjük a rendszert — **nem MVP-blokkoló, csak előkészítés**, hogy ne kelljen a helyszínen kapkodni.
> Utolsó frissítés: 2026-07-25.

## 1. Alapfeltétel-ellenőrzés a cweb hostingon

- PHP verzió támogatja-e a Laravel aktuális LTS-verziójának minimumkövetelményét (jellemzően PHP 8.2+).
- MySQL adatbázis + felhasználó létrehozása a cPanel-en.
- SSH-hozzáférés elérhető-e (Composer/Artisan futtatásához) — ha nem, a `vendor/` mappát lokálisan kell összeállítani és feltölteni.

## 2. Document root beállítása

Laravel-nél a webszervernek a projekt `public/` mappájára kell mutatnia, NEM a projekt gyökerére (biztonsági okból — a `.env`, `app/`, `vendor/` mappák nem lehetnek publikusan elérhetők). Megoldási lehetőségek shared hostingnál, ha nincs mód a document root átállítására:
- A `public/` mappa tartalmának symlinkelése/másolása a hosting webgyökerébe, az `index.php`-ban az útvonalak átírásával — ez a bevett minta megosztott hostingnál futó Laravelnél.
- Alternatíva: al-mappa/aldomain (`crm.sajatdomain.hu`) esetén könnyebb közvetlenül a `public/`-ra állítani a document rootot.

## 3. Környezeti fájl (`.env`)

- **Soha nem kerül a Git-repóba** (lásd `.gitignore`), közvetlenül a szerveren kell létrehozni/feltölteni.
- Kulcs beállítások: `APP_ENV=production`, `APP_DEBUG=false` (élesben SOHA `true`, biztonsági kockázat), `APP_URL`, adatbázis-kapcsolat, SMTP (a meglévő, coachlab.hu-n is használt szolgáltatás), `APP_LOCALE=hu`, `APP_TIMEZONE=Europe/Budapest`.

## 4. Telepítési lépések (élesítéskor)

```
composer install --no-dev --optimize-autoloader
php artisan key:generate          (csak első alkalommal)
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Ütemezett feladatok (cron) — mivel shared hostingon nincs folyamatosan futó process

Két cPanel cron-bejegyzés szükséges:

- **Laravel Scheduler** (percenként fut, ez indítja a `TaskDueSoon` ellenőrzést, GDPR-törlési jobot stb. — lásd `ertesitesek-terv.md`, `gdpr-terv.md`):
  ```
  * * * * * php /path/to/crm/artisan schedule:run >> /dev/null 2>&1
  ```
- **Adatbázis-mentés** (lásd `crm_projekt.md` 3. szekció "Biztonsági mentés" elve) — napi automatikus MySQL-dump, pl. cPanel beépített backup-eszközével vagy egy egyszerű `mysqldump` cron-scripttel, külön tárhelyre (pl. Google Drive/e-mail-csatolmány, ingyenes megoldás).

Queue: MVP-ben a `sync` driver (azonnali, szinkron feldolgozás) valószínűleg elég, mert nincs valódi háttérfeldolgozási igény nagy terhelésnél. Ha a CSV-import vagy más hosszabb művelet miatt mégis kellene valódi háttér-worker, cweb hostingon ez cron-triggerelt `queue:work --stop-when-empty` paranccsal oldható meg (mivel nincs hosszan futó process).

## 6. SSL / domain

Lásd `crm_projekt.md` 3. szekció "Domain/SSL" elve — ingyenes Let's Encrypt, cPanel-ből jellemzően pár kattintással aktiválható. Nem sürgős, csak élesítéskor esedékes.

## 7. Kapcsolódó dokumentumok

- [`csomag-terv.md`](csomag-terv.md)
- [`ertesitesek-terv.md`](ertesitesek-terv.md)
- [`gdpr-terv.md`](gdpr-terv.md)
