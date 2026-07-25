<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['account_id', 'noteable_type', 'noteable_id', 'user_id', 'body'])]
class Note extends Model
{
    use BelongsToAccount, HasFactory;

    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Semmilyen rekordhoz nem kötött, saját jegyzet (crm_projekt.md 8. szekció, 9. pont).
     */
    public function scopePersonal(Builder $query): Builder
    {
        return $query->whereNull('noteable_type');
    }
}
