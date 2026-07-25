<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Jövőbeli SaaS-fázis: mit fizet az adott account a CRM-ért. Előkészítve, MVP-ben nem használt.
 */
#[Fillable(['account_id', 'tier', 'status', 'started_at', 'renewed_at', 'canceled_at', 'external_ref'])]
class Subscription extends Model
{
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'renewed_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }
}
