<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Models\Concerns\HasActivityTimeline;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'account_id', 'pipeline_id', 'pipeline_stage_id', 'contact_id', 'organization_id',
    'campaign_id', 'owner_user_id', 'title', 'description', 'value', 'currency', 'status',
    'expected_close_date', 'closed_at', 'stage_entered_at', 'lost_reason', 'invoice_status',
    'custom_fields',
])]
class Deal extends Model
{
    use BelongsToAccount, HasActivityTimeline, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'stage_entered_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    /** Hány teljes napja áll a deal a jelenlegi lépésén — HubSpot-stílusú "elakadt üzlet" jelzéshez. */
    public function daysInStage(): int
    {
        return (int) ($this->stage_entered_at ?? $this->created_at)->diffInDays(now());
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** A "won" dealből lett egyszeri projekt, ha a pipeline erre van konfigurálva. */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    /** A "won" dealből lett ismétlődő retainer, ha a pipeline erre van konfigurálva. */
    public function retainer(): HasOne
    {
        return $this->hasOne(Retainer::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
