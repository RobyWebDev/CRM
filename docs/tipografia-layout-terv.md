# Tipográfia és layout — fluid/reszponzív méretezés

> Rob kérése (2026-07-25): "ahol lehet a legfrissebb webdesign elveket és méretkezeléseket használd, azaz clamp és hasonlók betűmérethez, spacinghez és mindenhez, hogy reszponzív és fluid legyen, azaz minden eszközön profin jelenjen meg." Ez a `docs/szinvilag-terv.md` és `docs/ui-wireframe-terv.md` (8. szekció, akadálymentesség) természetes párja — együtt adják ki a teljes vizuális alapréteget, amit a Blade/Tailwind-munka kezdetén egyszer kell megépíteni, utána mindenhol újrahasznosítható.

---

## 1. Miért "fluid" a klasszikus töréspontok (breakpoint) helyett/mellett

A hagyományos megközelítés (pl. `text-sm` mobilon, `text-lg` desktopon, egy-egy ugrással a `md:`/`lg:` töréspontnál) **ugrásszerűen** vált méretet — a kettő közötti eszközméreteken (pl. tablet-fekvő, kis laptop) a szöveg vagy túl kicsi, vagy túl nagy marad a következő töréspontig. A **fluid** megközelítés (CSS `clamp()` + viewport-egység) folyamatosan, simán skálázza a méretet a legkisebb és legnagyobb eszköz között — ez a jelenlegi (2024-2026-os) webdesign legjobb gyakorlata, minden modern böngészőben (Baseline, széles támogatottság) elérhető.

**A két megközelítés nem kizárja, hanem kiegészíti egymást:** a fluid `clamp()` adja az alapértéket minden elemhez, a Tailwind töréspontjai (`sm`/`md`/`lg`) pedig ott maradnak, ahol tényleg **elrendezés-váltás** kell (pl. táblázat → kártyás nézet mobilon, lásd `ui-wireframe-terv.md` 7. szekció) — ott ugyanis nem fokozatos átmenetről van szó, hanem valódi szerkezetváltásról.

## 2. Fluid tipográfia — `clamp()` alapú típusskála

A `clamp(min, preferred, max)` szintaxis: a `preferred` érték viewport-szélesség alapú (`vw`), de sosem megy a `min` alá, és sosem lépi túl a `max`-ot. Alapképlet (352px és 1280px viewport-szélesség közt skálázva):

```css
:root {
  /* Alap (törzsszöveg) — SOSEM megy 1rem (16px) alá, lásd ui-wireframe-terv.md 8. szekció */
  --step-0: clamp(1rem, 0.95rem + 0.25vw, 1.125rem);       /* body: ~16px → ~18px */
  --step-1: clamp(1.125rem, 1.05rem + 0.35vw, 1.3rem);     /* kiemelt szöveg, kártya-cím */
  --step-2: clamp(1.3rem, 1.15rem + 0.65vw, 1.6rem);       /* H3 */
  --step-3: clamp(1.6rem, 1.35rem + 1.1vw, 2.1rem);        /* H2 */
  --step-4: clamp(2rem, 1.6rem + 1.8vw, 2.75rem);          /* H1 */
  --step--1: clamp(0.875rem, 0.85rem + 0.1vw, 0.95rem);    /* segédszöveg — csak nem-kritikus infóhoz, lásd ui-wireframe-terv.md */
}
```

- Minden méret **`rem`-ben** (relatív a böngésző alap betűméretéhez) — ha a felhasználó a böngészőjében megnöveli az alap betűméretet (amit egy gyengénlátó felhasználó, mint Rob, gyakran megtesz), a `clamp()` MINDEN lépcsője arányosan nő, mert a `rem` az alapból indul ki, nem abszolút pixelben van rögzítve.
- A `preferred` tag (`0.95rem + 0.25vw` stb.) egy lineáris interpolációt ad 352px és 1280px viewport közt — ezeket egy fluid-típusskála kalkulátorral (pl. utopia.fyi elve, nem szükséges külön csomag) generáltuk, ökölszabályként: minél nagyobb a lépték (H1), annál nagyobb a `vw`-szorzó, hogy a nagy címek jobban reagáljanak a képernyőméretre, mint a törzsszöveg.

## 3. Fluid spacing (térköz) skála

Ugyanez az elv térközökre (padding/margin/gap) is — így a kártyák, gombok, szekció-távolságok is arányosan lélegeznek nagyobb/kisebb kijelzőn, nem csak a betű:

```css
:root {
  --space-xs:  clamp(0.5rem, 0.45rem + 0.25vw, 0.625rem);
  --space-sm:  clamp(0.75rem, 0.65rem + 0.5vw, 1rem);
  --space-md:  clamp(1.25rem, 1.05rem + 1vw, 1.75rem);
  --space-lg:  clamp(2rem, 1.6rem + 2vw, 3rem);
  --space-xl:  clamp(3rem, 2.3rem + 3.5vw, 5rem);
}
```

Tailwind-integráció: ezek a `tailwind.config.js` (vagy Tailwind v4 esetén a CSS-alapú `@theme` blokk) `spacing`/`fontSize` kulcsai alá kerülnek egyedi értékként (pl. `fontSize: { 'fluid-body': 'var(--step-0)' }`), így a Blade-sablonokban Tailwind-osztályként (`text-fluid-body`, `p-fluid-md`) használhatók, nem kell inline CSS-t írni.

## 4. Egyéb modern CSS-technikák, amiket érdemes bevezetni

- **Container query-k (`@container`)** a kártya-szintű komponenseknél (kanban-kártya, dashboard-csempe): a komponens a SAJÁT szülőjének szélességéhez igazodik, nem a teljes viewporthoz — ez azért fontos, mert egy kanban-kártya máshogy fér el egy szűk oldalsávban, mint egy teljes szélességű listanézetben, függetlenül attól, hogy a telefon vagy a monitor mekkora. Minden modern böngészőben támogatott (2023 óta Baseline).
- **Dinamikus viewport-egységek (`dvh`/`svh`/`lvh`) a `vh` helyett:** mobil böngészőknél a címsor/címke ki-be csúszása miatt a klasszikus `100vh` rosszul számol (vagy túl sok, vagy túl kevés helyet foglal) — a `dvh` (dynamic viewport height) ezt korrigálja. Teljes magasságú elrendezéseknél (pl. bejelentkezés-képernyő) ezt használjuk `vh` helyett.
- **Logikai tulajdonságok (`margin-inline`, `padding-block` a `margin-left/right`, `padding-top/bottom` helyett):** nyelv-/irányfüggetlen — ha a `lokalizacio-terv.md` szerint egyszer jobbról-balra író nyelv (pl. arab) is bekerülne, nem kell újraírni a CSS-t, automatikusan tükröződik.
- **`:focus-visible`** a `:focus` helyett a fókusz-gyűrűnél — így az egérrel kattintó felhasználónak nem jelenik meg zavaró keret minden kattintásnál, de billentyűzettel navigálva (Tab) igen, ott ahol WCAG 2.4.11 (fókusz nem takarható, lásd `szinvilag-terv.md`) megköveteli.
- **`accent-color` CSS-tulajdonság:** natív form-elemek (checkbox, radio, range) márkaszínre hangolása böngésző-natív stílus felülírása/JS nélkül — a `--accent-primary` tokent kapja értékül.
- **`prefers-reduced-motion` media query:** aki a rendszerszintű "mozgás csökkentése" beállítást bekapcsolta (gyakori időseknél/érzékeny felhasználóknál), annak minden animáció/átmenet kikapcsolódik vagy minimálisra csökken.
- **`prefers-contrast: more` media query:** ha a rendszer "fokozott kontraszt" módot jelez, a `--border-subtle`/`--text-muted` tokenek helyett automatikusan a `--border-strong`/`--text-secondary` erősebb változatuk aktiválódhat — előkészítve, nem MVP-blokkoló.

## 5. Amit ez NEM jelent (hogy ne legyen félreértés)

- A Tailwind `sm:`/`md:`/`lg:` töréspontjai **nem tűnnek el** — ott maradnak, ahol tényleg elrendezés-váltás kell (táblázat↔kártya, lásd `ui-wireframe-terv.md`).
- Nem kell külön npm-csomag a `clamp()`-hoz — natív CSS, a fenti egyéni tulajdonságok simán bekerülnek egy `resources/css/app.css` alapréteg-fájlba (vagy Tailwind v4 `@theme` blokkba).
- Ez a dokumentum tervezési alapréteg — a tényleges bevezetés akkor történik, amikor a Blade-sablonok/CSS-build ténylegesen elkezdődik (3. fázis UI-munkája), nem igényel most kódolást.

## 6. Kapcsolódó dokumentumok

- [`szinvilag-terv.md`](szinvilag-terv.md) — színpaletta, WCAG 2.1/2.2 kontraszt.
- [`ui-wireframe-terv.md`](ui-wireframe-terv.md) — akadálymentesség (8. szekció), fő képernyők, töréspont-alapú reszponzivitás (7. szekció).
- [`csomag-terv.md`](csomag-terv.md) — Tailwind CSS mint a frontend alapja.
- [`lokalizacio-terv.md`](lokalizacio-terv.md) — a logikai CSS-tulajdonságok jövőbeli RTL-relevanciája.
