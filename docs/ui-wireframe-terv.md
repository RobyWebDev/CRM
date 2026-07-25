# UI wireframe terv — fő alkalmazás-képernyők

> Az [`admin-felulet-terv.md`](admin-felulet-terv.md) a "Beállítások" (service_types/pipelines/custom_fields) képernyőket írta le — ez a fájl a **mindennapi használatra szánt fő képernyőket**. Szöveges wireframe, nem grafikai terv; a cél, hogy amint elkezdődik a Blade-sablonok írása, legyen kész user-flow. Szerver-oldali Blade + Tailwind + Alpine.js alapon (lásd `csomag-terv.md`), mobilbarát/reszponzív alapkövetelménnyel (`crm_projekt.md` 3. szekció).
> Utolsó frissítés: 2026-07-25.

## 1. Navigáció (fő menü)

```
[Logo/Account név]   Dashboard | Kontaktok | Pipeline-ok | Projektek | Feladatok | (Beállítások — csak owner/admin)   [User menü ▾]
```

Mobilon: hamburger-menü, azonos sorrendben, lenyíló listaként.

## 2. Dashboard (kezdőoldal bejelentkezés után)

- Felül: gyors számok — "Nyitott dealek", "Aktív projektek", "Ma esedékes feladatok", "Lejárt feladatok" (kártyák, kattinthatók, a megfelelő szűrt listára visznek).
- Alatta: "Legutóbbi aktivitás" lista (a saját `activity_log` bejegyzéseiből, csak a sajátjaim vagy csapat, szerepkörtől függően).
- Jobb oldalt (asztali nézetben) vagy alul (mobilon): "Ma esedékes feladataim" mini-lista, pipipával kipipálható.

## 3. Kontaktok

**Lista nézet:** táblázat (Név, Szervezet, E-mail, Telefon, Felelős), kereséssel (név/e-mail) és szűréssel (szervezet, felelős, szolgáltatás-típus szerint — utóbbi az adott kontakthoz tartozó dealeken keresztül). "+ Új kontakt" gomb.

**Kontakt-részletek:** fejlécben alapadatok (név, e-mail, telefon, szervezet) + GDPR-hozzájárulás állapota. Fülek/szekciók:
- Egyedi mezők (a `custom_field_definitions` alapján dinamikusan renderelve — ez a legfontosabb "no-code" pont a felületen).
- Kapcsolódó dealek/projektek listája.
- Jegyzetek (időrendi lista, "+ Új jegyzet" gyors beviteli mezővel).
- Feladatok.
- Dokumentumok (linkek).
- "GDPR export" és "Törlés" gombok lent, megerősítő dialógussal.

Mobilon a fülek helyett egymás alatti szekciók, összecsukható (accordion) formában.

## 4. Pipeline / Kanban nézet

- Fejlécben: pipeline-választó (ha egy account több pipeline-t is használ), "+ Új deal" gomb.
- Oszlopok = `pipeline_stages`, a stage `color` mezője adja az oszlopfejléc színét.
- Minden oszlopban kártyák = `deals` (cím, érték, kontakt neve, felelős avatarja).
- Kártya húzása másik oszlopba → `POST /api/v1/deals/{id}/move-stage` hívás (lásd `api-tervek.md`), ami kiváltja a `DealStageChanged` eseményt.
- Mobilon a kanban vízszintesen görgethető, vagy (kisebb képernyőn) oszloponkénti lapozás — a kártyahúzás mobilon nehézkes, ezért ott egy egyszerű "Áthelyezés" legördülő is legyen a kártyán/részletnézetben, ne csak drag-and-drop.
- Kártyára kattintva: deal-részletek modal/oldal (érték, várható zárás dátuma, egyedi mezők, kapcsolódó kontakt, jegyzetek).

## 5. Projektek

**Lista nézet:** hasonló a kontaktokéhoz, szűrhető státusz (`active`/`on_hold`/`completed`/`cancelled`) és szolgáltatás-típus szerint.

**Projekt-részletek:** fejléc (cím, státusz, határidő, büdzsé) + szekciók: egyedi mezők, feladatok (checklist-szerű, kipipálható), jegyzetek, dokumentumok. Ha a projekt egy dealből jött létre automatikusan (lásd `architektura.md` 5. pont), egy link mutat vissza az eredeti dealre.

## 6. Feladatok

- Önálló "Feladatok" nézet: minden rám kiosztott feladat, szűrhető (ma / e héten / lejárt / összes), kereszthivatkozással arra, mihez tartozik (kontakt/deal/projekt neve, kattintható link).
- Gyors "kész" jelölés checkbox-szal, azonnali frissüléssel (Alpine.js, oldal-újratöltés nélkül).

## 7. Reszponzivitás — általános szabályok

- Táblázatok mobilon kártyás nézetbe váltanak (minden sor egy kártya, oszlopnevek label-ként a mező mellett) — ez a leggyakoribb, jól bevált minta táblázat → mobil átalakításra.
- Minden elsődleges cselekvés-gomb ("+ Új...", "Mentés") mobilon is könnyen elérhető legyen (nem csak asztali hover-menüben).
- Tailwind alapértelmezett breakpointjai (`sm`/`md`/`lg`) elegendők, nincs szükség egyedi breakpoint-rendszerre.

## 8. Kapcsolódó dokumentumok

- [`admin-felulet-terv.md`](admin-felulet-terv.md) — Beállítások képernyők.
- [`api-tervek.md`](api-tervek.md) — a képernyők mögötti végpontok.
- [`csomag-terv.md`](csomag-terv.md) — Tailwind + Alpine.js választás indoklása.
