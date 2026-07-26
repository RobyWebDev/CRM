<?php

namespace App\Support;

/**
 * Feloldja a "válassz meglévőt VAGY hozz létre újat" lenyíló mezőket (Rob kérése,
 * 2026-07-26) — pl. Kampány, Szervezet — a nagy CRM-ek (Salesforce/HubSpot/Notion)
 * "+ Új létrehozása..." mintájára: a lenyíló listában választott "__new__" érdék
 * esetén egy VALÓDI, önálló rekordot hoz létre (nem szabad szöveges duplikátumot),
 * `firstOrCreate` névvel — így ha véletlenül ugyanazt a nevet gépeli be valaki,
 * amit már felvett, nem jön létre felesleges másolat.
 */
class SelectOrCreate
{
    public const NEW_OPTION_VALUE = '__new__';

    /**
     * @param  class-string  $modelClass  Egy `name` mezővel rendelkező, account-szűrt modell (pl. Campaign, Organization)
     */
    public static function resolveId(string $modelClass, ?string $selected, ?string $newName): ?int
    {
        if ($selected === self::NEW_OPTION_VALUE) {
            $name = trim((string) $newName);

            return $name !== '' ? $modelClass::firstOrCreate(['name' => $name])->id : null;
        }

        return $selected !== null && $selected !== '' ? (int) $selected : null;
    }
}
