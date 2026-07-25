<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Accounthoz tartozó API-kulcsok, amikkel külső modulok (pl. ajánlatkészítő) hitelesítve
 * elérik a CRM belső REST API-ját. Csak a hash tárolódik, a nyers token soha.
 */
#[Fillable(['account_id', 'name', 'token_hash', 'scopes', 'last_used_at', 'revoked_at'])]
#[Hidden(['token_hash'])]
class ApiKey extends Model
{
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
