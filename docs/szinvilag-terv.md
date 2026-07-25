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

## 3. Sötét téma (alapértelmezett) — AI-javaslat: OKLCH-alapú, perceptuálisan egyenletes skála

**Rob nyitott kérdésére** ("van-e jobb javaslatom, mint pontosan a cib.hu zöldje") — igen: ahelyett, hogy egy másik weboldal hex-kódjait vennénk át szó szerint, a **CSS Color Module 4 `oklch()`** színmodellben terveztem újra a paletta — ez a jelenleg legmodernebb, minden friss böngészőben (2023+) támogatott megközelítés, mert *perceptuálisan egyenletes*: ha a világosság (`L`) értékét lépésről lépésre azonos mértékben növelem, a szem is azonos mértékű változást érzékel (ellentétben a hex/HSL-lel, ahol ez nem garantált — ott könnyen "csúnyán" ugrálhat a kontraszt két szomszédos árnyalat között). Ez különösen fontos egy **napi sok órán át használt CRM-nél** — a cib.hu zöldjének élénksége egy marketing-honlapon jó, de egy munkaeszköznél a kissé visszafogottabb, konzisztens léptékű változat kényelmesebb a szemnek.

A hue (színárnyalat, ~165°, ugyanaz a teal-zöld sáv, mint a cib.hu-nál) megmaradt — tehát vizuálisan ugyanabba a családba tartozik, amit Rob kért, csak szisztematikusan felépítve. Minden alábbi szín `oklch()`-ben van megadva (elsődleges deklaráció) + hex fallback-kel (a nagyon régi böngészőknek, amik nem értik az `oklch()`-öt — a CSS kaszkád automatikusan a hexet használja, ha az `oklch()` érvénytelen az adott böngészőben, nem kell `@supports`):

| Token | oklch() | Hex (fallback) | Szerep | Kontraszt |
|---|---|---|---|---|
| `--bg-sunken` | `oklch(24% 0.045 165)` | `#05261a` | Input mezők, "besüllyesztett" felületek | fehér szöveg: **~14:1** |
| `--bg-page` | `oklch(33% 0.06 165)` | `#0f3f2e` | Oldal alap háttere | — |
| `--bg-surface` | `oklch(39% 0.065 165)` | `#1c503c` | Kártyák, panelek (egy szinttel "emeltebb") | — |
| `--bg-surface-hover` | `oklch(45% 0.07 165)` | `#2a624b` | Hover/aktív állapot kártyákon | — |
| `--text-primary` | `oklch(97% 0.006 165)` | `#f2f6f4` | Fő szövegszín | `--bg-page`-en: **10.9:1** ✅ AAA |
| `--text-secondary` | `oklch(83% 0.025 165)` | `#b9cdc3` | Másodlagos/segédszöveg | `--bg-page`-en: **7.1:1** ✅ AAA |
| `--text-muted` | `oklch(72% 0.035 165)` | `#91ac9f` | Halvány, nem kritikus szöveg (pl. időbélyeg) | `--bg-page`-en: **4.9:1** ✅ AA |
| `--border-subtle` | `oklch(48% 0.05 165)` | `#426757` | Díszítő elválasztóvonal (nem hordoz jelentést) | — (nem kritikus, nincs 3:1 elvárás) |
| `--border-strong` | `oklch(68% 0.085 165)` | `#62a98b` | Input-keret, fókusz-gyűrű, kritikus UI-határvonal | `--bg-page`-en: **4.3:1** ✅, `--bg-surface`-en: **3.4:1** ✅ |
| `--accent-primary` | `oklch(87% 0.17 100)` | `#efd62f` | Elsődleges CTA-gomb háttere (arany-sárga, a cib.hu sárgájának visszafogottabb rokona) | `--bg-page`-en: **8.1:1** ✅, sötétzöld szöveg rajta: **8.1:1** ✅ |
| `--status-success` | `oklch(78% 0.14 150)` | `#6fd087` | Sikeres állapot (pl. "won" deal, fizetve) | `--bg-page`-en: **6.2:1** ✅ |
| `--status-danger` | `oklch(78% 0.12 25)` | `#fb9890` | Hiba, "lost" deal, lejárt határidő | `--bg-page`-en: **5.6:1** ✅ |
| `--status-warning` | `oklch(85% 0.15 80)` | `#ffc249` | Figyelmeztetés (pl. közelgő határidő) | `--bg-page`-en: **7.4:1** ✅ |
| `--status-info` | `oklch(80% 0.09 220)` | `#77cce6` | Semleges infó-jelzés | `--bg-page`-en: **6.5:1** ✅ |

**CSS-ben a fallback-mintázat így néz ki** (a második deklaráció felülírja az elsőt minden böngészőben, ami érti az `oklch()`-öt; ami nem érti, ott érvénytelen szabályként eldobódik, és a hex marad érvényben):

```css
:root {
  --bg-page: #0f3f2e;
  --bg-page: oklch(33% 0.06 165);
}
```

**Ha Rob mégis ragaszkodna a cib.hu pontos zöldjéhez** (`#0b4a35`) a `--bg-page`-nél: technikailag simán behelyettesíthető, a kontrasztarányok (lásd az előző, 2026-07-25-i első verzió) akkor is rendben voltak — ez itt egy **javaslat**, nem letiltás.

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
- [`tipografia-layout-terv.md`](tipografia-layout-terv.md) — fluid/reszponzív méretezés (`clamp()`, container query-k, modern CSS-egységek) — a színvilág természetes párja.
- [`csomag-terv.md`](csomag-terv.md) — Tailwind CSS mint a frontend alapja, ahova ez a paletta konfigurációként bekerül.
