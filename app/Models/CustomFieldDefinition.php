<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ez a tábla teszi lehetővé, hogy FEJLESZTŐ NÉLKÜL bármilyen egyedi mezőt fel lehessen
 * venni bármelyik entitáshoz — ez adja a rendszer "univerzalitását" (lásd adatmodell.md).
 */
#[Fillable(['account_id', 'service_type_id', 'entity_type', 'field_key', 'label', 'field_type', 'options', 'is_required', 'sort_order'])]
class CustomFieldDefinition extends Model
{
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }
}
