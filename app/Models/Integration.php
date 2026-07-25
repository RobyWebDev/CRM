<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Külső eszközök kapcsolatai accountonként — pl. a meglévő HTML-alapú
 * ajánlat-/szerződéskészítő eszköz (lásd crm_projekt.md 7. szekció, architektura.md 4. pont).
 */
#[Fillable(['account_id', 'provider', 'config', 'status'])]
class Integration extends Model
{
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'config' => 'encrypted:array',
        ];
    }
}
