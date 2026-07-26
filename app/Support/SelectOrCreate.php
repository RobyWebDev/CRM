<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Feloldja a "válassz meglévőt VAGY hozz létre újat" lenyíló mezőket (Rob kérése,
 * 2026-07-26) — pl. Kampány, Szervezet — a nagy CRM-ek (Salesforce/HubSpot/Notion)
 * "+ Új létrehozása..." mintájára: a lenyíló listában választott "__new__" érdék
 * esetén egy VALÓDI, önálló rekordot hoz létre (nem szabad szöveges duplikátumot).
 *
 * A névegyezés KIS/NAGYBETŰTŐL FÜGGETLEN (2026-07-26, önállóan felismert javítás) —
 * enélkül a "Bau-Haus Kft." és a "bau-haus kft." két külön rekordot hozna létre,
 * pont azt a fajta adat-széttöredezést okozva, amit ez az egész mechanizmus el
 * akar kerülni.
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

            return $name !== '' ? self::firstOrCreateByName($modelClass, $name)->id : null;
        }

        return $selected !== null && $selected !== '' ? (int) $selected : null;
    }

    /**
     * @param  class-string  $modelClass
     */
    public static function firstOrCreateByName(string $modelClass, string $name): Model
    {
        $existing = $modelClass::query()->whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();

        return $existing ?? $modelClass::create(['name' => $name]);
    }
}
