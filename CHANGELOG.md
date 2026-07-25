# Changelog

> Verziószámozás a `crm_projekt.md` 3. szekció "Verziószámozás" elve szerint (pl. v0.1.0 = MVP), a TextBuilder projekt mintájára. [Semantic Versioning](https://semver.org/lang/hu/) formátumban: MAJOR.MINOR.PATCH.

## [Unreleased] — tervezési fázis (0.0.x)

Még nincs futó alkalmazás — ez a szakasz a specifikáció és a Laravel-indítás előtti tervezőmunka.

### 2026-07-25

- Projekt specifikáció (`crm_projekt.md`) elkészült, technológiai döntések meghozva (PHP/Laravel, MySQL, cweb hosting).
- Részletes adatmodell (`docs/adatmodell.md`, `docs/schema.sql`) és architektúra-terv (`docs/architektura.md`) elkészült.
- Piszkozat pipeline-sablonok az 5 induló szolgáltatáshoz (`docs/pipeline-sablonok.md`) — validálásra vár.
- REST API végpont-terv (`docs/api-tervek.md`), GDPR-folyamatterv (`docs/gdpr-terv.md`), admin-felület terv (`docs/admin-felulet-terv.md`), teszt-personák (`docs/teszt-personak.md`), CSV-import terv (`docs/csv-import-terv.md`), értesítési rendszer terv (`docs/ertesitesek-terv.md`), coach-kereső integráció terv (`docs/coach-kereso-integracio.md`) elkészült.
- Git verziókövetés elindítva.

**Következő cél: v0.1.0** — MVP (3. fázis): Laravel projekt inicializálva, kontaktok + 1 pipeline/szolgáltatás + projektek + feladatok, single-user, de tenant-ready adatbázissal.
