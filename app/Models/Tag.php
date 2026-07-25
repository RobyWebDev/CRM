<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Szabadon felvehető címke, kontaktokhoz/szervezetekhez rendelhető —
 * MiniCRM-inspiráció (docs/minicrm-inspiracio.md 6. pont).
 */
#[Fillable(['account_id', 'name', 'color'])]
class Tag extends Model
{
    use BelongsToAccount, HasFactory;

    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(Contact::class, 'taggable');
    }

    public function organizations(): MorphToMany
    {
        return $this->morphedByMany(Organization::class, 'taggable');
    }
}
