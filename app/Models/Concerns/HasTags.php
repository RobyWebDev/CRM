<?php

namespace App\Models\Concerns;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

/**
 * Címkézhető entitás (Contact, Organization) — MiniCRM-inspiráció, lásd
 * docs/minicrm-inspiracio.md 6. pont. A címkék account-szinten egyediek
 * (két account ugyanazt a nevet függetlenül használhatja).
 */
trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Vesszővel elválasztott címke-nevek szinkronizálása — a nem létező címkék
     * automatikusan létrejönnek az aktuális accounthoz, kód nélkül (a felhasználó
     * bármikor felvehet újat, egyszerűen begépelve).
     */
    public function syncTagsFromString(?string $namesString): void
    {
        $names = collect(explode(',', (string) $namesString))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => Str::lower($name));

        $tagIds = $names->map(function (string $name) {
            return Tag::firstOrCreate(
                ['account_id' => $this->account_id, 'name' => $name]
            )->id;
        });

        $this->tags()->sync($tagIds);
    }
}
