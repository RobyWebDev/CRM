# Onboarding — "első lépések" folyamat

> A `crm_projekt.md` 3. szekció "Onboarding" elvének kifejtése: ha bárki regisztrálhat, kell egy egyszerű "első lépések" folyamat.
> MVP-ben (1 user = Rob) ez nem releváns — ez a terv az 5-6. fázisra készül elő, hogy ne kelljen akkor kapkodni.
> Utolsó frissítés: 2026-07-25.

## 1. Mikor releváns

Attól a pillanattól, hogy valaki a `crm_projekt.md` 2. szekcióban leírt coach-kereső webhookon keresztül (vagy egy jövőbeli publikus regisztrációs formon) új accountot kap — ekkor kell először eligazítani, mit lát maga előtt.

## 2. Első bejelentkezés utáni folyamat (javaslat)

1. **Üdvözlő képernyő:** rövid, 3 lépéses bemutató (modal vagy dedikált oldal) — "Ez a CRM-ed. Nézzük meg, hogyan állítsd be a saját szakmádra."
2. **Szolgáltatás-típus választó/létrehozó:** a felhasználó vagy kiválaszt egy előre elkészített sablont (ha lesz ilyen — pl. a `pipeline-sablonok.md`-ben szereplő 5 induló szakma, mint választható induló sablon), vagy létrehozza a sajátját az [`admin-felulet-terv.md`](admin-felulet-terv.md) szerinti felületen.
3. **Első kontakt felvétele:** egy rövid, végigvezetett form ("vegyél fel egy ügyfelet"), hogy azonnal lásson valós adatot a rendszerben.
4. **"Kész vagy"** — irányítás a Dashboardra (lásd [`ui-wireframe-terv.md`](ui-wireframe-terv.md)).

Az onboarding bármikor átugorható ("Kihagyom, magamtól nézem meg") — ne legyen kényszerített.

## 3. Sablon-alapú induló pipeline-ok (ötlet, nem MVP-blokkoló)

Ha a `pipeline-sablonok.md` tartalma validálva lesz, érdemes lehet ezeket "induló sablonokként" felkínálni minden új accountnak (pl. "Coach vagyok" → automatikusan bemásolja a coaching pipeline-sablont, testreszabható). Ez jelentősen felgyorsítja az új felhasználók első élményét, mert nem üres listával indulnak.

## 4. Nem MVP-feladat, csak jegyezve

Ez a funkció csak akkor válik ténylegesen szükségessé, amikor a multi-user/publikus regisztrációs fázis (5-6.) elindul. MVP-ben Rob accountja seederrel jön létre (lásd `seeder-terv.md`), nincs szüksége onboarding-flow-ra.

## 5. Kapcsolódó dokumentumok

- [`ui-wireframe-terv.md`](ui-wireframe-terv.md)
- [`admin-felulet-terv.md`](admin-felulet-terv.md)
- [`pipeline-sablonok.md`](pipeline-sablonok.md)
