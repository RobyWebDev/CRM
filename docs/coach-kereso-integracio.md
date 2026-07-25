# Coach-kereső weboldal ↔ CRM integráció — részletes terv

> A `crm_projekt.md` 2. szekció (2026-07-25-i döntés) kifejtése: webhook-alapú előfizetés-szinkron + egyszer használatos SSO-token. Ez egy **6. fázisos** (Fizetős/SaaS réteg) funkció, most csak a terv készül el előre, hogy ne kelljen a fázis elején újratervezni.
> Utolsó frissítés: 2026-07-25.

## 1. Webhook: előfizetés-változás értesítése

**Végpont:** `POST /api/v1/webhooks/subscription-changed` (lásd `api-tervek.md`).

**Küldő:** a coach-kereső weboldal, amikor egy felhasználó regisztrál/fizet elő/lemond.

**Payload-javaslat:**

```json
{
  "event": "subscription.created",
  "external_user_id": "coachfinder-12345",
  "email": "pelda@email.hu",
  "name": "Példa Ügyfél",
  "subscription_tier": "basic",
  "occurred_at": "2026-07-25T10:00:00+02:00"
}
```

`event` lehetséges értékei: `subscription.created`, `subscription.upgraded`, `subscription.downgraded`, `subscription.canceled`.

**Feldolgozás a CRM oldalán:**
1. Ha `external_user_id`-hoz még nincs `accounts` rekord, létrejön egy új account + egy `users` rekord (owner szerepkörrel) a megadott névvel/e-maillel.
2. Ha már létezik, az `accounts.subscription_tier` frissül.
3. `subscriptions` táblába új sor kerül a napló/történet miatt.
4. A feldolgozás eredménye (`200 OK` vagy hibakód) visszaküldve a weboldalnak.

**Biztonság:** a webhook kérést alá kell írni (HMAC-SHA256 aláírás egy megosztott titkos kulccsal, `X-Signature` fejlécben), amit a CRM ellenőriz feldolgozás előtt — enélkül bárki hamis előfizetés-adatot küldhetne be. A titkos kulcs `.env`-ben tárolva, sosem a repóban.

## 2. Egyszer használatos SSO-token (belépés a weboldalról a CRM-be)

**Cél:** a coach-kereső weboldalon már bejelentkezett felhasználó egy kattintással jusson be a CRM-be, külön jelszó nélkül.

**Folyamat:**

1. A felhasználó a weboldalon rákattint a "CRM megnyitása" gombra.
2. A weboldal szerver-oldalról hívja a CRM `POST /api/v1/sso/token` végpontját, a saját account-API-kulcsával hitelesítve, megadva melyik CRM-userhez kér belépést (`email` vagy `external_user_id` alapján).
3. A CRM generál egy rövid élettartamú (**javaslat: 5 perc**), egyszer felhasználható, véletlenszerű tokent, elmenti (pl. cache-ben, nem adatbázisban, hogy automatikusan lejárjon), és visszaadja.
4. A weboldal átirányítja a böngészőt: `https://crm.sajatdomain.hu/sso/consume/{token}`.
5. A CRM ellenőrzi a tokent (létezik, nem járt le, nem lett még felhasználva), bejelentkezteti a hozzá tartozó usert, a tokent azonnal érvényteleníti, majd átirányít a CRM főoldalára.

**Biztonsági szabályok:**
- A token csak egyszer használható fel (felhasználás után azonnal törölve/invalidálva).
- Rövid élettartam (5 perc), hogy egy esetlegesen elfogott linkkel se lehessen később visszaélni.
- A token generálása csak érvényes account-API-kulccsal hívható (nem publikus végpont).
- Minden SSO-bejelentkezés `activity_log`-ba kerül.

## 3. Mi NEM tartozik ide (tisztázás)

- Ez NEM helyettesíti a CRM saját, jelszavas bejelentkezését — az SSO csak egy kényelmi kiegészítő út, a CRM-be közvetlenül (jelszóval) is be lehet lépni.
- A `subscription_tier` alapú funkció-korlátozás (feature gate) logikája külön téma — ennek pontos tier-határai a `crm_projekt.md` szerint még nincsenek meghatározva, ez később finomítandó, nem blokkolja az MVP-t.

## 4. Kapcsolódó dokumentumok

- [`api-tervek.md`](api-tervek.md) — a két végpont a "Coach-kereső ↔ CRM webhook-fogadó" szakaszban.
- [`adatmodell.md`](adatmodell.md) — `accounts`, `subscriptions`, `api_keys` táblák.
