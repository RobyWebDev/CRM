# Riportok / statisztikák — terv

> A `ui-wireframe-terv.md` Dashboardja egyszerű gyors-számokat mutat (nyitott dealek, aktív projektek, esedékes feladatok). Ez a fájl egy lépéssel mélyebbre megy: milyen üzleti riportokra lesz szüksége Robnak (és később bármely accountnak), hogy lássa, hogyan megy a vállalkozása. Nem volt kifejezett elve a `crm_projekt.md`-nek erre, de a CRM-ek egyik legfontosabb hozzáadott értéke ez — ezért érdemesnek tartottam megtervezni, amíg van rá idő/kapacitás Laravel nélkül. **Ha Rob úgy ítéli meg, hogy ez nem prioritás, egyszerűen kihagyható/később aktuális az MVP-ből.**
> Utolsó frissítés: 2026-07-25.

## 1. Javasolt riportok (MVP utáni, de MVP-adatmodellből már kinyerhető mind)

| Riport | Mit mutat | Forrás |
|---|---|---|
| **Pipeline-tölcsér (funnel)** | hány deal / mekkora összérték van jelenleg az egyes `pipeline_stages`-eken | `deals` GROUP BY `pipeline_stage_id` |
| **Nyerési arány (win rate)** | lezárt dealek közül hány % `won` vs `lost`, időszakra szűrve | `deals` WHERE `status` IN (won, lost), csoportosítva |
| **Átlagos értékesítési ciklus** | átlagosan hány nap telik el a deal létrehozása és `won`/`lost` állapot között | `deals.created_at` → `closed_at` különbség átlaga |
| **Bevétel szolgáltatás-típusonként** | mennyi `won` deal-érték jutott az egyes `service_types`-ra, időszakonként | `deals` JOIN `pipelines` JOIN `service_types` |
| **Havi trend** | hány deal záródott le (won) havonta, milyen összértékben | `deals.closed_at` hónap szerint csoportosítva |
| **Feladat-teljesítés** | hány feladat készült el időben / késve, userenként | `tasks.due_date` vs `completed_at` |

## 2. Hol jelenjen meg

- MVP-ben elég egy egyszerű "Riportok" menüpont, ami a fenti táblázatokat/egyszerű grafikonokat mutatja meg (nem kell külön BI-eszköz).
- **Chart-könyvtár javaslat:** Chart.js (ingyenes, nyílt forráskódú, könnyű, jól működik Blade + Alpine.js mellett, nincs szükség React/Vue-ra) — megfelel a Költség-elvnek és a "nincs SPA" döntésnek (`crm_projekt.md` 2. szekció).
- Szűrők: időszak (pl. utolsó 30/90/365 nap), szolgáltatás-típus, felelős user (ha multi-user).

## 3. Technikai megjegyzés

Ezek a lekérdezések MVP méretben (egy account, néhány száz/ezer rekord) triviálisan gyorsak, nincs szükség előre számolt (materializált) riport-táblákra vagy külön analitikai adatbázisra. Ha a rendszer SaaS-fázisban sok accountra/rekordra nő, ez a pont felülvizsgálandó (pl. napi cache-elt összesítő táblák) — de ez messze nem MVP-kérdés, csak jegyezve a jövőre.

## 4. Kapcsolódó dokumentumok

- [`ui-wireframe-terv.md`](ui-wireframe-terv.md) — Dashboard alapkoncepció.
- [`adatmodell.md`](adatmodell.md) — `deals`, `tasks` táblák, amikből a riportok számolódnak.
