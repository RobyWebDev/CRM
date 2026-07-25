<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Szabadon bővíthető szolgáltatás-típus — nincs hardcode-olt lista (coaching, webdesign, stb.),
 * Rob bármikor felvehet újat kódolás nélkül. Lásd adatmodell.md / pipeline-sablonok.md.
 */
#[Fillable(['account_id', 'name', 'slug', 'description', 'icon', 'color', 'is_active', 'sort_order'])]
class ServiceType extends Model
{
    use BelongsToAccount, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pipelines(): HasMany
    {
        return $this->hasMany(Pipeline::class);
    }

    public function customFieldDefinitions(): HasMany
    {
        return $this->hasMany(CustomFieldDefinition::class);
    }
}
