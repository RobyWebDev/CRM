<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Strukturált kampány-nyilvántartás a `source` szabad szöveges mező mellé —
 * Salesforce Lead Source/Campaign Influence minta egyszerűsítve, lásd
 * docs/ugyfelszerzes-terv.md 3.2. pont.
 */
#[Fillable(['account_id', 'name', 'type', 'started_at', 'cost'])]
class Campaign extends Model
{
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
