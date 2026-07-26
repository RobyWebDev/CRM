<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Egy tetszőlegesen elnevezhető elérhetőség/mező egy kontakthoz — lásd
 * a migráció megjegyzését (Google Címtár-minta, Rob kérése 2026-07-26).
 */
#[Fillable(['account_id', 'contact_id', 'type', 'label', 'value', 'sort_order'])]
class ContactField extends Model
{
    use BelongsToAccount, HasFactory;

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
