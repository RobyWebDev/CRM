<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nincs saját account_id — a pipeline-on (pipeline_id) keresztül tartozik tenanthoz,
 * ezért nem használja a BelongsToAccount trait-et, csak a pipelines() reláción keresztül szűrődik.
 */
#[Fillable(['pipeline_id', 'name', 'sort_order', 'color', 'probability', 'is_won_stage', 'is_lost_stage'])]
class PipelineStage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_won_stage' => 'boolean',
            'is_lost_stage' => 'boolean',
        ];
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
