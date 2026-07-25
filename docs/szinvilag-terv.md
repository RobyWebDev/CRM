# Színvilág-terv — sötét alapértelmezett téma, WCAG 2.x szerint

> Rob kérése (2026-07-25): a felület alapból **sötét**, de **nem fekete** hátteret használjon — irányadó referencia a **cib.hu sötétzöldje**. A színeknél a legfrissebb WCAG (2.1/2.2) elveket kell figyelembe venni. Ez a `crm_projekt.md` 3. szekció "Akadálymentesség / méretezhető szöveg" pontjának közvetlen folytatása — ugyanaz a felhasználói kör (Rob + vegyes látású jövőbeli felhasználók) indokolja.

---

## 1. Forrás — a cib.hu tényleges márkaszíne

A cib.hu élő CSS-éből (nem becslésből) kinyert, ténylegesen használt zöld árnyalatok:

| Szín | Hex | Előfordulás a cib.hu CSS-ében | Szerep nálunk |
|---|---|---|---|
| CIB sötétzöld (fő) | `#0b4a35` | 489× (domináns szín) | Ez a mi sötét téma **alap háttérszíne** |
| CIB még sötétebb zöld | `#073325` | 30× | "besüllyesztett" felületekhez (input mezők, mélyebb szintek) |
| CIB élénk sárga (kiegészítő) | `#fff000` / `#fcee50` | 289× / 50× | Nálunk **nem kötelező**, csak opcionális kiegészítő accent-szín, ha Rob később élénkebb figyelemfelhívó színt szeretne (pl. fontos CTA-gombhoz) |

*(Forrás: `https://www.cib.hu` élő stíluslapjának lekérése és a benne előforduló hex-kódok gyakoriság szerinti elemzése, 2026-07-25.)*

## 2. WCAG 2.x alapelvek, amik szerint a paletta készült

- **1.4.3 Kontraszt (minimum) — AA:** törzsszöveg és háttere között legalább **4.5:1** kontraszt.
- **1.4.6 Kontraszt (fokozott) — AAA:** ahol ésszerű, **7:1**-et céloztunk (Rob kiemelt akadálymentességi igénye miatt inkább AAA, mint a minimum AA).
- **1.4.11 Nem-szöveges kontraszt (WCAG 2.1 új szabálya):** UI-komponensek (gombkeretek, input-keretek, fókusz-jelzés) és a szomszédos háttér között legalább **3:1**.
- **2.4.11 Fókusz nem takarható (WCAG 2.2 új szabálya):** a billentyűzet-fókusz jelzése (pl. Tab-bal navigálva) sosem takarható el más elemmel — ezt a fókusz-gyűrű z-index/pozíció tervezésénél kell betartani a tényleges Blade/CSS megvalósításnál.
- **2.5.8 Cél mérete (minimum) — WCAG 2.2:** interaktív elemek (gombok, linkek mobilon) legalább 24×24 px érintési célterület — ez összhangban van a meglévő mobilbarát/reszponzív elvvel (`ui-wireframe-terv.md`).
- Minden alábbi kontrasztarány **ténylegesen kiszámolt** érték (relatív luminancia alapján, a WCAG hivatalos képlete szerint), nem becslés.

## 3. Sötét téma (alapértelmezett)

| Token | Hex | Szerep | Kontraszt |
|---|---|---|---|
| `--bg-page` | `#0b4a35` | Oldal alap háttere (a cib.hu zöldje) | — |
| `--bg-sunken` | `#073325` | Input mezők, "besüllyesztett" felületek | fehér szöveg: **13.9:1** |
| `--bg-surface` | `#12573f` | Kártyák, panelek (egy szinttel "emeltebb") | fehér szöveg: **7.9:1** |
| `--text-primary` | `#f5f7f6` | Fő szövegszín | `--bg-page`-en: **9.5:1** ✅ AAA |
| `--text-secondary` | `#b9c9c2` | Másodlagos/segédszöveg | `--bg-page`-en: **6.0:1** ✅ AA(+) |
| `--text-muted` | `#8ca79b` | Halvány, nem kritikus szöveg (pl. időbélyeg) | `--bg-page`-en: **4.0:1** — csak kis/nem-kritikus szöveghez |
| `--border-subtle` | `#1c6a4e` | Díszítő elválasztóvonal (nem hordoz jelentést) | — (nem kritikus, nincs 3:1 elvárás) |
| `--border-strong` | `#5fa98c` | Input-keret, fókusz-gyűrű, kritikus UI-határvonal | `--bg-page`-en: **3.7:1** ✅, `--bg-surface`-en: **3.1:1** ✅ |
| `--accent-primary` | `#f5d90a` | Elsődleges CTA-gomb háttere (a cib.hu sárgájának visszafogottabb, WCAG-barát változata) | sötétzöld szöveg rajta: **7.2:1** ✅ |
| `--status-success` | `#5fd685` | Sikeres állapot (pl. "won" deal, fizetve) | `--bg-page`-en: **5.6:1** ✅ |
| `--status-danger` | `#ffb3a8` | Hiba, "lost" deal, lejárt határidő | `--bg-page`-en: **6.0:1** ✅ |
| `--status-warning` | `#ffd166` | Figyelmeztetés (pl. közelgő határidő) | `--bg-page`-en: **7.1:1** ✅ |
| `--status-info` | `#8fd6f0` | Semleges infó-jelzés | `--bg-page`-en: **6.4:1** ✅ |

**Elsődleges CTA-gomb:** `--accent-primary` (`#f5d90a`) háttér + sötétzöld (`#0b4a35`) szöveg → **7.2:1**, messze AA felett.

## 4. Világos téma (másodlagos, választható)

*Nem MVP-blokkoló, de érdemes már most is tervezni, mert lesznek jól látó, fényes környezetben dolgozó felhasználók is, akiknek a világos téma kényelmesebb lehet — a téma-váltó egyszerű CSS-osztály/`data-theme` attribútum lesz.*

| Token | Hex | Szerep | Kontraszt |
|---|---|---|---|
| `--bg-page` | `#f7faf9` | Oldal alap háttere (nagyon halvány zöldes-fehér, nem tiszta fehér) | — |
| `--bg-surface` | `#ffffff` | Kártyák, panelek | — |
| `--text-primary` | `#132b21` | Fő szövegszín | `--bg-page`-en: **14.3:1** ✅ AAA |
| `--text-secondary` | `#3f5c50` | Másodlagos szöveg | `--bg-page`-en: **7.0:1** ✅ AAA |
| `--accent-primary` / link | `#0e6247` | A cib.hu-zöld világos témára hangolt, kicsit világosított változata — linkszín, gombkeret | `--bg-page`-en: **7.0:1** ✅ |
| CTA-gomb háttér | `#0b4a35` | Ugyanaz a márkazöld, mint a sötét témában | fehér szöveg rajta: **10.3:1** ✅ |

## 5. Megvalósítási irány (amikor a Blade/Tailwind-munka elkezdődik)

- A fenti tokenek **CSS egyéni tulajdonságként** (`:root { --bg-page: #0b4a35; ... }`) és Tailwind-konfigurációs színekként is bekerülnek, hogy a Blade-sablonokban szemantikus osztálynevekkel dolgozzunk (`bg-page`, `text-primary` stb.), NE nyers hex-kódokkal — így egy helyen módosítható a teljes paletta.
- Alapértelmezett téma: **sötét** (`data-theme="dark"` vagy nincs attribútum = dark az alap). Világos téma választható kapcsolóval, ami a felhasználó profiljában tárolódik (hasonlóan a már tervezett `locale` mezőhöz — ha igény lesz rá, egy `theme_preference` mező kerülhet a `users` táblára, amikor a Beállítások/Profil képernyő megépül; nem kell most külön migráció).
- **Sosem szín az egyetlen jelzés** (WCAG 1.4.1): pl. a pipeline "won"/"lost" állapotát ne csak zöld/piros szín jelezze, hanem ikon vagy szöveges címke is kísérje — ez különösen fontos színtévesztő felhasználók miatt.
- A `docs/ui-wireframe-terv.md` 8. szekciójában található akadálymentességi szabályok (relatív mértékegységek, böngésző-nagyítás, min. betűméret) ugyanúgy érvényesek, függetlenül a témától.

## 6. Kapcsolódó dokumentumok

- [`ui-wireframe-terv.md`](ui-wireframe-terv.md) — akadálymentesség/méretezhető szöveg (8. szekció), fő képernyők wireframe-je.
- [`csomag-terv.md`](csomag-terv.md) — Tailwind CSS mint a frontend alapja, ahova ez a paletta konfigurációként bekerül.
