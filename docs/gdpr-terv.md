# GDPR — hozzájárulás, adatexport, törlés (terv)

> Ez a fájl a `crm_projekt.md` 3. szekció "GDPR" alapelvének részletes kifejtése.
> Utolsó frissítés: 2026-07-25. Jogi részletekben (pontos megőrzési határidők, számviteli kötelezettségek) érdemes lesz jogásszal/könyvelővel egyeztetni élesítés előtt — ez a terv technikai kiindulópont, nem jogi állásfoglalás.

## 1. Hozzájárulás-nyilvántartás

- MVP-ben elég a `contacts.gdpr_consent_at` + `gdpr_consent_note` mezőpár (lásd `adatmodell.md`): mikor és mire adott hozzájárulást a kontakt.
- Ha később szükség lesz több, egymástól független hozzájárulás-típusra (pl. külön "adatkezelés" és "hírlevél"), a `gdpr_consent_log` tábla vezethető be (már megtervezve `adatmodell.md`-ben, de MVP-ben nem kötelező).
- A hozzájárulás rögzítése/visszavonása minden esetben `activity_log` bejegyzést hoz létre (ki, mikor módosította).

## 2. Adatexport ("hozzáférési jog")

**Cél:** egy kontakt (vagy egy teljes account) minden adatáról exportot lehessen készíteni CSV/JSON formátumban, kérésre.

**Kontakt-szintű export tartalma:**
- A `contacts` rekord összes mezője (custom_fields-szel együtt).
- A hozzá kapcsolódó `notes`, `tasks`, `documents` (polimorf kapcsolatokon keresztül).
- A hozzá kapcsolódó `deals` és `projects` alapadatai (cím, státusz, dátumok — pénzügyi/üzleti adat, ami az account tulajdonáé is, ezért csak a kontaktra vonatkozó rész kerül bele, nem a teljes deal-történet).
- Formátum: JSON alapértelmezett (gépileg olvasható), CSV opcióval a könnyebb emberi átتекintéshez.

**Account-szintű export (multi-tenant fázisban lesz releváns):** egy account tulajdonosa exportálhatja a teljes fiókja adatát, ha pl. le akar mondani az előfizetésről és magával akarja vinni az adatait.

**Végpont:** `GET /api/v1/contacts/{id}/export` — lásd `api-tervek.md`.

## 3. Törlés ("elfeledtetéshez való jog")

Kétlépcsős törlés, hogy véletlen törlésből még legyen visszaút, de a végleges törlés is megtörténjen:

1. **Soft delete azonnal:** a user a felületen törlést kér → `deleted_at` beállítva. A rekord azonnal eltűnik minden listából/keresésből, de az adatbázisban megmarad.
2. **Ütemezett végleges törlés:** egy Laravel scheduled command (`php artisan schedule:run`, naponta futtatva) megkeresi azokat a soft-deleted kontaktokat, amelyeknél a `deleted_at` óta eltelt egy megőrzési időszak (**javaslat: 30 nap**, később accountonként konfigurálhatóvá tehető), és:
   - **Kontakt személyes adatait (`first_name`, `last_name`, `email`, `phone`, `custom_fields`) véglegesen töröljük/anonimizáljuk** (pl. "[törölt kapcsolattartó]" placeholder-re cserélve).
   - **A hozzá kapcsolódó üzleti rekordokat (deals, projects) NEM töröljük**, csak a személyes kapcsolót anonimizáljuk rajtuk — mert a számviteli/üzleti nyilvántartás megőrzési kötelezettsége (pl. NAV-szabályok) tovább élhet, mint a személyes adat megőrzési joga. *(Ez egy ésszerű alapértelmezett feltételezés — véglegesítés előtt érdemes könyvelővel/jogásszal megerősíttetni Robnak.)*
   - A törlés ténye `activity_log`-ba kerül (ki kérte, mikor hajtódott végre).
3. **30 napon belüli visszavonás:** amíg csak soft-deleted az állapot, a user (vagy Rob super adminként) visszaállíthatja a rekordot.

## 4. Miért ez a megközelítés

- Megfelel a GDPR "elfeledtetéshez való jog" elvárásának (személyes adat törölve).
- Nem sérti a számviteli megőrzési kötelezettségeket (üzleti/pénzügyi rekordok megmaradnak, csak anonimizálva).
- A 30 napos késleltetés véd a véletlen/rosszindulatú törlés ellen — visszaállítható ablak.
- A teljes folyamat automatizált (scheduled job), nem igényel manuális beavatkozást minden törlési kérésnél.

## 5. Nyitott, később finomítandó kérdés

- A pontos megőrzési határidő (30 nap javaslat) és az, hogy mely üzleti rekord-mezőket kell/lehet megőrizni törlés után — ezt érdemes Robnak (vagy a könyvelőjének) megerősítenie, mielőtt élesben bevezetjük. Nem blokkolja az MVP-t, mert MVP-ben úgyis csak Rob saját adatai vannak a rendszerben.

## Kapcsolódó dokumentumok

- [`adatmodell.md`](adatmodell.md)
- [`api-tervek.md`](api-tervek.md)
