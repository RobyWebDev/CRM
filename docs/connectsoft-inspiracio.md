# ConnectSoft-inspiráció — kutatás és átvételi javaslatok

> Rob kérése (2026-07-25): "van még egy jó cucc, a ConnectSoft nevezetű összetett CRM és egyéb ügyfél- és kapcsolatkezelő rendszer, annak is nézz utána és a hasznos dolgok kerüljenek a todo-ba."
>
> **Fontos háttér-tény, amit a kutatás során találtam:** a **ConnectSoft (`connectsoft.hu`) nem önálló, saját fejlesztésű szoftver** — egy amerikai, all-in-one értékesítési/marketing platform, a **GoHighLevel (GHL)** magyar piacra szabott, white-label viszonteladása. A felület egyelőre nincs lefordítva magyarra, de a ConnectSoft magyar nyelvű oktatóanyagokkal, közösséggel és ügyfélszolgálattal egészíti ki. Ez azt jelenti, hogy a ténylegesen releváns funkció-forrás maga a GoHighLevel — ezt a dokumentumot a GHL nyilvánosan dokumentált funkciói alapján írtam, mert ott van a tényleges mélység.
>
> A MiniCRM egy **klasszikus B2B CRM** (adatlapok, pipeline, automatizáció, riportok). A GoHighLevel/ConnectSoft ezzel szemben egy **all-in-one értékesítési+marketing platform**, ami a klasszikus CRM-funkciók MELLÉ egy teljes marketing- és ügyfélszerzési eszköztárat is ad (weboldal/funnel-builder, e-mail/SMS-automatizáció, hírnév-kezelés, fizetés, tagságkezelés, közösségi média). Ez sok szempontból **túlmutat egy "CRM" hagyományos definícióján** — ezért minden pontnál külön jelzem, hogy ez tényleg CRM-funkció-e, vagy inkább egy szomszédos (marketing/website) termékkategória, amit Rob-nak külön kell mérlegelnie, mielőtt beépítenénk.

---

## 1. Mi az a GoHighLevel/ConnectSoft — teljes funkciólista

**Weboldal/funnel-builder:** landing oldalak, értékesítési tölcsérek, webinárium-funnelek, e-commerce oldalak, 1000+ sablonnal, kód nélkül.

**E-mail + SMS automatizáció:** vizuális workflow-builder, korlátlan e-mail/SMS küldés (forgalmi alapú díjazással), automata válaszok.

**Telefónia:** call center, hívásfelvétel, automata SMS-válasz.

**Időpontfoglalás:** naptár-widget, Google Naptár/Zoom/Google Meet-integráció, díjas és ingyenes foglalások.

**CRM-mag:** kontaktok, ügyfélstátuszok, **pipeline-nyomkövetés** (ez fedi a mi már meglévő Pipeline modulunkat).

**Egységes beérkező üzenetfolyam ("Unified Conversations Inbox"):** SMS, e-mail, Facebook Messenger, Instagram DM, WhatsApp, Google Business Messages és élő chat **egyetlen közös beérkező üzenetlistában**, a kontaktus teljes kontextusával együtt megjelenítve.

**Hírnév-/vélemény-kezelés ("Reputation Management"):** Google/Facebook-értékelések automatikus bekérése és kezelése — a piacon ez önálló szoftverkategória (pl. BirdEye), amit a GHL beépítve kínál.

**Fizetés/termékértékesítés:** Stripe-integráció, egyszeri/részletes/előfizetéses díjazás.

**Tagság/kurzusplatform:** e-learning, zárt közösségi felület.

**Affiliate-modul:** többszintű jutalékrendszer, cookie-alapú követés.

**Közösségimédia-ütemezés:** Facebook, Instagram, TikTok, LinkedIn, YouTube, Pinterest posztok előre ütemezve.

**"Snapshot" — kulcsfontosságú architekturális minta:** egy teljes al-fiók (sub-account) konfigurációja — funnel-ek, automatizmusok, e-mail-sablonok, pipeline-ok, egyedi mezők, dokumentumsablonok — **egyetlen, újrahasznosítható csomagként lementhető és új ügyfélnek egy kattintással telepíthető**. A tényleges ügyféladatok (kontaktok, beszélgetések, hívásnaplók, foglalások, vélemények) **szándékosan SOHA nem részei egy Snapshotnak** — ez tisztán adatvédelmi/adatelkülönítési döntés.

**Ügynökségi al-fiók (sub-account) architektúra:** egy ügynökség egyetlen felületről kezel korlátlan számú, egymástól teljesen elkülönített ügyfél-fiókot, mindegyiknek saját CRM-je, automatizmusa, pipeline-ja, beállításai vannak.

## 2. Mi az, ami TÉNYLEGES CRM-funkció és releváns nálunk

- **Egységes beérkező üzenetfolyam (Unified Inbox)** ✅ **Erősen javasolt, általánosan hasznos, nem csak kisvállalkozói ötlet.** Jelenleg a mi rendszerünkben egy kontakt/lead adatlapján teendők és jegyzetek vannak, de nincs egy hely, ahol az illetővel folytatott TÉNYLEGES kommunikáció (e-mail-váltás, hívás, SMS) egy idővonalon látszana. Ez egy klasszikus CRM best practice (Salesforce "Activity Timeline", HubSpot "Conversations" ugyanezt csinálja) — **később, amikor lesz e-mail-/SMS-/telefónia-integráció, ez legyen az architekturális cél**: minden csatorna egyetlen, kontaktonkénti idővonalon fusson össze, ne külön modulokban. Ez összefügg a korábban rögzített "aktivitás-idővonal" backlog-ötlettel (`crm_projekt.md` 8. szekció, "Egyéb CRM best practice ötletek" pont) — ez a GHL-kutatás megerősíti és konkretizálja: NE csak jegyzet/teendő legyen az idővonalon, hanem a tényleges kommunikáció is, amint lesz hozzá integráció.
- **"Snapshot" — cserélhető/klónozható account-sablon** ✅ **Közvetlenül ráépül a már meglévő `pipeline-sablonok.md` ötletünkre**, csak kiterjeszti: ne csak a pipeline-lépések legyenek sablonozhatók szolgáltatásonként, hanem egy **teljes account-konfiguráció** (pipeline-ok + egyedi mezők + dokumentumsablonok + automatizmus-szabályok) egyben, egy kattintással klónozható/telepíthető legyen egy új accountnak — ez különösen fontossá válik, ha Rob tényleg SaaS-termékké fejleszti (minden új előfizető egy kattintással megkaphatja a "coaching" vagy "webdesign" kezdő-konfigurációt). **Fontos elv, amit a GHL is követ és nálunk is érdemes betartani:** a sablon SOHA ne tartalmazzon tényleges ügyféladatot (kontaktokat, jegyzeteket, beszélgetéseket) — csak struktúrát.
- **Hírnév-/vélemény-kezelés (Google/Facebook review automatizálás)** — **részben CRM-funkció, részben marketing-eszköz.** A mag ötlet (elégedett ügyfelet automatikusan megkérni egy Google-értékelésre egy lezárt projekt/retainer után) jól illeszkedne a meglévő automatizáció-backlogunkhoz ("ha a projekt lezárult → X nap múlva automatikus e-mail: kérünk egy értékelést"). **Alacsony prioritású, de olcsó és általános haszon** — bármilyen szolgáltatónak (nemcsak kisvállalkozásnak) hasznos.
- **Időpontfoglalás naptár-widgettel** — **részben CRM-funkció.** Egy nyilvános, ügyfél által kitölthető foglalási link (pl. "foglalj egy 30 perces konzultációt Robbal"), ami automatikusan Lead-et vagy Kontaktot hoz létre és Teendőt/Naptár-eseményt generál. Ez összefügg a már meglévő "webes űrlap → automatikus lead-befogás" MiniCRM-backlog-tétellel, csak azt naptár-idősáv-választással egészíti ki. **Középtávú, jó backlog-ötlet.**

## 3. Mi az, ami NEM klasszikus CRM-funkció, hanem szomszédos termékkategória — Rob döntésére vár

Ezeket **NEM javaslom automatikusan beépítésre**, mert érdemben kibővítenék a projekt hatókörét egy "CRM"-ből egy "teljes marketingplatform" irányába — ez explicit, önálló döntést igényel Robtól, ha egyáltalán akarja:

- **Weboldal/landing oldal/funnel-builder** — ez gyakorlatilag egy önálló, komplex website-builder termék (drag-and-drop szerkesztő, sablon-motor). Rob-nak amúgy is van saját, meglévő webdesign-szolgáltatása/eszköze — inkább egy **linkelési/integrációs pont** lenne értelmes (pl. egy landing oldal beküldött űrlapja webhookkal létrehoz egy Lead-et nálunk), nem egy saját built-in oldalszerkesztő.
- **Tagság-/kurzusplatform, affiliate-modul, közösségimédia-ütemezés, termékértékesítés Stripe-fizetéssel** — ezek mind önálló, nagy termékkategóriák (LMS, affiliate-tracking, social media management, e-commerce), amik jóval túlmutatnak egy CRM hatókörén. **Nem javaslom, hogy ezeket a CRM-be építsük** — ha Robnak ezekre ténylegesen szüksége van a saját vállalkozásához, azok inkább külön, kész SaaS-eszközök (pl. tényleg egy ConnectSoft/GHL-előfizetés) használatával oldhatók meg, a mi CRM-ünk pedig API/webhook-integrációval kapcsolódhatna hozzájuk (pl. "ConnectSoft-ban lezárt értékesítés → webhook → nálunk Deal `won` állapotba kerül").

## 4. Összegzés — mi kerüljön a backlogba

1. **Egységes kommunikációs idővonal (Unified Inbox-elv)** — architekturális célként rögzítve, amint lesz e-mail/SMS/telefónia-integráció (lásd `crm_projekt.md` 8. szekció).
2. **Account-szintű "Snapshot"/sablon-klónozás** — a `pipeline-sablonok.md` ötlet kiterjesztése teljes account-konfigurációra.
3. **Automatikus review-kérés lezárt projekt/retainer után** — kis, olcsó automatizáció-minta.
4. **Nyilvános időpontfoglaló link** — a webes lead-befogás ötlet kiterjesztése naptár-idősáv-választással.
5. **Explicit NEM javasolt automatikus beépítésre:** website/funnel-builder, tagság/kurzus, affiliate, social media ütemezés, built-in termékértékesítés — ezek külön termékkategóriák, csak integrációs pontként érdemes rájuk gondolni, ha Rob úgy dönt.

## 5. Kapcsolódó dokumentumok

- [`minicrm-inspiracio.md`](minicrm-inspiracio.md) — a "klasszikus CRM" oldali kutatás, amivel ez a dokumentum kiegészül.
- [`crm_projekt.md`](../crm_projekt.md) 8. szekció — Ötlet-backlog, ahova a fenti tételek bekerültek.
- [`pipeline-sablonok.md`](pipeline-sablonok.md) — a "Snapshot"-ötlet ide kapcsolódik, mint kiterjesztés.
- [`architektura.md`](architektura.md) — az `integrations`/`api_keys` táblák relevánsak a webhook-alapú kapcsolódási pontokhoz.
