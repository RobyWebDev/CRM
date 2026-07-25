<?php

namespace App\Models\Concerns;

/**
 * Nyelv szerint helyes névsorrend (Rob kérése, 2026-07-25): magyar nyelvi
 * konvenció szerint a vezetéknév áll elöl (pl. "Kovács János"), angol (US)
 * konvenció szerint a keresztnév (pl. "John Smith"). Ezt használja minden
 * modell, aminek first_name/last_name mezője van (Contact, Lead).
 */
trait HasPersonName
{
    public function getFullNameAttribute(): string
    {
        return trim(static::nameOrder()
            ? $this->last_name.' '.$this->first_name
            : $this->first_name.' '.$this->last_name);
    }

    /**
     * true = magyar sorrend (vezetéknév elöl), false = angol/US sorrend (keresztnév elöl).
     * A bejelentkezett user személyes `locale`-ja számít, ennek hiányában a fiók `locale`-ja,
     * végső esetben az alkalmazás alapértelmezése.
     */
    public static function nameOrder(?\App\Models\User $user = null): bool
    {
        $user ??= auth()->user();
        $locale = $user?->locale ?? $user?->account?->locale ?? config('app.locale');

        return str_starts_with((string) $locale, 'hu');
    }
}
