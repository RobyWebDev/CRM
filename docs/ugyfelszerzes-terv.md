# Ügyfélszerzés (customer acquisition) — nem túl távoli tervek

> Rob kérése (2026-07-26): "a minicrm saját ügyfélszerzésre szánt része is és lehet bárhonnan, ami jólbevált már akár salesforce ügyfélszerzési rész is kerüljön be a nem túl távoli tervekbe, mert az fontos eleme lesz."
>
> Ez a dokumentum szándékosan **külön kategória** a `connectsoft-inspiracio.md`-ben rögzített **TÁVOLI (hosszú távú)** GoHighLevel-scope-tól — az itt felsorolt ötletek **közelebbi prioritásúak**, mert Rob kifejezetten "nem túl távoli"-nak jelölte meg őket, és az ügyfélszerzést "fontos elemnek" nevezte.

---

## 1. Két különböző "ügyfélszerzés" — fontos megkülönböztetés

A kutatás során két, egymástól elvileg különböző dolog merült fel, mindkettő releváns, de más célra:

**A) A CRM TERMÉK saját növekedése (ajánlói/partnerprogram)** — amikor Rob (mint a CRM jövőbeli SaaS-üzemeltetője) új ELŐFIZETŐKET akar szerezni a saját CRM-termékéhez, ajánlási/jutalék-mechanizmussal. Ez a MiniCRM "meghívói programjának" mintája.

**B) A CRM ÜGYFELEINEK (coach, webdesigner, stb.) saját ügyfélszerzése** — vagyis maga a CRM ad-e eszközt ahhoz, hogy Rob (és a jövőbeli többi CRM-felhasználó) jobban lássa, honnan jönnek a SAJÁT leadjei/ügyfelei, és melyik csatorna éri meg. Ez a Salesforce lead-forrás/kampány-attribúció mintája.

Mindkettő fontos, de ne keveredjenek: **A)** egy jövőbeli üzleti/növekedési modul (amikor a CRM valóban SaaS-termékké válik), **B)** egy tényleges CRM-funkció, amit Rob és a jövőbeli felhasználók MA is használnának a saját vállalkozásuk ügyfélszerzésének mérésére.

## 2. A) MiniCRM "meghívói programja" — saját forrás, konkrét működés

A MiniCRM-nek van egy **partner/ajánlói programja**: aki egy új céget ajánl, és az adott cég 3 hónapon belül előfizet a MiniCRM-re, az ajánló jutalékot kap. Ez NEM egy funkció, amit a MiniCRM ügyfelei a SAJÁT üzletükhöz használhatnának — ez a MiniCRM saját, magának való ügyfélszerzési eszköze.

**Rob kérése alapján:** ha a mi CRM-ünk is eljut arra a pontra, hogy valódi SaaS-termékként más vállalkozásoknak (coachoknak, webdesignereknek, stb.) is eladjuk, **hasonló mechanizmus** legyen nálunk is: meglévő előfizetők ajánlhatnak új előfizetőket, jutalékkal/kedvezménnyel. Ehhez majd kellene:
- `referrals` tábla (ki ajánlott kit, mikor, milyen státuszban — meghívott/regisztrált/előfizetett)
- egyedi ajánló-link/kód minden accounthoz
- jutalék-/kedvezmény-szabály (pl. "1 hónap ingyen, ha az ajánlott 3 hónapon belül előfizet" — a MiniCRM-mintát követve)

**Ez csak akkor válik ténylegesen aktuálissá, amikor a CRM tényleg SaaS-termékké válik** (5-6. fázis, lásd `crm_projekt.md` 5. szekció Fázisterv) — de Rob kérésére MOST kerül be a tervek közé, nem a távoli scope-ba, hogy ne felejtődjön el és ne kelljen újra kutatni.

## 3. B) Salesforce ügyfélszerzési minták — mind a CRM-felhasználóknak szóló, valódi funkció

### 3.1 Referral Partner Program (partner-ajánlási program)

A Salesforce PRM (Partner Relationship Management) koncepciója szerint a **referral partnerek** (tanácsadók, iparági kapcsolatok) alacsonyabb ügyfélszerzési költséggel, magasabb minőségű leadeket hoznak, mint a fizetett hirdetés — mert a bizalmi tőkéjüket adják a bevezetéshez, és előre szűrik a nem odaillő érdeklődőket.

**Nálunk releváns forma:** nem feltétlenül egy teljes partner-portál (az túl nagy lenne most), hanem egy egyszerűbb minta: a `leads`/`contacts` `source` mezője már ma is rögzíti, honnan jött valaki (pl. "ajánlás") — **de nincs strukturált nyilvántartás arról, KI ajánlotta**. Egy `referred_by_contact_id` mező (self-referencing FK a `contacts` táblán) lehetővé tenné, hogy lássuk: melyik meglévő ügyfél hozta a legtöbb új ügyfelet — ez a Rob saját vállalkozásának (coaching, webdesign stb.) ügyfélszerzését segítené közvetlenül, kis munkával.

### 3.2 Lead Source / Campaign Influence (forrás- és kampány-attribúció)

A Salesforce megkülönbözteti:
- **Lead Source** — egyszerű mező: honnan jött a lead (ez NÁLUNK MÁR MEGVAN: `leads.source`, `contacts.source`).
- **Primary Campaign Source** — melyik KONKRÉT marketingkampány (nem csak általános csatorna, hanem pl. "2026 nyári Facebook-hirdetés") hozta a leadet — ez nálunk még nincs, csak egy szabad szöveges `source` mező van, nem egy strukturált "kampányok" lista.
- **Campaign Influence** — több kampány/érintkezési pont súlyozott hozzájárulása egy megnyert üzlethez (multi-touch attribúció) — ez egy fejlettebb, nálunk egyelőre túlzás lenne.

**Nálunk releváns, kis lépés:** egy egyszerű `campaigns` tábla (név, típus, indítás dátuma, költség — opcionális) + a `leads`/`deals` táblán egy `campaign_id` FK a jelenlegi szabad szöveges `source` mező MELLÉ (nem helyette). Ez lehetővé tenné a már meglévő `riportok-terv.md`-ben tervezett "bevétel szolgáltatás-típusonként" riport kiegészítését egy **"bevétel kampányonként/forrásonként"** nézettel — pontosan megválaszolva a klasszikus vállalkozói kérdést: "melyik hirdetésem/csatornám térül meg valójában?"

## 4. Összegzés — mi kerüljön a "nem túl távoli" backlogba

1. **Ajánló-/partnerprogram a CRM SAJÁT növekedéséhez** (MiniCRM-minta) — csak akkor aktuális, ha a CRM SaaS-termékké válik, de MOST rögzítve, nem a távoli scope-ban. **Változatlanul később esedékes** (5-6. fázis).
2. **"Ki ajánlotta?" mező a kontaktokon** (`referred_by_contact_id`, Salesforce referral-partner minta egyszerűsítve) ✅ **megvalósítva (2026-07-26)** — kontakt felvételi/szerkesztő űrlapon választható meglévő kontakt, a kontakt-részletek oldal mutatja mindkét irányt ("ki ajánlotta" + "ő kiket ajánlott").
3. **Egyszerű kampány-nyilvántartás + kampányonkénti riport** (`campaigns` tábla, Salesforce Lead Source/Campaign Influence minta egyszerűsítve) ✅ **megvalósítva (2026-07-26)** — önálló "Kampányok" menüpont (CRUD), a lead/deal felvételi és szerkesztő űrlapokon választható kampány a szabad szöveges `source` mező mellett, a kampány-részletek oldal mutatja a leadek/nyitott és nyert üzletek számát és a nyert bevételt.

Mindkettő tesztelve (`tests/Feature/CampaignTest.php` — account-elkülönítés, önhivatkozás elleni védelem, form-renderelés — 6/6 zöld; teljes csomag 31/31 zöld).

## 5. Kapcsolódó dokumentumok

- [`minicrm-inspiracio.md`](minicrm-inspiracio.md) 9c. szakasz — a MiniCRM "meghívói programjának" első leírása.
- [`connectsoft-inspiracio.md`](connectsoft-inspiracio.md) — a GoHighLevel-jellegű, TÁVOLI scope, amitől ez a dokumentum szándékosan elkülönül.
- [`riportok-terv.md`](riportok-terv.md) — a kampányonkénti riport ide kapcsolódna.
- [`crm_projekt.md`](../crm_projekt.md) 8. szekció — Ötlet-backlog, ahova a fenti tételek bekerültek.
