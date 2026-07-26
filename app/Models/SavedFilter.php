<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Egy elmentett szűrés-kombináció egy listaoldalhoz (pl. "Forró leadjeim") —
 * csak a szerzőjéhez tartozik, mint a személyes jegyzetek (PersonalNoteController).
 */
#[Fillable(['account_id', 'user_id', 'resource', 'name', 'query_string'])]
class SavedFilter extends Model
{
    use BelongsToAccount, HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
