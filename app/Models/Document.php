<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Linkek szerződésekhez/ajánlatokhoz — pl. a meglévő HTML-alapú
 * ajánlat-/szerződéskészítő eszköz kimenete (lásd crm_projekt.md 7. szekció).
 */
#[Fillable(['account_id', 'documentable_type', 'documentable_id', 'title', 'url', 'type'])]
class Document extends Model
{
    use BelongsToAccount, HasFactory, SoftDeletes;

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
